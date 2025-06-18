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
$required_fields = ['id', 'name', 'email', 'phone', 'address'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi']);
        exit;
    }
}

try {
    // Sanitize input
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);

    // Check if email already exists for other users
    $check_sql = "SELECT id FROM customers WHERE email = ? AND id != ? AND deleted_at IS NULL";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Error preparing check statement: " . $conn->error);
    }

    $check_stmt->bind_param("si", $email, $id);
    if (!$check_stmt->execute()) {
        throw new Exception("Error executing check statement: " . $check_stmt->error);
    }

    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        exit;
    }

    // Start building the update query
    $sql = "UPDATE customers SET name = ?, email = ?, phone = ?, address = ?";
    $params = [$name, $email, $phone, $address];
    $types = "ssss";

    // If password is provided, update it
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql .= ", password = ?";
        $params[] = $password;
        $types .= "s";
    }

    $sql .= " WHERE id = ? AND deleted_at IS NULL";
    $params[] = $id;
    $types .= "i";

    // Prepare and execute the update
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparing update statement: " . $conn->error);
    }

    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        throw new Exception("Error executing update statement: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Data anggota berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Tidak ada perubahan data']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Update kolom user_id di tabel customers berdasarkan email yang sama di tabel users
$sql = "UPDATE customers c JOIN users u ON c.email = u.email SET c.user_id = u.id WHERE (c.user_id IS NULL OR c.user_id = 0) AND u.deleted_at IS NULL";
if ($conn->query($sql) === TRUE) {
    echo "Berhasil update user_id di tabel customers untuk data lama.";
} else {
    echo "Gagal update: " . $conn->error;
}
$conn->close(); 