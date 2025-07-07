<?php
// Script untuk mengimport struktur database
require 'db.php';

// Baca file SQL
$sql_file = 'project_akhir.sql';
$sql_content = file_get_contents($sql_file);

if ($sql_content === false) {
    die("Error: Tidak dapat membaca file $sql_file");
}

// Pisahkan query berdasarkan semicolon
$queries = explode(';', $sql_content);

$success_count = 0;
$error_count = 0;

foreach ($queries as $query) {
    $query = trim($query);
    
    // Skip query kosong
    if (empty($query)) {
        continue;
    }
    
    // Eksekusi query
    if ($conn->query($query)) {
        $success_count++;
        echo "✓ Berhasil: " . substr($query, 0, 50) . "...<br>";
    } else {
        $error_count++;
        echo "✗ Error: " . $conn->error . " pada query: " . substr($query, 0, 50) . "...<br>";
    }
}

echo "<br><strong>Hasil Import:</strong><br>";
echo "Berhasil: $success_count tabel<br>";
echo "Error: $error_count tabel<br>";

if ($error_count == 0) {
    echo "<br><strong style='color: green;'>✓ Database berhasil diimport dengan sempurna!</strong><br>";
    echo "<a href='dashboard.php'>Klik di sini untuk ke Dashboard</a>";
} else {
    echo "<br><strong style='color: red;'>✗ Ada error dalam import database</strong>";
}

$conn->close();
?> 