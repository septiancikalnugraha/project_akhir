<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'petugas') {
    echo '<div style="color:#e74c3c;">Akses ditolak.</div>';
    exit;
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM deposits WHERE id = $id AND deleted_at IS NULL";
$result = $conn->query($sql);
$data = $result && $result->num_rows ? $result->fetch_assoc() : null;
if(!$data) { echo '<div style="color:#e74c3c;">Data tidak ditemukan.</div>'; exit; }
echo '<form onsubmit="submitEditSimpanan(event,'.$id.')">';
echo '<h3 style="margin-top:0">Edit Simpanan</h3>';
echo '<div class="form-group"><label>Type</label><input type="text" name="type" value="'.htmlspecialchars($data['type']).'" required></div>';
echo '<div class="form-group"><label>Plan</label><input type="text" name="plan" value="'.htmlspecialchars($data['plan']).'" required></div>';
echo '<div class="form-group"><label>Subtotal</label><input type="number" name="subtotal" value="'.$data['subtotal'].'" required></div>';
echo '<div class="form-group"><label>Fee</label><input type="number" name="fee" value="'.$data['fee'].'" required></div>';
echo '<div class="form-group"><label>Total</label><input type="number" name="total" value="'.$data['total'].'" required></div>';
echo '<div class="form-group"><label>Fiscal Date</label><input type="datetime-local" name="fiscal_date" value="'.date('Y-m-d\TH:i', strtotime($data['fiscal_date'])).'" required></div>';
echo '<div class="form-group"><label>Status</label><select name="status" required><option value="pending"'.($data['status']=='pending'?' selected':'').'>Pending</option><option value="verified"'.($data['status']=='verified'?' selected':'').'>Verified</option></select></div>';
echo '<div id="editError" style="color:#e74c3c;margin-bottom:8px;"></div>';
echo '<button type="submit" class="btn">Simpan</button>';
echo '</form>'; 