<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Check if user is logged in and is a petugas
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if all required fields are provided
if (!isset($_POST['id']) || !isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['role'])) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
    exit;
}

$id = intval($_POST['id']);
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$role = $_POST['role'];

// Validate input
if (empty($name) || empty($email) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Format email tidak valid']);
    exit;
}

if (!in_array($role, ['petugas', 'ketua'])) {
    echo json_encode(['success' => false, 'message' => 'Role tidak valid']);
    exit;
}

try {
    // Check if email already exists for other users
    $check_sql = "SELECT id FROM users WHERE email = ? AND id != ? AND deleted_at IS NULL";
    $check_stmt = $conn->prepare($check_sql);
    
    if (!$check_stmt) {
        throw new Exception("Error preparing check statement: " . $conn->error);
    }
    
    $check_stmt->bind_param("si", $email, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        exit;
    }
    
    $check_stmt->close();
    
    // Prepare the update query based on whether password is being updated
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE id = ? AND deleted_at IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $hashed_password, $role, $id);
    } else {
        $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ? AND deleted_at IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $role, $id);
    }
    
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Error executing statement: " . $stmt->error);
    }
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Data user berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tidak ada perubahan data']);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
} 