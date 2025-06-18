<?php
session_start();
require 'db.php';

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Define the $role variable
$role = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : '';

// Jika role adalah anggota, alihkan ke pinjaman_anggota.php
if ($role == 'anggota') {
    header("Location: pinjaman_anggota.php");
    exit;
}

// --- DEBUG: Tampilkan role saat ini ---
// echo "<!-- Current Role: " . $role . " -->"; // Menghapus baris debug
// --- END DEBUG ---

// Ambil data pinjaman (loans) join customer
$sql = "SELECT l.*, c.name as customer_name FROM loans l
        LEFT JOIN customers c ON l.customer_id = c.id
        WHERE l.deleted_at IS NULL";

// Tambahkan filter untuk menghilangkan data kosong/tidak lengkap saat role adalah 'petugas' (jika diperlukan)
if ($role == 'petugas') {
     // Sesuaikan kondisi filter ini jika ada kriteria lain untuk data 'kosong' pinjaman
    $sql .= " AND (l.subtotal > 0 OR l.total > 0 OR (l.subtotal = 0 AND l.total = 0 AND l.fiscal_date IS NOT NULL AND l.fiscal_date != '1970-01-01 01:00:00'))";
}

$sql .= " ORDER BY l.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pinjaman - SIKOPIN</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Sidebar Improvements */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(135deg, #FFB266 0%, #FF9933 100%);
            border-right: none;
            padding: 0;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar h2 {
            text-align: center;
            font-size: 26px;
            margin: 25px 0 35px 0;
            font-weight: 700;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            padding: 0 20px;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0 15px;
            margin: 0;
        }
        
        .sidebar li {
            margin-bottom: 8px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .sidebar li a {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            text-decoration: none;
            color: #fff;
            font-size: 15px;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s ease;
            opacity: 0.9;
        }
        
        .sidebar li a span {
            margin-right: 12px;
            font-size: 18px;
        }
        
        .sidebar li:hover a {
            background-color: rgba(255,255,255,0.15);
            opacity: 1;
            transform: translateX(5px);
        }
        
        .sidebar li.active a {
            background-color: #fff;
            color: #FF9933;
            opacity: 1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-weight: 600;
        }

        /* Topbar */
        .topbar {
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            height: 65px;
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            z-index: 999;
        }
        
        .profile-dot {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #FF9933, #FFB266);
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .profile-dot:hover {
            transform: scale(1.1);
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            margin-top: 65px;
            padding: 25px 30px;
            min-height: calc(100vh - 65px);
        }

        /* Breadcrumb and Title */
        .breadcrumb {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .breadcrumb::before {
            content: "🏠";
            margin-right: 8px;
        }
        
        .page-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #2c3e50;
            display: flex;
            align-items: center;
        }
        
        .page-title::before {
            content: "💳";
            margin-right: 15px;
            font-size: 28px;
        }

        /* Card Container */
        .card-table {
            background: #fff;
            border-radius: 16px;
            border: none;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            overflow: hidden;
        }

        /* Action Bar */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .action-left {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .action-right {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #FF9933, #FFB266);
            color: #fff;
            box-shadow: 0 4px 12px rgba(255,153,51,0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255,153,51,0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }

        /* Table Styling */
        .table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            font-size: 14px;
            min-width: 600px;
        }
        
        .table th, .table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-size: 14px;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            font-size: 14px;
            transition: background-color 0.2s;
        }
        
        .table tr:hover {
            background-color: #f8f9fa;
        }

        /* Search and Filter */
        .table-search {
            padding: 10px 15px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            width: 250px;
            transition: all 0.3s ease;
        }
        
        .table-search:focus {
            border-color: #FF9933;
            box-shadow: 0 0 0 3px rgba(255,153,51,0.1);
            outline: none;
        }

        /* Badge Styling */
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge.loaned {
            background: #d4edda;
            color: #388e3c;
        }

        /* Pagination */
        .table-pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .per-halaman-select {
            padding: 8px 12px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .per-halaman-select:focus {
            border-color: #FF9933;
            box-shadow: 0 0 0 3px rgba(255,153,51,0.1);
            outline: none;
        }

        /* Modal Improvements */
        .custom-modal {
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .custom-modal-content {
            background: #fff;
            border-radius: 16px;
            max-width: 500px;
            width: 90vw;
            max-height: 90vh;
            overflow-y: auto;
            padding: 35px 30px 30px 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            position: relative;
            animation: modalSlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .custom-modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            background: none;
            border: none;
            font-size: 28px;
            color: #adb5bd;
            cursor: pointer;
            transition: color 0.2s ease;
            padding: 5px;
            line-height: 1;
        }
        
        .custom-modal-close:hover {
            color: #dc3545;
        }
        
        .modal-title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #2c3e50;
            cursor: move;
            user-select: none;
        }

        /* Form Improvements */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #FF9933;
            box-shadow: 0 0 0 3px rgba(255,153,51,0.1);
        }
        
        .form-group input[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        /* Detail Display */
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }
        
        .detail-label {
            font-weight: 600;
            color: #495057;
            width: 140px;
            flex-shrink: 0;
        }
        
        .detail-value {
            color: #2c3e50;
        }

        /* Error Message */
        .error-message {
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 6px;
            padding: 10px 15px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .sidebar {
                width: 240px;
            }
            .main-content {
                margin-left: 240px;
            }
            .topbar {
                left: 240px;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .topbar {
                left: 0;
            }
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .action-right {
                justify-content: stretch;
            }
            .search-input {
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 20px 15px;
            }
            .card-table {
                padding: 20px 15px;
            }
            .page-title {
                font-size: 28px;
            }
            .custom-modal-content {
                padding: 25px 20px;
                margin: 20px;
            }
        }

        /* Print Styles */
        @media print {
            body, html {
                background: #fff !important;
            }
            .sidebar, .topbar, .btn, .table-pagination, .breadcrumb, .profile-dot, .custom-modal, .page-title, .action-bar {
                display: none !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .card-table {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
            }
            .table {
                font-size: 12px !important;
            }
            .table th, .table td {
                padding: 8px 6px !important;
            }
        }

        /* Search Container */
        .search-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 250px;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .search-input:focus {
            outline: none;
            border-color: #FFB266;
            box-shadow: 0 0 0 3px rgba(255,178,102,0.25);
        }

        /* Table Styles */
        .table-container {
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>SIKOPIN</h2>
        <ul>
            <li class="<?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo 'active'; ?>">
                <a href="dashboard.php">
                    <span>&#128200; Dasboard</span>
                </a>
            </li>
            <?php if($role == 'petugas' || $role == 'ketua'): ?>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='simpanan.php') echo 'active'; ?>">
                    <a href="simpanan.php">
                        <span>&#128179; Simpanan</span>
                    </a>
                </li>
                <li class="active">
                    <a href="pinjaman.php">
                        <span>&#128181; Pinjaman</span>
                    </a>
                </li>
                <li>
                    <a href="anggota.php">
                        <span>&#128101; Anggota</span>
                    </a>
                </li>
                <?php if($role == 'petugas'): ?>
                <li>
                    <a href="user.php">
                        <span>&#9881; User</span>
                    </a>
                </li>
                <?php endif; ?>
            <?php elseif($role == 'anggota'): ?>
                 <li>
                    <a href="simpanan_anggota.php">
                        <span>&#128179; Simpanan</span>
                    </a>
                </li>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='pinjaman_anggota.php') echo 'active'; ?>">
                    <a href="pinjaman_anggota.php">
                        <span>&#128181; Pinjaman Saya</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="topbar">
        <div></div>
        <div class="profile-dot"></div>
    </div>
    <div class="main-content">
        <div class="breadcrumb">Home / Pinjaman</div>
        <h1 class="page-title">Daftar Pinjaman</h1>
        
        <div class="card-table">
            <div class="action-bar">
                <div class="action-left">
                    <?php if($role == 'petugas' || $role == 'ketua'): ?>
                        <button class="btn btn-primary" onclick="openTambahModal()">
                            ➕ Tambah Baru
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-secondary" onclick="printFullTablePinjaman()">
                        🖨️ Cetak
                    </button>
                </div>
                <div class="action-right">
                    <div class="search-container">
                        <input type="text" class="search-input" id="searchInput" placeholder="Cari pinjaman...">
                        <button class="btn btn-outline" onclick="searchPinjaman()">🔍 Cari</button>
                    </div>
                </div>
            </div>
            
            <div class="table-container">
                <table class="table" id="pinjamanTable">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th style="min-width: 150px;">Customer</th>
                            <th style="min-width: 100px;">Tanggal</th>
                            <th style="min-width: 120px;">Subtotal</th>
                            <th style="min-width: 120px;">Total</th>
                            <th style="min-width: 100px;">Status</th>
                            <th style="min-width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()): 
                                $currentStatus = isset($row['status']) ? $row['status'] : '';
                                $statusClass = ('loaned' == $currentStatus) ? 'badge-success' : 'badge-warning';
                                $statusText = ('loaned' == $currentStatus) ? 'Dipinjam' : 'Belum Dipinjam';
                        ?>
                            <tr>
                                <td><strong><?php echo $no++; ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($row['customer_name']); ?></strong></td>
                                <td><?php echo date('d M Y H:i', strtotime($row['fiscal_date'])); ?></td>
                                <td><strong>Rp <?php echo number_format($row['subtotal'], 0, ',', '.'); ?></strong></td>
                                <td><strong style="color: #28a745;">Rp <?php echo number_format($row['total'], 0, ',', '.'); ?></strong></td>
                                <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                <td class="table-actions">
                                    <button class="btn btn-outline" onclick="showDetailModal(<?php echo $row['id']; ?>)">👁️ View</button>
                                    <?php if($role == 'petugas'): ?>
                                        <button class="btn btn-outline" onclick="openEditModal(<?php echo $row['id']; ?>)">✏️ Edit</button>
                                        <button class="btn btn-danger" onclick="hapusPinjaman(<?php echo $row['id']; ?>)">🗑️ Hapus</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        } else {
                            echo "<tr><td colspan='7' style='text-align:center; padding: 40px; color: #6c757d;'>\n                                <div style='font-size: 48px; margin-bottom: 15px;'>📭</div>\n                                <div style='font-size: 18px; font-weight: 600;'>Tidak ada data pinjaman</div>\n                                <div style='font-size: 14px; margin-top: 5px;'>Belum ada transaksi pinjaman yang tercatat</div>\n                            </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="table-pagination">
                <div class="pagination-info">
                    Menampilkan <strong><?php echo ($no > 1) ? ($no-1) : 0; ?></strong> dari <strong><?php echo ($no > 1) ? ($no-1) : 0; ?></strong> data
                </div>
                <div class="pagination-controls">
                    <span style="margin-right: 10px;">Per halaman:</span>
                    <select>
                        <option>10</option>
                        <option>20</option>
                        <option>50</option>
                        <option>100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Detail Pinjaman -->
    <div id="detailModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeDetailModal()" class="custom-modal-close">&times;</button>
            <div id="modalContent"><div style="text-align: center; padding: 40px;"><div class="spinner" style="margin: 0 auto 15px;"></div><div>Memuat data...</div></div></div>
        </div>
    </div>
    <!-- Modal Tambah Pinjaman -->
    <div id="tambahModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeTambahModal()" class="custom-modal-close">&times;</button>
            <form id="formTambahPinjaman">
                <h3 class="modal-title">➕ Tambah Pinjaman Baru</h3>
                <div class="form-group">
                    <label>👤 Customer</label>
                    <input type="text" id="tambah-customer-name" readonly required placeholder="Pilih Customer">
                    <input type="hidden" id="tambah-customer-id" name="customer_id" required>
                    <button type="button" class="btn btn-outline" onclick="openCustomerSelectionModal('tambah')" style="margin-top: 8px;">
                        🔍 Pilih Customer
                    </button>
                </div>
                <div class="form-group">
                    <label>🔢 Jumlah Cicilan</label>
                    <input type="number" name="instalment" required placeholder="0" min="0">
                </div>
                <div class="form-group">
                    <label>💵 Subtotal</label>
                    <input type="number" name="subtotal" id="subtotal" required placeholder="0" min="0">
                </div>
                <div class="form-group">
                    <label>💰 Fee</label>
                    <input type="number" name="fee" id="fee" required placeholder="0" min="0">
                </div>
                <div class="form-group">
                    <label>💳 Total</label>
                    <input type="number" name="total" id="total" required placeholder="0" min="0" readonly>
                </div>
                <div class="form-group">
                    <label>🏷️ Fiscal Date</label>
                    <input type="datetime-local" name="fiscal_date" required>
                </div>
                <div class="form-group">
                    <label>🏷️ Status</label>
                    <select name="status" required>
                        <option value="">Pilih Status</option>
                        <option value="pending">Pending</option>
                        <option value="loaned">Dipinjam</option>
                        <option value="paid">Lunas</option>
                    </select>
                </div>
                <div id="tambahError" class="error-message" style="display: none;"></div>
                <button type="submit" class="btn btn-primary" style="width:100%; padding: 15px; font-size: 16px; margin-top: 20px;">
                    💾 Simpan Data
                </button>
            </form>
        </div>
    </div>
    <!-- Modal Edit Pinjaman -->
    <div id="editModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button class="custom-modal-close" onclick="closeEditModal()">&times;</button>
            <div id="editContent">
                <div style="text-align: center; padding: 40px;">
                    <div class="spinner"></div>
                    <div>Memuat form edit...</div>
                </div>
            </div>
        </div>
    </div>
     <!-- Modal Customer Selection -->
    <div id="customerSelectionModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button class="custom-modal-close" onclick="closeCustomerSelectionModal()">&times;</button>
            <h3 class="modal-title custom-modal-drag">👥 Pilih Customer</h3>
            <div class="form-group">
                <label>🔍 Cari Customer</label>
                <input type="text" id="customerSearchInput" placeholder="Ketik nama customer..." onkeyup="searchCustomers()" class="search-input">
            </div>
            <div id="customerResults" style="max-height:350px; overflow-y:auto; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px;">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <div style="font-size: 24px; margin-bottom: 10px;">👥</div>
                    <div>Ketik untuk mencari customer</div>
                </div>
            </div>
            <div id="customerSelectionError" class="error-message" style="display: none;"></div>
        </div>
    </div>
    <script>
     let currentFormType = ''; // 'tambah' or 'edit'

    function showDetailModal(id) {
        document.getElementById('detailModal').style.display = 'flex';
        document.getElementById('modalContent').innerHTML = '<div style="text-align: center; padding: 40px;"><div class="spinner" style="margin: 0 auto 15px;"></div><div>Memuat data...</div></div>';
        fetch('get_pinjaman_detail.php?id=' + id)
            .then(r => r.text())
            .then(html => {
                document.getElementById('modalContent').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('modalContent').innerHTML = `<div style="text-align: center; padding: 40px; color: #dc3545;"><div style="font-size: 48px; margin-bottom: 15px;">❌</div><div>Gagal memuat detail data.</div></div>`;
            });
    }
    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
        document.getElementById('modalContent').innerHTML = 'Loading...';
    }
    function openTambahModal() {
        currentFormType = 'tambah';
        document.getElementById('tambahModal').style.display = 'flex';
        document.getElementById('formTambahPinjaman').reset();
        document.getElementById('tambahError').innerText = '';
        document.getElementById('formTambahPinjaman').querySelector('button[type=submit]').disabled = false;
        document.getElementById('formTambahPinjaman').querySelector('button[type=submit]').innerHTML = '💾 Simpan Data';
        document.getElementById('tambah-customer-name').value = '';
        document.getElementById('tambah-customer-id').value = '';
        
        // Set default datetime to now, matching simpanan.php
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.querySelector('#formTambahPinjaman input[name="fiscal_date"]').value = now.toISOString().slice(0, 16);

        setTimeout(setupAutoCalculation, 100);
    }
    function closeTambahModal() {
        document.getElementById('tambahModal').style.display = 'none';
        document.getElementById('formTambahPinjaman').reset();
        document.getElementById('tambahError').innerText = '';
        document.getElementById('formTambahPinjaman').querySelector('button[type=submit]').disabled = false;
        document.getElementById('formTambahPinjaman').querySelector('button[type=submit]').innerHTML = '💾 Simpan Data';
         document.getElementById('tambah-customer-name').value = '';
        document.getElementById('tambah-customer-id').value = '';
    }
    document.getElementById('formTambahPinjaman')?.addEventListener('submit', function(e) {
        e.preventDefault();
        var form = e.target;
        var btn = form.querySelector('button[type=submit]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Menyimpan...';
        var data = new FormData(form);
        fetch('aksi_tambah_pinjaman.php', { method: 'POST', body: data })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    closeTambahModal();
                    alert(res.message || 'Pinjaman berhasil ditambahkan!');
                    location.reload();
                } else {
                    alert(res.error || 'Gagal menambah data pinjaman!');
                    document.getElementById('tambahError').innerText = res.error || 'Gagal menambah data.';
                    btn.disabled = false;
                    btn.innerHTML = '💾 Simpan Data';
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan sistem.');
                document.getElementById('tambahError').innerText = 'Terjadi kesalahan sistem.';
                btn.disabled = false;
                btn.innerHTML = '💾 Simpan Data';
            });
    });
    function openEditModal(id) {
         currentFormType = 'edit';
        document.getElementById('editModal').style.display = 'flex';
        document.getElementById('editContent').innerHTML = '<div style="text-align: center; padding: 40px;"><div class="spinner" style="margin: 0 auto 15px;"></div><div>Memuat form edit...</div></div>';
        fetch('get_pinjaman_edit.php?id=' + id)
            .then(r => r.text())
            .then(html => { 
                document.getElementById('editContent').innerHTML = html;
                const form = document.querySelector('#editContent form');
                if (form) {
                    form.onsubmit = function(e) {
                        submitEditPinjaman(e, id);
                    };
                    setTimeout(setupAutoCalculation, 100); // Setup auto-calculation for edit modal as well
                }
            })
            .catch(error => {
                document.getElementById('editContent').innerHTML = `<div style="text-align: center; padding: 40px; color: #dc3545;"><div style="font-size: 48px; margin-bottom: 15px;">❌</div><div>Gagal memuat form edit.</div></div>`;
            });
    }
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('editContent').innerHTML = 'Loading...';
    }
    function submitEditPinjaman(e, id) {
        e.preventDefault();
        var form = e.target;
        var data = new FormData(form);
        data.append('id', id);
        
        var btn = form.querySelector('button[type=submit]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Menyimpan...';
        
        fetch('aksi_edit_pinjaman.php', {
            method: 'POST',
            body: data
        })
        .then(r=>r.json())
        .then(res=>{
            if(res.success) { 
                location.reload(); 
            } else { 
                document.getElementById('editError').innerText = res.error || 'Gagal mengedit data.';
                btn.disabled = false;
                btn.innerHTML = 'Simpan';
            }
        })
        .catch(error => {
            document.getElementById('editError').innerText = 'Terjadi kesalahan sistem.';
            btn.disabled = false;
            btn.innerHTML = 'Simpan';
        });
    }
     // Customer Selection Modal Functions
    function openCustomerSelectionModal(formType) {
        currentFormType = formType; // 'tambah' or 'edit'
        document.getElementById('customerSelectionModal').style.display = 'flex';
        document.getElementById('customerSearchInput').value = ''; // Clear previous search
        searchCustomers(); // Load all customers initially
    }

    function closeCustomerSelectionModal() {
        document.getElementById('customerSelectionModal').style.display = 'none';
         document.getElementById('customerResults').innerHTML = ''; // Clear results
         document.getElementById('customerSelectionError').innerText = ''; // Clear errors
    }

    function searchCustomers() {
        const query = document.getElementById('customerSearchInput').value;
        const resultsDiv = document.getElementById('customerResults');
         const errorDiv = document.getElementById('customerSelectionError');
        resultsDiv.innerHTML = '<div style="text-align: center; padding: 40px;"><div class="spinner" style="margin: 0 auto 15px;"></div><div>Memuat data...</div></div>';
         errorDiv.innerText = '';

        fetch('get_customers.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                resultsDiv.innerHTML = ''; // Clear loading
                if (data.success) {
                    if (data.customers.length > 0) {
                        data.customers.forEach(customer => {
                            const customerDiv = document.createElement('div');
                            customerDiv.style.padding = '10px';
                            customerDiv.style.borderBottom = '1px solid #eee';
                            customerDiv.style.cursor = 'pointer';
                            customerDiv.textContent = customer.name + ' (' + customer.role + ')';
                            customerDiv.onclick = () => selectCustomer(customer.id, customer.name);
                            resultsDiv.appendChild(customerDiv);
                        });
                    } else {
                        resultsDiv.innerHTML = `<div style="text-align: center; padding: 40px; color: #6c757d;"><div style="font-size: 48px; margin-bottom: 15px;">🔍</div><div style="font-weight: 600; margin-bottom: 5px;">Customer tidak ditemukan</div><div style="font-size: 14px;">Coba gunakan kata kunci lain</div></div>`;
                    }
                } else {
                     errorDiv.innerText = 'Error: ' + (data.error || 'Gagal memuat data customer');
                     errorDiv.style.display = 'block';
                     resultsDiv.innerHTML = `<div style="text-align: center; padding: 40px; color: #dc3545;"><div style="font-size: 48px; margin-bottom: 15px;">❌</div><div>Gagal memuat data customer</div></div>`;
                }
            })
            .catch(error => {
                console.error('Error fetching customers:', error);
                errorDiv.innerText = 'Terjadi kesalahan jaringan.';
                errorDiv.style.display = 'block';
                resultsDiv.innerHTML = `<div style="text-align: center; padding: 40px; color: #dc3545;"><div style="font-size: 48px; margin-bottom: 15px;">🌐</div><div>Kesalahan jaringan</div></div>`;
            });
    }

    function selectCustomer(id, name) {
        if (currentFormType === 'tambah') {
            document.getElementById('tambah-customer-id').value = id;
            document.getElementById('tambah-customer-name').value = name;
        } else if (currentFormType === 'edit') {
            const editCustomerId = document.querySelector('#editContent #edit-customer-id');
            const editCustomerName = document.querySelector('#editContent #edit-customer-name');
            if (editCustomerId && editCustomerName) {
                editCustomerId.value = id;
                editCustomerName.value = name;
            }
        }
        closeCustomerSelectionModal();
    }

    // DRAGGABLE MODAL
    function makeModalDraggable(modalSelector, dragSelector) {
        const modal = document.querySelector(modalSelector);
        const dragArea = modal.querySelector(dragSelector);
        
        if (!dragArea) return;
        
        let isDown = false, offsetX = 0, offsetY = 0;
        
        dragArea.addEventListener('mousedown', function(e) {
            isDown = true;
            const content = modal.querySelector('.custom-modal-content');
            const rect = content.getBoundingClientRect();
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;
            content.style.position = 'fixed';
            content.style.margin = 0;
            document.body.style.userSelect = 'none';
            dragArea.style.cursor = 'grabbing';
        });
        
        document.addEventListener('mousemove', function(e) {
            if (!isDown) return;
            const content = modal.querySelector('.custom-modal-content');
            const newX = e.clientX - offsetX;
            const newY = e.clientY - offsetY;
            
            const maxX = window.innerWidth - content.offsetWidth;
            const maxY = window.innerHeight - content.offsetHeight;
            
            content.style.left = Math.max(0, Math.min(newX, maxX)) + 'px';
            content.style.top = Math.max(0, Math.min(newY, maxY)) + 'px';
        });
        
        document.addEventListener('mouseup', function() {
            isDown = false;
            document.body.style.userSelect = '';
            if (dragArea) {
                dragArea.style.cursor = 'move';
            }
        });
    }

    // Initialize draggable modals when DOM is loaded
    window.addEventListener('DOMContentLoaded', function() {
        makeModalDraggable('#tambahModal', '.modal-title');
        makeModalDraggable('#editModal', '.modal-title');
        makeModalDraggable('#detailModal', '.modal-title');
        makeModalDraggable('#customerSelectionModal', '.modal-title');
    });
    
    // Close modals when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('custom-modal')) {
            if (e.target.id === 'detailModal') closeDetailModal();
            else if (e.target.id === 'tambahModal') closeTambahModal();
            else if (e.target.id === 'editModal') closeEditModal();
            else if (e.target.id === 'customerSelectionModal') closeCustomerSelectionModal();
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // ESC to close modals
        if (e.key === 'Escape') {
            if (document.getElementById('detailModal').style.display === 'flex') closeDetailModal();
            else if (document.getElementById('tambahModal').style.display === 'flex') closeTambahModal();
            else if (document.getElementById('editModal').style.display === 'flex') closeEditModal();
            else if (document.getElementById('customerSelectionModal').style.display === 'flex') closeCustomerSelectionModal();
        }
        
        // Ctrl+F to focus search
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            document.getElementById('searchInput').focus();
        }
    });
    
    // Auto-calculate total when subtotal or fee changes
    function setupAutoCalculation() {
        // Untuk modal tambah
        const subtotalInput = document.querySelector('#tambahModal input[name="subtotal"]');
        const feeInput = document.querySelector('#tambahModal input[name="fee"]');
        const totalInput = document.querySelector('#tambahModal input[name="total"]');
        function updateTotal() {
            const subtotal = parseFloat(subtotalInput.value) || 0;
            const fee = parseFloat(feeInput.value) || 0;
            totalInput.value = subtotal + fee;
        }
        if(subtotalInput && feeInput && totalInput) {
            subtotalInput.addEventListener('input', updateTotal);
            feeInput.addEventListener('input', updateTotal);
            updateTotal();
        }
        // Untuk modal edit
        const editSubtotal = document.getElementById('edit-subtotal');
        const editFee = document.getElementById('edit-fee');
        const editTotal = document.getElementById('edit-total');
        function updateEditTotal() {
            const subtotal = parseFloat(editSubtotal.value) || 0;
            const fee = parseFloat(editFee.value) || 0;
            editTotal.value = subtotal + fee;
        }
        if(editSubtotal && editFee && editTotal) {
            editSubtotal.addEventListener('input', updateEditTotal);
            editFee.addEventListener('input', updateEditTotal);
            updateEditTotal();
        }
    }

    // Initialize auto-calculation when tambah modal opens
    const originalOpenTambahModal = openTambahModal;
    openTambahModal = function() {
        originalOpenTambahModal();
        setTimeout(setupAutoCalculation, 100);
    };

    // Print Full Table Function for Pinjaman
    let originalDisplayPinjaman = {}; // To store original display styles for pinjaman
    function printFullTablePinjaman() {
        const tableBody = document.querySelector('#pinjamanTable tbody');
        const rows = tableBody ? tableBody.querySelectorAll('tr') : [];
        const paginationControls = document.querySelector('.table-pagination');
        const searchContainer = document.querySelector('.search-container');
        const actionLeft = document.querySelector('.action-left');

        // Store original display and show all rows
        rows.forEach((row, index) => {
            originalDisplayPinjaman[index] = row.style.display; // Store original display
            row.style.display = ''; // Show all rows
        });

        // Hide elements not needed for print
        if (paginationControls) paginationControls.style.display = 'none';
        if (searchContainer) searchContainer.style.display = 'none';
        if (actionLeft) {
            actionLeft.querySelectorAll('button').forEach(button => {
                if (button.innerText.includes('Tambah Baru')) {
                    button.style.display = 'none';
                }
            });
        }

        // Set a timeout to ensure display changes apply before printing
        setTimeout(() => {
            window.print();
        }, 300);

        // Restore original display after print (or if print is cancelled)
        window.onafterprint = () => {
            rows.forEach((row, index) => {
                row.style.display = originalDisplayPinjaman[index]; // Restore original display
            });
            if (paginationControls) paginationControls.style.display = ''; // Show pagination again
            if (searchContainer) searchContainer.style.display = ''; // Show search again
            if (actionLeft) {
                actionLeft.querySelectorAll('button').forEach(button => {
                    if (button.innerText.includes('Tambah Baru')) {
                        button.style.display = '';
                    }
                });
            }
        };
    }

    function searchPinjaman() {
        const keyword = document.getElementById('searchInput').value;
        const tableBody = document.querySelector('#pinjamanTable tbody');
        
        if (!tableBody) return;
        
        if (keyword.trim() === '') {
            location.reload();
            return;
        }
        
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px;">
                    <div class="spinner" style="margin: 0 auto 15px;"></div>
                    <div>Mencari data...</div>
                </td>
            </tr>
        `;
        
        fetch('get_pinjaman_search.php?q=' + encodeURIComponent(keyword))
            .then(response => response.text())
            .then(html => {
                document.getElementById('pinjamanTable').innerHTML = html;
            })
            .catch(error => {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #dc3545;">
                            <div style="font-size: 48px; margin-bottom: 15px;">❌</div>
                            <div>Gagal mencari data</div>
                        </td>
                    </tr>
                `;
            });
    }
    
    // Enter key search
    document.getElementById('searchInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            searchPinjaman();
        }
    });

    function hapusPinjaman(id) {
        if (confirm('Apakah Anda yakin ingin menghapus pinjaman ini?')) {
            fetch('hapus_pinjaman.php?id=' + id)
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert(res.error || 'Gagal menghapus data.');
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan saat menghapus data.');
                });
        }
    }
    </script>
</body>
</html>
