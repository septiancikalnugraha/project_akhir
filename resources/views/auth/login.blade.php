<!DOCTYPE html>
<html>
<head>
    <title>Login SIKOPIN</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="form-container">
    <div class="avatar" style="background:none;box-shadow:none;border:none;">
        <img src="{{ asset('images/koperasi.jpg') }}" alt="Logo Koperasi" style="width:80px;height:80px;object-fit:contain;display:block;margin:0 auto;">
    </div>
    <h3>Masuk ke Akun Koperasi</h3>
    <h2>SIKOPIN</h2>
    <form method="post" action="{{ route('login') }}">
        @csrf
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
        @if($error)
            <div class='error'>{{ $error }}</div>
        @endif
    </form>
    <p style="text-align:center;margin-top:10px;">
        Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>
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