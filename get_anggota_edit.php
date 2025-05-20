<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo '<div style="color:#e74c3c;">Akses ditolak.</div>';
    exit;
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM customers WHERE id = $id AND deleted_at IS NULL";
$result = $conn->query($sql);
$data = $result && $result->num_rows ? $result->fetch_assoc() : null;
if(!$data) { echo '<div style="color:#e74c3c;">Data tidak ditemukan.</div>'; exit; }
echo '<form onsubmit="submitEditAnggota(event,'.$id.')">';
echo '<h3 class="modal-title custom-modal-drag">Edit Anggota</h3>';
echo '<div class="form-group"><label>Nama</label><input type="text" name="name" value="'.htmlspecialchars($data['name']).'" required></div>';
echo '<div class="form-group"><label>Email</label><input type="email" name="email" value="'.htmlspecialchars($data['email']).'" required></div>';
echo '<div class="form-group"><label>Telepon</label><input type="text" name="phone" value="'.htmlspecialchars($data['phone']).'" required></div>';
echo '<div class="form-group"><label>Alamat</label><input type="text" name="address" value="'.htmlspecialchars($data['address']).'" required></div>';
echo '<div class="form-group"><label>Password (kosongkan jika tidak diubah)</label><input type="password" name="password"></div>';
echo '<div id="editError" style="color:#e74c3c;margin-bottom:8px;"></div>';
echo '<button type="submit" class="btn" style="width:100%;margin-top:10px;">Simpan</button>';
echo '</form>'; 