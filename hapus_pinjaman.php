<?php
session_start();
require 'db.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo json_encode(['success'=>false,'error'=>'Akses ditolak.']); exit;
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    $sql = "DELETE FROM loans WHERE id = $id";
    if ($conn->query($sql)) {
        echo json_encode(['success'=>true]);
        exit;
    } else {
        echo json_encode(['success'=>false,'error'=>'Gagal menghapus data.']);
        exit;
    }
}
echo json_encode(['success'=>false,'error'=>'ID tidak valid.']);
exit; 