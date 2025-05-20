<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';
header('Content-Type: application/json');

// Cek login dan role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success'=>false,'error'=>'Akses ditolak.']); exit;
}

// Validasi ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id == 0) {
    echo json_encode(['success'=>false,'error'=>'ID tidak valid.']); exit;
}

// Cek apakah user ada
$cek = $conn->prepare("SELECT id FROM users WHERE id = ?");
$cek->bind_param("i", $id);
$cek->execute();
$cek->store_result();
if($cek->num_rows == 0) {
    echo json_encode(['success'=>false,'error'=>'User tidak ditemukan.']); exit;
}
$cek->close();

// Hapus user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
if($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Gagal hapus: '.$conn->error]);
}
$stmt->close();
$conn->close(); 