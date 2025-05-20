<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo '<div style="color:#e74c3c;">Akses ditolak.</div>';
    exit;
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM users WHERE id = $id";
$result = $conn->query($sql);
$data = $result && $result->num_rows ? $result->fetch_assoc() : null;
if(!$data) { echo '<div style="color:#e74c3c;">Data tidak ditemukan.</div>'; exit; }
echo '<form onsubmit="submitEditUser(event,'.$id.')">';
echo '<h3 class="modal-title custom-modal-drag">Edit User</h3>';
echo '<div class="form-group"><label>Nama</label><input type="text" name="name" value="'.htmlspecialchars($data['name']).'" required></div>';
echo '<div class="form-group"><label>Email</label><input type="email" name="email" value="'.htmlspecialchars($data['email']).'" required></div>';
echo '<div class="form-group"><label>Role</label><select name="role" required><option value="petugas"'.($data['role']=='petugas'?' selected':'').'>Petugas</option><option value="ketua"'.($data['role']=='ketua'?' selected':'').'>Ketua</option><option value="anggota"'.($data['role']=='anggota'?' selected':'').'>Anggota</option></select></div>';
echo '<div class="form-group"><label>Password (kosongkan jika tidak diubah)</label><input type="password" name="password"></div>';
echo '<div id="editError" style="color:#e74c3c;margin-bottom:8px;"></div>';
echo '<button type="submit" class="btn" style="width:100%;margin-top:10px;">Simpan</button>';
echo '</form>'; 