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
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$password = trim($_POST['password'] ?? '');
if ($id==0 || $name=='' || $email=='' || $phone=='' || $address=='') {
    echo json_encode(['success'=>false,'error'=>'Semua field wajib diisi.']); exit;
}
// Cek email tidak boleh dobel (kecuali milik sendiri)
$cek = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != (SELECT user_id FROM customers WHERE id = ?) LIMIT 1');
$cek->bind_param('si', $email, $id);
$cek->execute();
$cek->store_result();
if ($cek->num_rows > 0) {
    echo json_encode(['success'=>false,'error'=>'Email sudah terdaftar.']); exit;
}
// Update users
if($password!=='') {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $sql = 'UPDATE users SET name=?, email=?, password=? WHERE id=(SELECT user_id FROM customers WHERE id=?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $name, $email, $hashed, $id);
} else {
    $sql = 'UPDATE users SET name=?, email=? WHERE id=(SELECT user_id FROM customers WHERE id=?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $name, $email, $id);
}
if(!$stmt->execute()) {
    echo json_encode(['success'=>false,'error'=>'Gagal update user.']); exit;
}
// Update customers
$sql2 = 'UPDATE customers SET name=?, email=?, phone=?, address=? WHERE id=?';
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param('ssssi', $name, $email, $phone, $address, $id);
if($stmt2->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>'Gagal update anggota.']);
} 