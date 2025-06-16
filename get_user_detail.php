<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM users WHERE id = $id";
$result = $conn->query($sql);
$data = $result && $result->num_rows ? $result->fetch_assoc() : null;

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    exit;
}

echo json_encode([
    'success' => true,
    'data' => [
        'id' => $data['id'],
        'name' => $data['name'],
        'email' => $data['email'],
        'role' => $data['role'],
        'created_at' => $data['created_at'] ? date('d F Y', strtotime($data['created_at'])) : '-'
    ]
]); 