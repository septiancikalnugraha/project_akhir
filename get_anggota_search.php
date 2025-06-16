<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$role = $_SESSION['user']['role'] ?? '';
$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$where = "deleted_at IS NULL";

if ($q != '') {
    $where .= " AND (name LIKE '%$q%' OR email LIKE '%$q%' OR phone LIKE '%$q%')";
}

$sql = "SELECT * FROM customers WHERE $where ORDER BY id ASC";
$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'no' => $no,
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']),
            'email' => htmlspecialchars($row['email']),
            'phone' => htmlspecialchars($row['phone']),
            'can_edit' => $role == 'petugas'
        ];
        $no++;
    }
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => true, 'data' => []]);
} 