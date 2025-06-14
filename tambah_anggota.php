<?php
session_start();
require 'db.php';

// Cek login dan role
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    header("Location: login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    if ($name === '' || $email === '' || $phone === '' || $address === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } else {
        // Insert ke tabel users
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql_user = "INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)";
        $stmt_user = $conn->prepare($sql_user);
        $role = 'anggota';
        $stmt_user->bind_param('ssss', $name, $email, $hashed, $role);
        if ($stmt_user->execute()) {
            $user_id = $conn->insert_id;
            // Insert ke tabel customers
            $sql_cust = "INSERT INTO customers (user_id, name, email, phone, address, created_at) VALUES (?,?,?,?,?,NOW())";
            $stmt_cust = $conn->prepare($sql_cust);
            $stmt_cust->bind_param('issss', $user_id, $name, $email, $phone, $address);
            if ($stmt_cust->execute()) {
                header('Location: anggota.php');
                exit;
            } else {
                $error = 'Gagal menambah data anggota (customers).';
            }
        } else {
            $error = 'Gagal menambah data user.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Anggota - SIKOPIN</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #FFB266; margin:0; font-family: Arial,sans-serif; }
        .sidebar {
            position: fixed; left: 0; top: 0; width: 220px; height: 100%; background: #FFB266; border-right: 1px solid #e0e0e0; padding-top: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        .sidebar h2 { text-align: center; font-size: 24px; margin-bottom: 30px; font-weight: bold; color: #333; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar li { padding: 12px 20px; font-size: 16px; color: #333; display: flex; align-items: center; border-radius: 8px 0 0 8px; margin-bottom: 2px; }
        .sidebar li.active { background-color: #fff; border-left: 4px solid #e67e22; color: #e67e22; font-weight: bold; }
        .sidebar li a { text-decoration: none; color: inherit; width: 100%; display: inline-block; }
        .sidebar li:hover { background-color: #ffe0b2; }
        .main-content { margin-left: 220px; padding: 30px; }
        .page-title { font-size: 28px; font-weight: bold; margin-bottom: 10px; }
        .card-form { background: #fff; border-radius: 8px; border: 1px solid #ddd; padding: 24px 30px; max-width: 500px; margin: 0 auto; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; color: #333; font-weight: 500; }
        .form-group input, .form-group textarea { width: 100%; padding: 8px 10px; border-radius: 5px; border: 1px solid #bbb; font-size: 15px; }
        .btn { padding: 8px 20px; border-radius: 5px; border: none; background: #e67e22; color: #fff; cursor: pointer; font-size: 15px; }
        .btn:hover { background: #ff9800; }
        .error { color: #e74c3c; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>SIKOPIN</h2>
        <ul>
            <li><a href="dashboard.php"><span>&#128200; Dasbor</span></a></li>
            <li><a href="simpanan.php"><span>&#128179; Simpanan</span></a></li>
            <li><a href="pinjaman.php"><span>&#128181; Pinjaman</span></a></li>
            <li class="active"><a href="anggota.php"><span>&#128101; Anggota</span></a></li>
            <li><a href="user.php"><span>&#9881; User</span></a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="page-title">Tambah Anggota</div>
        <div class="card-form">
            <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Telepon</label>
                    <input type="text" name="phone" required>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="address" required></textarea>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn">Simpan</button>
                <a href="anggota.php" class="btn" style="background:#bbb; color:#fff; margin-left:10px;">Batal</a>
            </form>
        </div>
    </div>
</body>
</html> 