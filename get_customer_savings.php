<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success'=>false,'error'=>'Akses ditolak.']); 
    exit;
}

$customer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($customer_id === 0) {
    echo json_encode(['success'=>false,'error'=>'ID customer tidak valid.']); 
    exit;
}

// Get total savings for the customer
$sql = "SELECT COALESCE(SUM(total), 0) as total_savings 
        FROM deposits 
        WHERE customer_id = ? AND deleted_at IS NULL AND status = 'verified'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'total_savings' => $row['total_savings']
]); 