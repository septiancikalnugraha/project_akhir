<?php
require 'db.php';
$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = trim($conn->real_escape_string($_POST['email']));
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $role = $conn->real_escape_string($_POST['role']);

    if ($password !== $confirm) {
        $error = "Konfirmasi kata sandi tidak cocok!";
    } else {
        $email_lower = strtolower($email);
        // Cek di tabel users (belum dihapus, case-insensitive)
        $sql_user = "SELECT id FROM users WHERE LOWER(email)='$email_lower' AND deleted_at IS NULL";
        $result_user = $conn->query($sql_user);
        // Cek di tabel customers (belum dihapus, case-insensitive)
        $sql_cust = "SELECT id FROM customers WHERE LOWER(email)='$email_lower' AND deleted_at IS NULL";
        $result_cust = $conn->query($sql_cust);
        if (($result_user && $result_user->num_rows > 0) || ($result_cust && $result_cust->num_rows > 0)) {
            $error = "Email sudah terdaftar!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hash', '$role')";
            if ($conn->query($sql)) {
                $user_id = $conn->insert_id;
                if ($role === 'anggota') {
                    $sql_customer = "INSERT INTO customers (user_id, name, email) VALUES ('$user_id', '$name', '$email')";
                    if (!$conn->query($sql_customer)) {
                        $error = "Registrasi anggota gagal: " . $conn->error;
                    } else {
                        $success = "Registrasi berhasil! Silakan <a href='login.php'>login</a>.";
                    }
                } else {
                    $success = "Registrasi berhasil! Silakan <a href='login.php'>login</a>.";
                }
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
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #FFB266 0%, #FF9933 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(255,153,51,0.18);
            padding: 40px 32px 32px 32px;
            width: 100%;
            max-width: 420px;
            margin: 40px 0;
            position: relative;
            animation: fadeIn 0.7s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .register-card .avatar {
            display: flex;
            justify-content: center;
            margin-bottom: 18px;
        }
        .register-card img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(255,153,51,0.15);
            background: #fff;
        }
        .register-card h2 {
            text-align: center;
            color: #FF9933;
            margin-bottom: 6px;
            font-size: 2rem;
            font-weight: 700;
        }
        .register-card h3 {
            text-align: center;
            color: #333;
            margin-bottom: 18px;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .register-card label {
            font-weight: 500;
            color: #444;
            margin-bottom: 4px;
            display: block;
        }
        .register-card input[type=text],
        .register-card input[type=email],
        .register-card input[type=password],
        .register-card select {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #FFB266;
            border-radius: 7px;
            margin-bottom: 16px;
            font-size: 1rem;
            background: #f9f9f9;
            transition: border-color 0.2s;
        }
        .register-card input[type=text]:focus,
        .register-card input[type=email]:focus,
        .register-card input[type=password]:focus,
        .register-card select:focus {
            border-color: #FF9933;
            outline: none;
        }
        .register-card .form-group {
            position: relative;
        }
        .register-card .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            color: #FF9933;
        }
        .register-card input[type=submit] {
            width: 100%;
            background: linear-gradient(135deg, #FF9933, #FFB266);
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 12px 0;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            box-shadow: 0 2px 8px rgba(255,153,51,0.10);
            transition: background 0.2s, transform 0.1s;
        }
        .register-card input[type=submit]:hover {
            background: linear-gradient(135deg, #FFB266, #FF9933);
            transform: translateY(-2px) scale(1.02);
        }
        .register-card .error {
            background: #ffe0e0;
            color: #d8000c;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 0;
            text-align: center;
            font-size: 0.98rem;
            animation: shake 0.3s;
        }
        @keyframes shake {
            10%, 90% { transform: translateX(-2px); }
            20%, 80% { transform: translateX(4px); }
            30%, 50%, 70% { transform: translateX(-8px); }
            40%, 60% { transform: translateX(8px); }
        }
        .register-card .success {
            background: #e0ffe0;
            color: #1a7f1a;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 0;
            text-align: center;
            font-size: 0.98rem;
        }
        .register-card p {
            text-align: center;
            margin-top: 18px;
            color: #555;
        }
        .register-card a {
            color: #FF9933;
            text-decoration: none;
            font-weight: 600;
        }
        .register-card a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="register-card">
    <div class="avatar">
        <img src="koperasi.jpg" alt="Logo Koperasi">
    </div>
    <h3>Buat Akun Koperasi</h3>
    <h2>SIKOPIN</h2>
    <form method="post">
        <label for="role">Peran</label>
        <select name="role" class="role-select" required>
            <option value="">Pilih Peran</option>
            <option value="petugas">Petugas</option>
            <option value="ketua">Ketua</option>
            <option value="anggota">Anggota</option>
        </select>
        <label for="name">Nama</label>
        <input type="text" name="name" required>
        <label for="email">Alamat email</label>
        <input type="email" name="email" required autocomplete="username">
        <label for="password">Kata sandi</label>
        <div class="form-group">
            <input type="password" name="password" id="password" required autocomplete="new-password">
            <button type="button" class="toggle-password" onclick="togglePassword('password', this)"></button>
        </div>
        <label for="confirm">Konfirmasi kata sandi</label>
        <div class="form-group">
            <input type="password" name="confirm" id="confirm" required autocomplete="new-password">
            <button type="button" class="toggle-password" onclick="togglePassword('confirm', this)"></button>
        </div>
        <input type="submit" value="Daftar">
        <?php
        if($error) echo "<div class='error'>$error</div>";
        if($success) echo "<div class='success'>$success</div>";
        ?>
    </form>
    <p>
        Sudah punya akun? <a href="login.php">Login di sini</a>
    </p>
</div>
<script>
function togglePassword(id, btn) {
    var input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "";
    } else {
        input.type = "password";
        btn.textContent = "";
    }
}
</script>
</body>
</html>