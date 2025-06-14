<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) { exit; }
$role = $_SESSION['user']['role'] ?? '';
$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';
$where = "l.deleted_at IS NULL";
if($role == 'anggota') {
    $user_id = $_SESSION['user']['id'];
    $where .= " AND l.customer_id = $user_id";
}
if($q != '') {
    $where .= " AND (c.name LIKE '$q%' OR c.name LIKE '%$q%')";
}
$sql = "SELECT l.*, c.name as customer_name FROM loans l LEFT JOIN customers c ON l.customer_id = c.id WHERE $where ORDER BY l.created_at DESC";
$result = $conn->query($sql);
$no = 1;
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$no}</td>
            <td>{$row['customer_name']}</td>
            <td><span class='badge loaned'>{$row['status']}</span></td>
            <td>{$row['instalment']}</td>
            <td>Rp " . number_format($row['subtotal'],0,',','.') . "</td>
            <td>Rp " . number_format($row['fee'],0,',','.') . "</td>
            <td>Rp " . number_format($row['total'],0,',','.') . "</td>
            <td>" . ($row['fiscal_date'] ? date('d F Y H:i', strtotime($row['fiscal_date'])) : '-') . "</td>
            <td class='table-actions'>
                <button class='btn btn-view' onclick='showDetailModal({$row['id']})'>View</button>
                <?php if($role == 'petugas'): ?>
                    <button class='btn btn-view' onclick='openEditModal({$row['id']})'>Edit</button>
                    <a href='hapus_pinjaman.php?id={$row['id']}' class='btn btn-view' style=\"color:#e74c3c;border-color:#e74c3c;\" onclick=\"return confirm('Yakin ingin menghapus data ini?');\">Hapus</a>
                <?php endif; ?>
            </td>
        </tr>";
        $no++;
    }
} else {
    echo "<tr><td colspan='9' style='text-align:center;'>Tidak ada data</td></tr>";
} 