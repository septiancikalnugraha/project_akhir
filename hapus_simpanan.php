<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    header('Location: login.php');
    exit;
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    $sql = "UPDATE deposits SET deleted_at = NOW() WHERE id = $id";
    $conn->query($sql);
}
header('Location: simpanan.php');
exit; 