<?php
session_start();
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success'=>false,'error'=>'Akses ditolak.']); exit;
}
$id = intval($_GET['id'] ?? 0);
if($id==0) { echo json_encode(['success'=>false,'error'=>'ID tidak valid.']); exit; }
// Ambil user_id dari customer
$q = $conn->query("SELECT user_id FROM customers WHERE id=$id AND deleted_at IS NULL");
if(!$q || !$q->num_rows) { echo json_encode(['success'=>false,'error'=>'Data tidak ditemukan.']); exit; }
$user_id = $q->fetch_assoc()['user_id'];
$now = date('Y-m-d H:i:s');
$conn->query("UPDATE customers SET deleted_at='$now' WHERE id=$id");
$conn->query("UPDATE users SET deleted_at='$now' WHERE id=$user_id");
echo json_encode(['success'=>true]); 