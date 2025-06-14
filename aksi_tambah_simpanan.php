<?php
session_start();
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success'=>false,'error'=>'Akses ditolak.']); exit;
}
$customer_id = trim($_POST['customer_id'] ?? '');
$type = trim($_POST['type'] ?? '');
$plan = trim($_POST['plan'] ?? '');
$subtotal = trim($_POST['subtotal'] ?? '');
$fee = trim($_POST['fee'] ?? '');
$total = trim($_POST['total'] ?? '');
$fiscal_date = trim($_POST['fiscal_date'] ?? '');
$status = trim($_POST['status'] ?? '');
if ($customer_id === '' || $type === '' || $plan === '' || $subtotal === '' || $fee === '' || $total === '' || $fiscal_date === '' || $status === '') {
    echo json_encode(['success'=>false,'error'=>'Semua field wajib diisi.']); exit;
}
$sql = "INSERT INTO deposits (customer_id, type, plan, subtotal, fee, total, fiscal_date, status, created_at) VALUES (?,?,?,?,?,?,?,?,NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param('issdddss', $customer_id, $type, $plan, $subtotal, $fee, $total, $fiscal_date, $status);
if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Gagal menambah data.']);
} 