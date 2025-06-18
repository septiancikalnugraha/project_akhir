<?php
session_start();
require 'db.php';

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];
    $role = $conn->real_escape_string(trim($_POST['role']));
    $email_lower = strtolower($email);
    $role_lower = strtolower($role);
    // Cek email saja dulu
    $sql_email = "SELECT * FROM users WHERE LOWER(email)='$email_lower' AND deleted_at IS NULL LIMIT 1";
    $result_email = $conn->query($sql_email);
    if ($result_email && $result_email->num_rows == 1) {
        $user = $result_email->fetch_assoc();
        if (strtolower($user['role']) !== $role_lower) {
            $error = "Peran tidak sesuai dengan email!";
        } else if (!password_verify($password, $user['password'])) {
            $error = "Kata sandi salah!";
        } else {
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit;
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login SIKOPIN</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #FFB266 0%, #FF9933 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 6px 24px rgba(255,153,51,0.13);
            padding: 36px 28px 28px 28px;
            width: 100%;
            max-width: 370px;
            margin: 40px 0;
            position: relative;
            animation: fadeIn 0.7s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-card .avatar {
            display: flex;
            justify-content: center;
            margin-bottom: 16px;
        }
        .login-card img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 1px 4px rgba(255,153,51,0.10);
        }
        .login-card h2 {
            text-align: center;
            color: #FF9933;
            margin-bottom: 6px;
            font-size: 1.7rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .login-card h3 {
            text-align: center;
            color: #333;
            margin-bottom: 16px;
            font-size: 1.08rem;
            font-weight: 500;
        }
        .login-card label {
            font-weight: 500;
            color: #444;
            margin-bottom: 4px;
            display: block;
        }
        .login-card input[type=email],
        .login-card input[type=password],
        .login-card select {
            width: 100%;
            padding: 10px 12px;
            border: 1.3px solid #FFB266;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 1rem;
            background: #f9f9f9;
            transition: border-color 0.2s;
        }
        .login-card input[type=email]:focus,
        .login-card input[type=password]:focus,
        .login-card select:focus {
            border-color: #FF9933;
            outline: none;
        }
        .login-card .form-group {
            position: relative;
        }
        .login-card .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            color: #FF9933;
            width: 22px;
            height: 22px;
        }
        .login-card .toggle-password:after {
            content: '';
            display: block;
            width: 18px;
            height: 2px;
            background: #FF9933;
            position: absolute;
            top: 10px;
            left: 2px;
            opacity: 0.2;
            border-radius: 1px;
        }
        .login-card .toggle-password.active:after {
            opacity: 0.7;
        }
        .login-card .remember-row {
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }
        .login-card input[type=checkbox] {
            accent-color: #FF9933;
            margin-right: 7px;
        }
        .login-card input[type=submit] {
            width: 100%;
            background: linear-gradient(135deg, #FF9933, #FFB266);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 12px 0;
            font-size: 1.08rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            box-shadow: 0 2px 8px rgba(255,153,51,0.10);
            transition: background 0.2s, transform 0.1s;
        }
        .login-card input[type=submit]:hover {
            background: linear-gradient(135deg, #FFB266, #FF9933);
            transform: translateY(-2px) scale(1.01);
        }
        .login-card .error {
            background: #ffe0e0;
            color: #d8000c;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 0;
            text-align: center;
            font-size: 0.98rem;
        }
        .login-card .success {
            background: #e0ffe0;
            color: #1a7f1a;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 0;
            text-align: center;
            font-size: 0.98rem;
        }
        .login-card p {
            text-align: center;
            margin-top: 18px;
            color: #555;
        }
        .login-card a {
            color: #FF9933;
            text-decoration: none;
            font-weight: 600;
        }
        .login-card a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="avatar">
        <img src="koperasi.jpg" alt="Logo Koperasi">
    </div>
    <h3>Masuk ke Akun Koperasi</h3>
    <h2>SIKOPIN</h2>
    <form method="post">
        <label for="role">Peran</label>
        <select name="role" class="role-select" required>
            <option value="">Pilih Peran</option>
            <option value="petugas">Petugas</option>
            <option value="ketua">Ketua</option>
            <option value="anggota">Anggota</option>
        </select>
        <label for="email">Alamat email</label>
        <input type="email" name="email" required autocomplete="username">
        <label for="password">Kata sandi</label>
        <div class="form-group">
            <input type="password" name="password" id="password" required autocomplete="current-password">
            <button type="button" class="toggle-password" onclick="togglePassword('password', this)"></button>
        </div>
        <div class="remember-row">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember" style="display:inline;">Ingat saya</label>
        </div>
        <input type="submit" value="Masuk">
        <?php if($error) echo "<div class='error'>$error</div>"; ?>
    </form>
    <p>
        Belum punya akun? <a href="register.php">Daftar di sini</a>
    </p>
</div>
<script>
function togglePassword(id, btn) {
    var input = document.getElementById(id);
    btn.classList.toggle('active');
    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
</script>
</body>
</html>