<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Check if user is logged in and is a petugas
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
    exit;
}

$id = intval($_GET['id']);

// Get customer details with total deposits and loans
$sql = "SELECT c.*, 
        COALESCE(SUM(d.total), 0) as total_simpanan,
        COALESCE(SUM(l.total), 0) as total_pinjaman
        FROM customers c
        LEFT JOIN deposits d ON c.id = d.customer_id AND d.deleted_at IS NULL AND d.status = 'verified'
        LEFT JOIN loans l ON c.id = l.customer_id AND l.deleted_at IS NULL AND l.status = 'loaned'
        WHERE c.id = ? AND c.deleted_at IS NULL
        GROUP BY c.id";

try {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        throw new Exception("Error executing statement: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        // Format numbers for display
        $row['total_simpanan'] = number_format($row['total_simpanan'], 0, ',', '.');
        $row['total_pinjaman'] = number_format($row['total_pinjaman'], 0, ',', '.');
        
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Anggota tidak ditemukan']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} 