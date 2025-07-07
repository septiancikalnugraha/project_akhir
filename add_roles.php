<?php
// Script untuk menambahkan role petugas dan ketua
require 'db.php';

echo "<h2>Menambahkan Role Petugas dan Ketua</h2>";

// Data role yang akan ditambahkan
$roles = [
    [
        'role' => 'petugas',
        'name' => 'Petugas Koperasi',
        'email' => 'petugas@sikopin.com',
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    ],
    [
        'role' => 'ketua',
        'name' => 'Ketua Koperasi',
        'email' => 'ketua@sikopin.com',
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    ]
];

$success_count = 0;
$error_count = 0;

foreach ($roles as $role_data) {
    // Cek apakah email sudah ada
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $role_data['email']);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<p style='color: orange;'>⚠️ Role {$role_data['role']} sudah ada (email: {$role_data['email']})</p>";
        continue;
    }
    
    // Insert role baru
    $sql = "INSERT INTO users (role, name, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", 
        $role_data['role'], 
        $role_data['name'], 
        $role_data['email'], 
        $role_data['password']
    );
    
    if ($stmt->execute()) {
        $success_count++;
        echo "<p style='color: green;'>✅ Berhasil menambahkan role: {$role_data['role']} ({$role_data['email']})</p>";
    } else {
        $error_count++;
        echo "<p style='color: red;'>❌ Error menambahkan role {$role_data['role']}: " . $stmt->error . "</p>";
    }
}

echo "<hr>";
echo "<h3>Hasil Penambahan Role:</h3>";
echo "<p>Berhasil: $success_count role</p>";
echo "<p>Error: $error_count role</p>";

// Tampilkan semua role yang ada
echo "<hr>";
echo "<h3>Daftar Role yang Tersedia:</h3>";
$sql = "SELECT id, role, name, email, created_at FROM users WHERE role IN ('admin', 'petugas', 'ketua') ORDER BY role";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>Role</th><th>Nama</th><th>Email</th><th>Password</th><th>Tanggal Dibuat</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td><strong>{$row['role']}</strong></td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>password</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Tidak ada role yang ditemukan.</p>";
}

echo "<hr>";
echo "<h3>Informasi Login:</h3>";
echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Admin:</strong> admin@sikopin.com / password</p>";
echo "<p><strong>Petugas:</strong> petugas@sikopin.com / password</p>";
echo "<p><strong>Ketua:</strong> ketua@sikopin.com / password</p>";
echo "</div>";

echo "<hr>";
echo "<a href='dashboard.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Kembali ke Dashboard</a>";

$conn->close();
?> 