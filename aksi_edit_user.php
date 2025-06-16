<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user'])) {
        throw new Exception('Unauthorized access');
    }

    // Validate required fields
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        throw new Exception('User ID is required');
    }

    $id = intval($_POST['id']);
    if ($id <= 0) {
        throw new Exception('Invalid user ID');
    }

    // Check if user exists and get current role
    $check_sql = "SELECT id, role, name, email FROM users WHERE id = ? AND deleted_at IS NULL";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Error preparing check statement: " . $conn->error);
    }
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $user_data = $result->fetch_assoc();

    if (!$user_data) {
        throw new Exception('User not found');
    }
    $current_role = $user_data['role'];
    $current_name = $user_data['name'];
    $current_email = $user_data['email'];

    // Prevent editing own role
    if ($id == $_SESSION['user']['id'] && isset($_POST['role']) && $_POST['role'] != $current_role) {
        throw new Exception('Cannot edit your own role');
    }

    // Get and validate input
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    $role = isset($_POST['role']) ? trim($_POST['role']) : null; // Proposed new role
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null; // Added for customer update
    $address = isset($_POST['address']) ? trim($_POST['address']) : null; // Added for customer update

    // Determine the effective role after this update
    $effective_role = $role ?? $current_role;

    // Validate email if provided
    if ($email !== null && !$email) {
        throw new Exception('Invalid email format');
    }

    // Validate role if provided
    if ($role !== null) {
        $allowed_roles = ['admin', 'petugas', 'anggota'];
        if (!in_array($role, $allowed_roles)) {
            throw new Exception('Invalid role');
        }
    }

    // Check if email already exists (if email is being changed) in users table
    if ($email !== null && $email !== $current_email) {
        $email_check_user_sql = "SELECT id FROM users WHERE email = ? AND id != ? AND deleted_at IS NULL";
        $email_check_user_stmt = $conn->prepare($email_check_user_sql);
        if (!$email_check_user_stmt) {
            throw new Exception("Error preparing user email check statement: " . $conn->error);
        }
        $email_check_user_stmt->bind_param("si", $email, $id);
        $email_check_user_stmt->execute();
        $email_user_result = $email_check_user_stmt->get_result();
        
        if ($email_user_result->num_rows > 0) {
            throw new Exception('Email sudah terdaftar untuk pengguna lain.');
        }
        $email_check_user_stmt->close();
    }

    // Start transaction
    $conn->begin_transaction();

    // Build update query for users table
    $update_fields_user = [];
    $types_user = "";
    $params_user = [];

    if ($name !== null) {
        $update_fields_user[] = "name = ?";
        $types_user .= "s";
        $params_user[] = $name;
    }

    if ($email !== null) {
        $update_fields_user[] = "email = ?";
        $types_user .= "s";
        $params_user[] = $email;
    }

    if ($password !== null) {
        $update_fields_user[] = "password = ?";
        $types_user .= "s";
        $params_user[] = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($role !== null) {
        $update_fields_user[] = "role = ?";
        $types_user .= "s";
        $params_user[] = $role;
    }

    if (empty($update_fields_user)) {
        // No fields to update in users table, but still need to proceed for customer update if role is anggota
        // throw new Exception('No fields to update'); // Remove this line
    }

    // Only execute user update if there are fields to update
    if (!empty($update_fields_user)) {
        // Add ID to params
        $types_user .= "i";
        $params_user[] = $id;

        $update_user_sql = "UPDATE users SET " . implode(", ", $update_fields_user) . " WHERE id = ?";
        $update_user_stmt = $conn->prepare($update_user_sql);
        
        if (!$update_user_stmt) {
            throw new Exception('Failed to prepare user update statement: ' . $conn->error);
        }
        $update_user_stmt->bind_param($types_user, ...$params_user);
        if (!$update_user_stmt->execute()) {
            throw new Exception('Failed to update user: ' . $update_user_stmt->error);
        }
        $update_user_stmt->close();
    }

    // Handle customers table update based on effective role
    $customer_id = null;
    $customer_sql = "SELECT id FROM customers WHERE user_id = ? AND deleted_at IS NULL";
    $customer_stmt = $conn->prepare($customer_sql);
    if (!$customer_stmt) {
        throw new Exception("Error preparing customer check statement: " . $conn->error);
    }
    $customer_stmt->bind_param("i", $id);
    $customer_stmt->execute();
    $customer_result = $customer_stmt->get_result();
    if ($customer_result->num_rows > 0) {
        $customer_data = $customer_result->fetch_assoc();
        $customer_id = $customer_data['id'];
    }
    $customer_stmt->close();

    if ($effective_role == 'anggota') {
        // If user is an 'anggota', ensure there's a corresponding customer entry and update it
        if ($customer_id) {
            // Update existing customer
            $update_fields_customer = [];
            $types_customer = "";
            $params_customer = [];

            // Use name from POST if provided, otherwise from user_data (users table)
            $customer_display_name = $name ?? $current_name;
            $customer_display_email = $email ?? $current_email;

            if ($name !== null) { $update_fields_customer[] = "name = ?"; $types_customer .= "s"; $params_customer[] = $name; }
            if ($email !== null) { $update_fields_customer[] = "email = ?"; $types_customer .= "s"; $params_customer[] = $email; }
            if ($phone !== null) { $update_fields_customer[] = "phone = ?"; $types_customer .= "s"; $params_customer[] = $phone; }
            if ($address !== null) { $update_fields_customer[] = "address = ?"; $types_customer .= "s"; $params_customer[] = $address; }

            if (!empty($update_fields_customer)) {
                $types_customer .= "i";
                $params_customer[] = $customer_id;
                $update_customer_sql = "UPDATE customers SET " . implode(", ", $update_fields_customer) . " WHERE id = ?";
                $update_customer_stmt = $conn->prepare($update_customer_sql);
                if (!$update_customer_stmt) {
                    throw new Exception('Failed to prepare customer update statement: ' . $conn->error);
                }
                $update_customer_stmt->bind_param($types_customer, ...$params_customer);
                if (!$update_customer_stmt->execute()) {
                    throw new Exception('Failed to update customer: ' . $update_customer_stmt->error);
                }
                $update_customer_stmt->close();
            }
        } else {
            // This case should ideally be handled by add_anggota.php. 
            // If a user becomes 'anggota' via edit but no customer entry exists, we should create one.
            // For now, let's just make sure it's created if it's new and has a user_id link.
            // Assuming `add_anggota.php` handles new anggota creation with user_id linkage.
            // If a user (not created via add_anggota.php) is converted to anggota, we create customer entry.
            if ($current_role != 'anggota') { // Only create if role just changed to 'anggota'
                 $insert_customer_sql = "INSERT INTO customers (name, email, phone, address, user_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
                 $insert_customer_stmt = $conn->prepare($insert_customer_sql);
                 if (!$insert_customer_stmt) {
                     throw new Exception("Error preparing customer insert statement: " . $conn->error);
                 }
                 // Use name/email from POST if available, otherwise from existing user_data
                 $insert_name = $name ?? $current_name;
                 $insert_email = $email ?? $current_email;
                 $insert_phone = $phone ?? ''; // Phone and Address might not be sent from user form unless it's the customer-specific one
                 $insert_address = $address ?? '';
                 $insert_customer_stmt->bind_param("ssssi", $insert_name, $insert_email, $insert_phone, $insert_address, $id);
                 if (!$insert_customer_stmt->execute()) {
                     throw new Exception("Error executing new customer insert statement: " . $insert_customer_stmt->error);
                 }
                 $insert_customer_stmt->close();
            }
        }
    } else if ($current_role == 'anggota' && $effective_role != 'anggota') {
        // If user was an 'anggota' but role changed, soft-delete the customer entry
        if ($customer_id) {
            $soft_delete_customer_sql = "UPDATE customers SET deleted_at = NOW() WHERE id = ?";
            $soft_delete_customer_stmt = $conn->prepare($soft_delete_customer_sql);
            if (!$soft_delete_customer_stmt) {
                throw new Exception('Failed to prepare customer soft-delete statement: ' . $conn->error);
            }
            $soft_delete_customer_stmt->bind_param("i", $customer_id);
            if (!$soft_delete_customer_stmt->execute()) {
                throw new Exception('Failed to soft-delete customer: ' . $soft_delete_customer_stmt->error);
            }
            $soft_delete_customer_stmt->close();
        }
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 