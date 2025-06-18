<?php
session_start();
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success'=>false,'error'=>'Akses ditolak.']); exit;
}
$id = intval($_POST['id'] ?? 0);
$customer_id = intval($_POST['customer_id'] ?? 0);
$type = trim($_POST['type'] ?? '');
$plan = trim($_POST['plan'] ?? '');
$subtotal = trim($_POST['subtotal'] ?? '');
$fee = trim($_POST['fee'] ?? '');
$total = trim($_POST['total'] ?? '');
$fiscal_date = trim($_POST['fiscal_date'] ?? '');
$status = trim($_POST['status'] ?? '');
if ($id==0 || $customer_id==0 || $type === '' || $plan === '' || $subtotal === '' || $fee === '' || $total === '' || $fiscal_date === '' || $status === '') {
    echo json_encode(['success'=>false,'error'=>'Semua field wajib diisi.']); exit;
}
// Validasi customer_id
$sql_check_customer = "SELECT id FROM customers WHERE id = ? AND deleted_at IS NULL";
$stmt_check_customer = $conn->prepare($sql_check_customer);
if (!$stmt_check_customer) {
    echo json_encode(['success'=>false,'error'=>'Gagal menyiapkan statement validasi customer: ' . $conn->error]); exit;
}
$stmt_check_customer->bind_param('i', $customer_id);
$stmt_check_customer->execute();
$result_customer = $stmt_check_customer->get_result();
if ($result_customer->num_rows == 0) {
    echo json_encode(['success'=>false,'error'=>'Customer tidak ditemukan atau sudah dihapus.']); exit;
}
$stmt_check_customer->close();
$sql = "UPDATE deposits SET customer_id=?, type=?, plan=?, subtotal=?, fee=?, total=?, fiscal_date=?, status=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('issdddssi', $customer_id, $type, $plan, $subtotal, $fee, $total, $fiscal_date, $status, $id);
if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Gagal mengedit data: ' . $conn->error]);
} 