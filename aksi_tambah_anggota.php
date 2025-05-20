<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success'=>false,'error'=>'Akses ditolak.']); exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($name === '' || $email === '' || $phone === '' || $address === '' || $password === '') {
    echo json_encode(['success'=>false,'error'=>'Semua field wajib diisi.']); exit;
}

// Cek email sudah terdaftar di users
$cek = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$cek->bind_param('s', $email);
$cek->execute();
$cek->store_result();
if ($cek->num_rows > 0) {
    echo json_encode(['success'=>false,'error'=>'Email sudah terdaftar.']); exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$sql_user = "INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)";
$stmt_user = $conn->prepare($sql_user);
$role = 'anggota';
$stmt_user->bind_param('ssss', $name, $email, $hashed, $role);
if ($stmt_user->execute()) {
    $user_id = $conn->insert_id;
    $sql_cust = "INSERT INTO customers (user_id, name, email, phone, address, created_at) VALUES (?,?,?,?,?,NOW())";
    $stmt_cust = $conn->prepare($sql_cust);
    $stmt_cust->bind_param('issss', $user_id, $name, $email, $phone, $address);
    if ($stmt_cust->execute()) {
        echo json_encode(['success'=>true]); exit;
    } else {
        echo json_encode(['success'=>false,'error'=>'Gagal menambah data anggota (customers).']); exit;
    }
} else {
    echo json_encode(['success'=>false,'error'=>'Gagal menambah data user.']); exit;
} 