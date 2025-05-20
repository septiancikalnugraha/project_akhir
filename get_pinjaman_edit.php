<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo '<div style="color:#e74c3c;">Akses ditolak.</div>';
    exit;
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM loans WHERE id = $id AND deleted_at IS NULL";
$result = $conn->query($sql);
$data = $result && $result->num_rows ? $result->fetch_assoc() : null;
if(!$data) { echo '<div style="color:#e74c3c;">Data tidak ditemukan.</div>'; exit; }
echo '<form onsubmit="submitEditPinjaman(event,'.$id.')">';
echo '<h3 class="modal-title custom-modal-drag">Edit Pinjaman</h3>';
echo '<div class="form-group"><label>Instalment</label><input type="text" name="instalment" value="'.htmlspecialchars($data['instalment']).'" required></div>';
echo '<div class="form-group"><label>Subtotal</label><input type="number" name="subtotal" value="'.$data['subtotal'].'" required></div>';
echo '<div class="form-group"><label>Fee</label><input type="number" name="fee" value="'.$data['fee'].'" required></div>';
echo '<div class="form-group"><label>Total</label><input type="number" name="total" value="'.$data['total'].'" required></div>';
echo '<div class="form-group"><label>Fiscal Date</label><input type="datetime-local" name="fiscal_date" value="'.date('Y-m-d\TH:i', strtotime($data['fiscal_date'])).'" required></div>';
echo '<div class="form-group"><label>Status</label><select name="status" required><option value="pending"'.($data['status']=='pending'?' selected':'').'>Pending</option><option value="loaned"'.($data['status']=='loaned'?' selected':'').'>Loaned</option></select></div>';
echo '<div id="editError" style="color:#e74c3c;margin-bottom:8px;"></div>';
echo '<button type="submit" class="btn">Simpan</button>';
echo '</form>'; 