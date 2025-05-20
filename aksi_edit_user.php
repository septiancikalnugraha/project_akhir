<?php
session_start();
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success'=>false,'error'=>'Akses ditolak.']); exit;
}
$id = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? '');
$password = trim($_POST['password'] ?? '');
if ($id==0 || $name=='' || $email=='' || $role=='') {
    echo json_encode(['success'=>false,'error'=>'Semua field wajib diisi.']); exit;
}
// Cek email tidak boleh dobel (kecuali milik sendiri)
$cek = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
$cek->bind_param('si', $email, $id);
$cek->execute();
$cek->store_result();
if ($cek->num_rows > 0) {
    echo json_encode(['success'=>false,'error'=>'Email sudah terdaftar.']); exit;
}
// Update users
if($password!=='') {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $sql = 'UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssi', $name, $email, $role, $hashed, $id);
} else {
    $sql = 'UPDATE users SET name=?, email=?, role=? WHERE id=?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $name, $email, $role, $id);
}
if($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Gagal update user.']);
} 