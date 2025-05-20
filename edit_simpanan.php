<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    header('Location: login.php');
    exit;
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Ambil data lama
$sql = "SELECT * FROM deposits WHERE id = $id AND deleted_at IS NULL";
$result = $conn->query($sql);
$data = $result && $result->num_rows ? $result->fetch_assoc() : null;
$error = '';
if (!$data) {
    $error = 'Data tidak ditemukan.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type']);
    $plan = trim($_POST['plan']);
    $subtotal = trim($_POST['subtotal']);
    $fee = trim($_POST['fee']);
    $total = trim($_POST['total']);
    $fiscal_date = trim($_POST['fiscal_date']);
    $status = trim($_POST['status']);
    if ($type === '' || $plan === '' || $subtotal === '' || $fee === '' || $total === '' || $fiscal_date === '' || $status === '') {
        $error = 'Semua field wajib diisi.';
    } else {
        $sql = "UPDATE deposits SET type=?, plan=?, subtotal=?, fee=?, total=?, fiscal_date=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssdddssi', $type, $plan, $subtotal, $fee, $total, $fiscal_date, $status, $id);
        if ($stmt->execute()) {
            header('Location: simpanan.php');
            exit;
        } else {
            $error = 'Gagal mengupdate data.';
        }
    }
}
?><!DOCTYPE html>
<html>
<head>
    <title>Edit Simpanan - SIKOPIN</title>
    <link rel='stylesheet' href='style.css'>
    <style>body{background:#FFB266;margin:0;font-family:Arial,sans-serif;}.sidebar{position:fixed;left:0;top:0;width:220px;height:100%;background:#FFB266;border-right:1px solid #e0e0e0;padding-top:20px;box-shadow:0 0 10px rgba(0,0,0,0.05);}.sidebar h2{text-align:center;font-size:24px;margin-bottom:30px;font-weight:bold;color:#333;}.sidebar ul{list-style:none;padding:0;margin:0;}.sidebar li{padding:12px 20px;font-size:16px;color:#333;display:flex;align-items:center;border-radius:8px 0 0 8px;margin-bottom:2px;}.sidebar li.active{background-color:#fff;border-left:4px solid #e67e22;color:#e67e22;font-weight:bold;}.sidebar li a{text-decoration:none;color:inherit;width:100%;display:inline-block;}.sidebar li:hover{background-color:#ffe0b2;}.main-content{margin-left:220px;padding:30px;}.page-title{font-size:28px;font-weight:bold;margin-bottom:10px;}.card-form{background:#fff;border-radius:8px;border:1px solid #ddd;padding:24px 30px;max-width:500px;margin:0 auto;}.form-group{margin-bottom:16px;}.form-group label{display:block;margin-bottom:6px;color:#333;font-weight:500;}.form-group input,.form-group select{width:100%;padding:8px 10px;border-radius:5px;border:1px solid #bbb;font-size:15px;}.btn{padding:8px 20px;border-radius:5px;border:none;background:#e67e22;color:#fff;cursor:pointer;font-size:15px;}.btn:hover{background:#ff9800;}.error{color:#e74c3c;margin-bottom:10px;}</style>
</head>
<body>
<div class='sidebar'><h2>SIKOPIN</h2><ul><li><a href='dashboard.php'><span>&#128200; Dasbor</span></a></li><li class='active'><a href='simpanan.php'><span>&#128179; Simpanan</span></a></li><li><a href='pinjaman.php'><span>&#128181; Pinjaman</span></a></li><li><a href='anggota.php'><span>&#128101; Anggota</span></a></li><li><a href='user.php'><span>&#9881; User</span></a></li></ul></div>
<div class='main-content'>
    <div class='page-title'>Edit Simpanan</div>
    <div class='card-form'>
        <?php if($error): ?><div class='error'><?php echo $error; ?></div><?php endif; ?>
        <?php if($data): ?>
        <form method='post'>
            <div class='form-group'><label>Type</label><input type='text' name='type' value='<?php echo htmlspecialchars($data['type']); ?>' required></div>
            <div class='form-group'><label>Plan</label><input type='text' name='plan' value='<?php echo htmlspecialchars($data['plan']); ?>' required></div>
            <div class='form-group'><label>Subtotal</label><input type='number' name='subtotal' value='<?php echo $data['subtotal']; ?>' required></div>
            <div class='form-group'><label>Fee</label><input type='number' name='fee' value='<?php echo $data['fee']; ?>' required></div>
            <div class='form-group'><label>Total</label><input type='number' name='total' value='<?php echo $data['total']; ?>' required></div>
            <div class='form-group'><label>Fiscal Date</label><input type='datetime-local' name='fiscal_date' value='<?php echo date('Y-m-d\TH:i', strtotime($data['fiscal_date'])); ?>' required></div>
            <div class='form-group'><label>Status</label><select name='status' required><option value='pending' <?php if($data['status']=='pending') echo 'selected'; ?>>Pending</option><option value='verified' <?php if($data['status']=='verified') echo 'selected'; ?>>Verified</option></select></div>
            <button type='submit' class='btn'>Simpan</button>
            <a href='simpanan.php' class='btn' style='background:#bbb;color:#fff;margin-left:10px;'>Batal</a>
        </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html> 