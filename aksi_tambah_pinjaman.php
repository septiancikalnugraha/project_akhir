<?php
session_start();
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success'=>false,'error'=>'Akses ditolak.']); exit;
}
$customer_id = trim($_POST['customer_id'] ?? '');
$instalment = trim($_POST['instalment'] ?? '');
$subtotal = trim($_POST['subtotal'] ?? '');
$fee = trim($_POST['fee'] ?? '');
$total = trim($_POST['total'] ?? '');
$fiscal_date = trim($_POST['fiscal_date'] ?? '');
$status = trim($_POST['status'] ?? '');
if ($customer_id === '' || $instalment === '' || $subtotal === '' || $fee === '' || $total === '' || $fiscal_date === '' || $status === '') {
    echo json_encode(['success'=>false,'error'=>'Semua field wajib diisi.']); exit;
}

// Check member's total savings
$savings_sql = "SELECT COALESCE(SUM(total), 0) as total_savings FROM deposits 
                WHERE customer_id = ? AND deleted_at IS NULL AND status = 'verified'";
$savings_stmt = $conn->prepare($savings_sql);
$savings_stmt->bind_param('i', $customer_id);
$savings_stmt->execute();
$savings_result = $savings_stmt->get_result();
$savings_row = $savings_result->fetch_assoc();
$total_savings = $savings_row['total_savings'];

// If total savings is less than loan amount, reject the loan
if ($total_savings < $total) {
    echo json_encode(['success'=>false,'error'=>'Total simpanan tidak mencukupi untuk melakukan pinjaman.']); 
    exit;
}

$sql = "INSERT INTO loans (customer_id, instalment, subtotal, fee, total, fiscal_date, status, created_at) VALUES (?,?,?,?,?,?,?,NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param('isdddss', $customer_id, $instalment, $subtotal, $fee, $total, $fiscal_date, $status);
if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Gagal menambah data.']);
} 