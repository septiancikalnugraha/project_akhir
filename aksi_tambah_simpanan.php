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
$subtotal = floatval($_POST['subtotal'] ?? 0);
$fee = floatval($_POST['fee'] ?? 0);
$total = $subtotal + $fee;
$fiscal_date = trim($_POST['fiscal_date'] ?? '');
$status = trim($_POST['status'] ?? '');
if ($customer_id === '' || $type === '' || $plan === '' || $subtotal === '' || $fee === '' || $fiscal_date === '' || $status === '') {
    echo json_encode(['success'=>false,'error'=>'Semua field wajib diisi.']); exit;
}
// Cek customer dan user sekaligus
$sql = "SELECT c.id as customer_id, u.id as user_id FROM customers c LEFT JOIN users u ON c.user_id = u.id AND u.deleted_at IS NULL WHERE c.id = ? AND c.deleted_at IS NULL LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo json_encode(['success'=>false,'error'=>'User atau anggota tidak ditemukan atau sudah dihapus.']); exit;
}
$row = $result->fetch_assoc();
if (!$row['user_id'] || !$row['customer_id']) {
    echo json_encode(['success'=>false,'error'=>'User atau anggota tidak ditemukan atau sudah dihapus.']); exit;
}
$stmt->close();
$sql = "INSERT INTO deposits (customer_id, type, plan, subtotal, fee, total, fiscal_date, status, created_at) VALUES (?,?,?,?,?,?,?,?,NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param('issdddss', $customer_id, $type, $plan, $subtotal, $fee, $total, $fiscal_date, $status);
if ($stmt->execute()) {
    echo json_encode(['success'=>true, 'message' => 'Simpanan berhasil ditambahkan.']);
} else {
    echo json_encode(['success'=>false,'error'=>'Gagal menambah data.']);
} 