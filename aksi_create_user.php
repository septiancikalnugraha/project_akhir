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
    $required_fields = ['name', 'email', 'password', 'role'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Field {$field} is required");
        }
    }

    // Sanitize and validate input
    $name = trim($_POST['name']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $role = trim($_POST['role']);

    if (!$email) {
        throw new Exception('Invalid email format');
    }

    // Validate role
    $allowed_roles = ['admin', 'petugas', 'anggota'];
    if (!in_array($role, $allowed_roles)) {
        throw new Exception('Invalid role');
    }

    // Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception('Email already exists');
    }
    $check_stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $insert_sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    
    if (!$insert_stmt) {
        throw new Exception('Failed to prepare insert statement: ' . $conn->error);
    }

    $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

    if ($insert_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'User created successfully'
        ]);
    } else {
        throw new Exception('Failed to create user: ' . $insert_stmt->error);
    }

    $insert_stmt->close();

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