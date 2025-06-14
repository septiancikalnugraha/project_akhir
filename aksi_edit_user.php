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

    // Check if user exists
    $check_sql = "SELECT id, role FROM users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        throw new Exception('User not found');
    }

    // Prevent editing own role
    if ($id == $_SESSION['user']['id']) {
        throw new Exception('Cannot edit your own role');
    }

    // Get and validate input
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    $role = isset($_POST['role']) ? trim($_POST['role']) : null;

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

    // Check if email already exists (if email is being changed)
    if ($email !== null && $email !== $user['email']) {
        $email_check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $email_check_stmt = $conn->prepare($email_check_sql);
        $email_check_stmt->bind_param("si", $email, $id);
        $email_check_stmt->execute();
        $email_result = $email_check_stmt->get_result();
        
        if ($email_result->num_rows > 0) {
            throw new Exception('Email already exists');
        }
        $email_check_stmt->close();
    }

    // Build update query
    $update_fields = [];
    $types = "";
    $params = [];

    if ($name !== null) {
        $update_fields[] = "name = ?";
        $types .= "s";
        $params[] = $name;
    }

    if ($email !== null) {
        $update_fields[] = "email = ?";
        $types .= "s";
        $params[] = $email;
    }

    if ($password !== null) {
        $update_fields[] = "password = ?";
        $types .= "s";
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($role !== null) {
        $update_fields[] = "role = ?";
        $types .= "s";
        $params[] = $role;
    }

    if (empty($update_fields)) {
        throw new Exception('No fields to update');
    }

    // Add ID to params
    $types .= "i";
    $params[] = $id;

    // Execute update
    $update_sql = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    
    if (!$update_stmt) {
        throw new Exception('Failed to prepare update statement: ' . $conn->error);
    }

    $update_stmt->bind_param($types, ...$params);

    if ($update_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update user: ' . $update_stmt->error);
    }

    $update_stmt->close();
    $check_stmt->close();

} catch (Exception $e) {
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