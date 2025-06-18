<?php
require 'db.php';
$sql = "UPDATE customers c JOIN users u ON c.email = u.email SET c.user_id = u.id WHERE (c.user_id IS NULL OR c.user_id = 0) AND u.deleted_at IS NULL";
if ($conn->query($sql) === TRUE) {
    echo "Berhasil update user_id di tabel customers untuk data lama.";
} else {
    echo "Gagal update: " . $conn->error;
}
$conn->close(); 