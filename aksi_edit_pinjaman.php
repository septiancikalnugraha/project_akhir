<?php
session_start();
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success'=>false,'error'=>'Akses ditolak.']); exit;
}
$id = intval($_POST['id'] ?? 0);
$instalment = trim($_POST['instalment'] ?? '');
$subtotal = trim($_POST['subtotal'] ?? '');
$fee = trim($_POST['fee'] ?? '');
$total = trim($_POST['total'] ?? '');
$fiscal_date = trim($_POST['fiscal_date'] ?? '');
$status = trim($_POST['status'] ?? '');
if ($id==0 || $instalment === '' || $subtotal === '' || $fee === '' || $total === '' || $fiscal_date === '' || $status === '') {
    echo json_encode(['success'=>false,'error'=>'Semua field wajib diisi.']); exit;
}
$sql = "UPDATE loans SET instalment=?, subtotal=?, fee=?, total=?, fiscal_date=?, status=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sdddssi', $instalment, $subtotal, $fee, $total, $fiscal_date, $status, $id);
if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Gagal mengedit data.']);
} 