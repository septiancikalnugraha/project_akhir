<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user'])) { echo '<div style="color:#e74c3c;">Akses ditolak.</div>'; exit; }
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM users WHERE id = $id";
$result = $conn->query($sql);
$data = $result && $result->num_rows ? $result->fetch_assoc() : null;
if(!$data) { echo '<div style="color:#e74c3c;">Data tidak ditemukan.</div>'; exit; }
echo '<div class="detail-row"><span class="detail-label">Nama</span>: '.htmlspecialchars($data['name']).'</div>';
echo '<div class="detail-row"><span class="detail-label">Email</span>: '.htmlspecialchars($data['email']).'</div>';
echo '<div class="detail-row"><span class="detail-label">Role</span>: '.htmlspecialchars($data['role']).'</div>';
echo '<div class="detail-row"><span class="detail-label">Tanggal Dibuat</span>: '.($data['created_at']?date('d F Y', strtotime($data['created_at'])):'-').'</div>'; 