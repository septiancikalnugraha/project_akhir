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

// Check if customer_id exists and get total verified savings
$sql_check_savings = "SELECT SUM(total) AS total_savings FROM deposits WHERE customer_id = ? AND status = 'verified' AND deleted_at IS NULL";
$stmt_check_savings = $conn->prepare($sql_check_savings);

if (!$stmt_check_savings) {
    echo json_encode(['success' => false, 'error' => 'Error preparing savings check statement: ' . $conn->error]);
    exit;
}

$stmt_check_savings->bind_param("i", $customer_id);
$stmt_check_savings->execute();
$result_savings = $stmt_check_savings->get_result();
$row_savings = $result_savings->fetch_assoc();
$total_savings = $row_savings['total_savings'] ?? 0;
$stmt_check_savings->close();

// Validate loan against savings
if ($total_savings < $total) {
    echo json_encode(['success' => false, 'error' => 'Jumlah simpanan anggota tidak mencukupi untuk pinjaman ini. Simpanan saat ini: Rp ' . number_format($total_savings, 0, ',', '.') . '. Jumlah pinjaman: Rp ' . number_format($total, 0, ',', '.')]);
    exit;
}

$sql = "INSERT INTO loans (customer_id, instalment, subtotal, fee, total, fiscal_date, status, created_at) VALUES (?,?,?,?,?,?,?,NOW())";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(['success' => false, 'error' => 'Gagal menyiapkan statement: ' . $conn->error]);
    exit;
}

$stmt->bind_param('isdddss', $customer_id, $instalment, $subtotal, $fee, $total, $fiscal_date, $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Pinjaman berhasil ditambahkan.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Gagal menambah data pinjaman: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?> 