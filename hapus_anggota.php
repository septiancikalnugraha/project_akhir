<?php
session_start();
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success'=>false,'error'=>'Akses ditolak.']); exit;
}
$id = intval($_GET['id'] ?? 0);
if($id==0) { echo json_encode(['success'=>false,'error'=>'ID tidak valid.']); exit; }
// Cek apakah anggota masih punya simpanan atau pinjaman yang belum dihapus
$cek = $conn->query("SELECT 
    (SELECT COUNT(*) FROM deposits WHERE customer_id=$id AND deleted_at IS NULL) as simpanan,
    (SELECT COUNT(*) FROM loans WHERE customer_id=$id AND deleted_at IS NULL) as pinjaman");
if($cek && $cek->num_rows) {
    $row = $cek->fetch_assoc();
    if($row['simpanan'] > 0 || $row['pinjaman'] > 0) {
        echo json_encode(['success'=>false,'error'=>'Anggota tidak dapat dihapus karena masih memiliki simpanan atau pinjaman.']); exit;
    }
}
// Ambil user_id dari customer
$q = $conn->query("SELECT user_id, email FROM customers WHERE id=$id");
if(!$q || !$q->num_rows) { echo json_encode(['success'=>false,'error'=>'Data tidak ditemukan.']); exit; }
$row = $q->fetch_assoc();
$user_id = $row['user_id'];
$email = $row['email'];
// Hapus customer
$conn->query("DELETE FROM customers WHERE id=$id");
if($user_id) {
    $conn->query("DELETE FROM users WHERE id=$user_id");
} else if($email) {
    $email_clean = strtolower(trim($email));
    $conn->query("DELETE FROM users WHERE LOWER(TRIM(email))='" . $conn->real_escape_string($email_clean) . "'");
}
echo json_encode(['success'=>true]); 