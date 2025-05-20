<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) { echo '<div style="color:#e74c3c;">Akses ditolak.</div>'; exit; }
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM customers WHERE id = $id AND deleted_at IS NULL";
$result = $conn->query($sql);
$data = $result && $result->num_rows ? $result->fetch_assoc() : null;
if(!$data) { echo '<div style="color:#e74c3c;">Data tidak ditemukan.</div>'; exit; }
echo '<div class="detail-row"><span class="detail-label">Nama</span>: '.htmlspecialchars($data['name']).'</div>';
echo '<div class="detail-row"><span class="detail-label">Email</span>: '.htmlspecialchars($data['email']).'</div>';
echo '<div class="detail-row"><span class="detail-label">Telepon</span>: '.htmlspecialchars($data['phone']).'</div>';
echo '<div class="detail-row"><span class="detail-label">Alamat</span>: '.htmlspecialchars($data['address']).'</div>';
echo '<div class="detail-row"><span class="detail-label">Tanggal Gabung</span>: '.date('d F Y', strtotime($data['created_at'])).'</div>'; 