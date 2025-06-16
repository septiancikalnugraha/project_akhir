<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Check if user is logged in and is a petugas
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required fields
$required_fields = ['name', 'email', 'phone', 'address', 'password'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        exit;
    }
}

try {
    // Sanitize input
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $raw_password = $_POST['password'];
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
    $role = 'anggota'; // Set role for new member

    // Check if email already exists in users table
    $check_user_sql = "SELECT id FROM users WHERE email = ? AND deleted_at IS NULL";
    $check_user_stmt = $conn->prepare($check_user_sql);
    if (!$check_user_stmt) {
        throw new Exception("Error preparing user check statement: " . $conn->error);
    }
    $check_user_stmt->bind_param("s", $email);
    $check_user_stmt->execute();
    $check_user_result = $check_user_stmt->get_result();
    if ($check_user_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar sebagai pengguna.']);
        exit;
    }
    $check_user_stmt->close();

    // Start transaction
    $conn->begin_transaction();

    // Insert new user into users table
    $insert_user_sql = "INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())";
    $insert_user_stmt = $conn->prepare($insert_user_sql);
    if (!$insert_user_stmt) {
        throw new Exception("Error preparing user insert statement: " . $conn->error);
    }
    $insert_user_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
    if (!$insert_user_stmt->execute()) {
        throw new Exception("Error executing user insert statement: " . $insert_user_stmt->error);
    }
    $user_id = $conn->insert_id;
    $insert_user_stmt->close();

    // Insert new customer into customers table, linked to the new user_id
    $insert_customer_sql = "INSERT INTO customers (name, email, phone, address, user_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $insert_customer_stmt = $conn->prepare($insert_customer_sql);
    if (!$insert_customer_stmt) {
        throw new Exception("Error preparing customer insert statement: " . $conn->error);
    }
    $insert_customer_stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);
    if (!$insert_customer_stmt->execute()) {
        throw new Exception("Error executing customer insert statement: " . $insert_customer_stmt->error);
    }
    $insert_customer_stmt->close();

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Anggota dan pengguna berhasil ditambahkan']);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    // Ensure connection is closed
    $conn->close();
} 