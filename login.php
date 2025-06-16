<?php
session_start();
require 'db.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $role = $conn->real_escape_string(trim($_POST['role']));

    $sql = "SELECT * FROM users WHERE LOWER(email)=LOWER('$email') AND LOWER(role)=LOWER('$role') AND deleted_at IS NULL LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Kata sandi salah!";
        }
    } else {
        $error = "Email atau peran tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login SIKOPIN</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <div class="avatar" style="background:none;box-shadow:none;border:none;">
        <img src="koperasi.jpg" alt="Logo Koperasi" style="width:80px;height:80px;object-fit:contain;display:block;margin:0 auto;">
    </div>
    <h3>Masuk ke Akun Koperasi</h3>
    <h2>SIKOPIN</h2>
    <form method="post">
        <select name="role" class="role-select" required>
            <option value="">Pilih Peran</option>
            <option value="petugas">Petugas Koperasi</option>
            <option value="ketua">Ketua</option>
            <option value="anggota">Anggota</option>
        </select>
        <label>Alamat email</label>
        <input type="email" name="email" required>
        <label>Kata sandi</label>
        <div class="form-group">
            <input type="password" name="password" id="password" required>
            <button type="button" class="toggle-password" onclick="togglePassword('password', this)"></button>
        </div>
        <div class="remember-row">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember" style="display:inline;">Ingat saya</label>
        </div>
        <input type="submit" value="Masuk">
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
    </form>
    <p style="text-align:center;margin-top:10px;">
        Belum punya akun? <a href="register.php">Daftar di sini</a>
    </p>
</div>
<script>
function togglePassword(id, btn) {
    var input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
</script>
</body>
</html>