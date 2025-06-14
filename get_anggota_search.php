<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) { exit; }
$role = $_SESSION['user']['role'] ?? '';
$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$where = "deleted_at IS NULL";
if($q != '') {
    $where .= " AND (name LIKE '%$q%' OR email LIKE '%$q%' OR phone LIKE '%$q%')";
}
$sql = "SELECT * FROM customers WHERE $where ORDER BY id ASC";
$result = $conn->query($sql);
$no = 1;
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$no}</td>
            <td>".htmlspecialchars($row['name'])."</td>
            <td>".htmlspecialchars($row['email'])."</td>
            <td>".htmlspecialchars($row['phone'])."</td>
            <td class='table-actions'>
                <button class='btn btn-view' onclick='showDetailModal({$row['id']})'>View</button>";
        if($role == 'petugas') {
            echo " <button class='btn btn-view' onclick='openEditModal({$row['id']})'>Edit</button>
                <button class='btn btn-view' style='color:#e74c3c;border-color:#e74c3c;' onclick='hapusAnggota({$row['id']})'>Hapus</button>";
        }
        echo "</td>
        </tr>";
        $no++;
    }
} else {
    echo "<tr><td colspan='5' style='text-align:center;'>Tidak ada data</td></tr>";
} 