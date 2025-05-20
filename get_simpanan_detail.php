<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) {
    echo '<div style="color:#e74c3c;">Akses ditolak.</div>';
    exit;
}
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT d.*, c.name as customer_name FROM deposits d LEFT JOIN customers c ON d.customer_id = c.id WHERE d.id = $id AND d.deleted_at IS NULL";
$result = $conn->query($sql);
$data = $result && $result->num_rows ? $result->fetch_assoc() : null;
if(!$data) {
    echo '<div style="color:#e74c3c;">Data tidak ditemukan.</div>';
    exit;
}
echo '<div class="detail-row"><span class="detail-label">Customer</span>: '.htmlspecialchars($data['customer_name']).'</div>';
echo '<div class="detail-row"><span class="detail-label">Type</span>: '.htmlspecialchars($data['type']).'</div>';
echo '<div class="detail-row"><span class="detail-label">Plan</span>: '.htmlspecialchars($data['plan']).'</div>';
echo '<div class="detail-row"><span class="detail-label">Status</span>: '.htmlspecialchars($data['status']).'</div>';
echo '<div class="detail-row"><span class="detail-label">Subtotal</span>: Rp '.number_format($data['subtotal'],0,',','.').'</div>';
echo '<div class="detail-row"><span class="detail-label">Fee</span>: Rp '.number_format($data['fee'],0,',','.').'</div>';
echo '<div class="detail-row"><span class="detail-label">Total</span>: Rp '.number_format($data['total'],0,',','.').'</div>';
echo '<div class="detail-row"><span class="detail-label">Fiscal Date</span>: '.date('d F Y H:i', strtotime($data['fiscal_date'])).'</div>';
echo '<div class="detail-row"><span class="detail-label">Created At</span>: '.date('d F Y H:i', strtotime($data['created_at'])).'</div>'; 