<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Check if user is logged in and is a petugas
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
    exit;
}

try {
    $id = intval($_POST['id']);

    // Check if customer has any active deposits or loans
    $check_sql = "SELECT 
        (SELECT COUNT(*) FROM deposits WHERE customer_id = ? AND deleted_at IS NULL AND status = 'verified') as active_deposits,
        (SELECT COUNT(*) FROM loans WHERE customer_id = ? AND deleted_at IS NULL AND status = 'loaned') as active_loans";
    
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Error preparing check statement: " . $conn->error);
    }

    $check_stmt->bind_param("ii", $id, $id);
    if (!$check_stmt->execute()) {
        throw new Exception("Error executing check statement: " . $check_stmt->error);
    }

    $check_result = $check_stmt->get_result();
    $check_data = $check_result->fetch_assoc();

    if ($check_data['active_deposits'] > 0 || $check_data['active_loans'] > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Anggota tidak dapat dihapus karena masih memiliki simpanan atau pinjaman aktif'
        ]);
        exit;
    }

    // Soft delete the customer
    $sql = "UPDATE customers SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparing delete statement: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception("Error executing delete statement: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Anggota berhasil dihapus']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Anggota tidak ditemukan atau sudah dihapus']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 