<?php
require 'db.php';
$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $role = $conn->real_escape_string($_POST['role']);

    if ($password !== $confirm) {
        $error = "Konfirmasi kata sandi tidak cocok!";
    } else {
        $sql = "SELECT id FROM users WHERE email='$email'";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hash', '$role')";
            if ($conn->query($sql)) {
                $success = "Registrasi berhasil! Silakan <a href='login.php'>login</a>.";
            } else {
                $error = "Registrasi gagal!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrasi SIKOPIN</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <div class="avatar" style="background:none;box-shadow:none;border:none;">
        <img src="koperasi.jpg" alt="Logo Koperasi" style="width:80px;height:80px;object-fit:contain;display:block;margin:0 auto;">
    </div>
    <h3>Buat Akun Koperasi</h3>
    <h2>SIKOPIN</h2>
    <form method="post">
        <select name="role" class="role-select" required>
            <option value="">Pilih Peran</option>
            <option value="petugas">Petugas Koperasi</option>
            <option value="ketua">Ketua</option>
            <option value="anggota">Anggota</option>
        </select>
        <label>Nama</label>
        <input type="text" name="name" required>
        <label>Alamat email</label>
        <input type="email" name="email" required>
        <label>Kata sandi</label>
        <div class="form-group">
            <input type="password" name="password" id="password" required>
            <button type="button" class="toggle-password" onclick="togglePassword('password', this)"></button>
        </div>
        <label>Konfirmasi kata sandi</label>
        <div class="form-group">
            <input type="password" name="confirm" id="confirm" required>
            <button type="button" class="toggle-password" onclick="togglePassword('confirm', this)"></button>
        </div>
        <input type="submit" value="Daftar">
        <?php
        if($error) echo "<div class='error'>$error</div>";
        if($success) echo "<div class='success'>$success</div>";
        ?>
    </form>
    <p style="text-align:center;margin-top:10px;">
        Sudah punya akun? <a href="login.php">Login di sini</a>
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