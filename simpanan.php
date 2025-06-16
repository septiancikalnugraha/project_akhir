<?php
session_start();
require 'db.php';

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$role = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : '';

// Ambil data simpanan (deposits) join customer
if($role != 'anggota') {
    $sql = "SELECT d.*, c.name as customer_name FROM deposits d
            LEFT JOIN customers c ON d.customer_id = c.id
            WHERE d.deleted_at IS NULL";
    if ($role == 'petugas') {
        $sql .= " AND (d.subtotal > 0 OR d.total > 0 OR (d.subtotal = 0 AND d.total = 0 AND d.fiscal_date IS NOT NULL AND d.fiscal_date != '1970-01-01 01:00:00'))";
    }
    $sql .= " ORDER BY d.created_at DESC";
    $result = $conn->query($sql);
} else {
    header("Location: simpanan_anggota.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simpanan - SIKOPIN</title>
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
            content: "💰";
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
            transform: translateY(-1px);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid #FF9933;
            color: #FF9933;
        }
        
        .btn-outline:hover {
            background: #FF9933;
            color: #fff;
        }
        
        .btn-danger {
            background: #dc3545;
            color: #fff;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }

        /* Search Input */
        .search-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .search-input {
            padding: 10px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            width: 250px;
            transition: border-color 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #FF9933;
            box-shadow: 0 0 0 3px rgba(255,153,51,0.1);
        }

        /* Table Improvements */
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            font-size: 14px;
        }
        
        .table th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 18px 16px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-size: 13px;
            color: #495057;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .table td {
            padding: 16px;
            border-bottom: 1px solid #f1f3f4;
            font-size: 14px;
            transition: background-color 0.2s ease;
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            white-space: nowrap;
        }
        
        .badge-default {
            background: #f8f9fa;
            color: #6c757d;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Table Actions */
        .table-actions {
            text-align: right;
            white-space: nowrap;
        }
        
        .table-actions .btn {
            padding: 6px 12px;
            font-size: 12px;
            margin-left: 5px;
        }

        /* Pagination */
        .table-pagination {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .pagination-info {
            color: #6c757d;
            font-size: 14px;
        }
        
        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .pagination-controls select {
            padding: 6px 10px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
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
            *, ::before, ::after {
                box-sizing: border-box !important;
            }
            body, html {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                min-width: 0 !important;
                max-width: 100vw !important;
                box-sizing: border-box !important;
            }
            .sidebar, .topbar, .btn, .table-pagination, .breadcrumb, .profile-dot, .custom-modal, .page-title, .action-bar {
                display: none !important;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100vw !important;
            }
            .card-table {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                overflow: visible !important;
                width: 100% !important;
                max-width: 100vw !important;
            }
            .table-container {
                overflow-x: visible !important;
                width: 100% !important;
                max-width: 100vw !important;
            }
            .table {
                font-size: 11px !important;
                width: 100% !important;
                min-width: auto !important;
                table-layout: auto !important;
                word-break: break-word !important;
                border-collapse: collapse !important;
                border-spacing: 0 !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            .table th, .table td {
                padding: 2px 1px !important;
                white-space: normal !important;
                width: auto !important;
                min-width: auto !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                box-sizing: border-box !important;
            }
            .table-actions {
                white-space: normal !important;
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 2px !important;
                justify-content: flex-start !important;
            }
            .table-actions .btn {
                font-size: 10px !important;
                padding: 3px 6px !important;
                margin: 1px !important;
                height: auto !important;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>SIKOPIN</h2>
        <ul>
            <li class="<?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo 'active'; ?>">
                <a href="dashboard.php">
                    <span>📊</span> Dashboard
                </a>
            </li>
            <?php if($role == 'petugas' || $role == 'ketua'): ?>
                <li class="active">
                    <a href="simpanan.php">
                        <span>💰</span> Simpanan
                    </a>
                </li>
                <li>
                    <a href="pinjaman.php">
                        <span>💳</span> Pinjaman
                    </a>
                </li>
                <li>
                    <a href="anggota.php">
                        <span>👥</span> Anggota
                    </a>
                </li>
                <?php if($role == 'petugas'): ?>
                <li>
                    <a href="user.php">
                        <span>⚙️</span> User
                    </a>
                </li>
                <?php endif; ?>
            <?php elseif($role == 'anggota'): ?>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='simpanan_anggota.php') echo 'active'; ?>">
                    <a href="simpanan_anggota.php">
                        <span>💰</span> Simpanan
                    </a>
                </li>
                <li>
                    <a href="pinjaman.php">
                        <span>💳</span> Pinjaman Saya
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Topbar -->
    <div class="topbar">
        <div class="profile-dot"></div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="breadcrumb">Simpanan › Daftar</div>
        <div class="page-title">Simpanan</div>
        
        <div class="card-table">
            <div class="action-bar">
                <div class="action-left">
                    <?php if($role == 'petugas' || $role == 'ketua'): ?>
                        <button class="btn btn-primary" onclick="openTambahModal()">
                            ➕ Tambah Baru
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-secondary" onclick="printFullTableSimpanan()">
                        🖨️ Cetak
                    </button>
                </div>
                <div class="action-right">
                    <div class="search-container">
                        <input type="text" class="search-input" id="searchInput" placeholder="Cari simpanan...">
                        <button class="btn btn-outline" onclick="searchSimpanan()">🔍 Cari</button>
                    </div>
                </div>
            </div>
            
            <div class="table-container">
                <table class="table" id="simpananTable">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Subtotal</th>
                            <th>Fee</th>
                            <th>Total</th>
                            <th>Fiscal Date</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $statusClass = $row['status'] == 'verified' ? 'badge-success' : 'badge-warning';
                                echo "<tr>
                                    <td><strong>{$no}</strong></td>
                                    <td><strong>{$row['customer_name']}</strong></td>
                                    <td><span class='badge badge-info'>{$row['type']}</span></td>
                                    <td><span class='badge badge-default'>{$row['plan']}</span></td>
                                    <td><span class='badge {$statusClass}'>{$row['status']}</span></td>
                                    <td><strong>Rp " . number_format($row['subtotal'],0,',','.') . "</strong></td>
                                    <td>Rp " . number_format($row['fee'],0,',','.') . "</td>
                                    <td><strong style='color: #28a745;'>Rp " . number_format($row['total'],0,',','.') . "</strong></td>
                                    <td>" . date('d M Y H:i', strtotime($row['fiscal_date'])) . "</td>
                                    <td class='table-actions'>
                                        <button class='btn btn-outline' onclick='showDetailModal({$row['id']})'>👁️ View</button>";
                                        if($role == 'petugas') {
                                            echo "<button class='btn btn-outline' onclick='openEditModal({$row['id']})'>✏️ Edit</button>
                                            <a href='hapus_simpanan.php?id={$row['id']}' class='btn btn-danger' onclick=\"return confirm('Yakin ingin menghapus data ini?');\">🗑️ Hapus</a>";
                                        }
                                echo "</td>
                                </tr>";
                                $no++;
                            }
                        } else {
                            echo "<tr><td colspan='10' style='text-align:center; padding: 40px; color: #6c757d;'>
                                <div style='font-size: 48px; margin-bottom: 15px;'>📭</div>
                                <div style='font-size: 18px; font-weight: 600;'>Tidak ada data simpanan</div>
                                <div style='font-size: 14px; margin-top: 5px;'>Belum ada transaksi simpanan yang tercatat</div>
                            </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="table-pagination">
                <div class="pagination-info">
                    Menampilkan <strong>1</strong> dari <strong><?php echo $no-1; ?></strong> data
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

    <!-- Modal Detail -->
    <div id="detailModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeDetailModal()" class="custom-modal-close">&times;</button>
            <div id="modalContent">
                <div style="text-align: center; padding: 40px;">
                    <div class="spinner"></div>
                    <div>Memuat data...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Simpanan -->
    <div id="tambahModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeTambahModal()" class="custom-modal-close">&times;</button>
            <form id="formTambahSimpanan">
                <h3 class="modal-title">➕ Tambah Simpanan Baru</h3>
                
                <div class="form-group">
                    <label>👤 Customer</label>
                    <input type="text" id="tambah-customer-name" readonly required placeholder="Pilih Customer">
                    <input type="hidden" id="tambah-customer-id" name="customer_id" required>
                    <button type="button" class="btn btn-outline" onclick="openCustomerSelectionModal('tambah')" style="margin-top: 8px;">
                        🔍 Pilih Customer
                    </button>
                </div>
                
                <div class="form-group">
                    <label>📋 Type</label>
                    <input type="text" name="type" required placeholder="Masukkan tipe simpanan">
                </div>
                
                <div class="form-group">
                    <label>📊 Plan</label>
                    <input type="text" name="plan" required placeholder="Masukkan rencana simpanan">
                </div>
                
                <div class="form-group">
                    <label>💵 Subtotal</label>
                    <input type="number" name="subtotal" required placeholder="0" min="0">
                </div>
                
                <div class="form-group">
                    <label>💰 Fee</label>
                    <input type="number" name="fee" required placeholder="0" min="0">
                </div>
                
                <div class="form-group">
                    <label>💳 Total</label>
                    <input type="number" name="total" required placeholder="0" min="0">
                </div>
                
                <div class="form-group">
                    <label>📅 Fiscal Date</label>
                    <input type="datetime-local" name="fiscal_date" required>
                </div>
                
                <div class="form-group">
                    <label>🏷️ Status</label>
                    <select name="status" required>
                        <option value="">Pilih Status</option>
                        <option value="pending">Pending</option>
                        <option value="verified">Verified</option>
                    </select>
                </div>
                
                <div id="tambahError" class="error-message" style="display: none;"></div>
                
                <button type="submit" class="btn btn-primary" style="width:100%; padding: 15px; font-size: 16px; margin-top: 20px;">
                    💾 Simpan Data
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Simpanan -->
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
            <h3 class="modal-title">👥 Pilih Customer</h3>
            
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
    
    // Detail Modal Functions
    function showDetailModal(id) {
        document.getElementById('detailModal').style.display = 'flex';
        document.getElementById('modalContent').innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <div class="spinner"></div>
                <div>Memuat detail data...</div>
            </div>
        `;
        
        fetch('get_simpanan_detail.php?id=' + id)
            .then(response => response.text())
            .then(html => {
                document.getElementById('modalContent').innerHTML = html;
            })
            .catch(error => {
                document.getElementById('modalContent').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #dc3545;">
                        <div style="font-size: 48px; margin-bottom: 15px;">❌</div>
                        <div>Gagal memuat detail data</div>
                    </div>
                `;
            });
    }
    
    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
    }
    
    // Tambah Modal Functions
    function openTambahModal() {
        currentFormType = 'tambah';
        document.getElementById('tambahModal').style.display = 'flex';
        // Set default datetime to now
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.querySelector('input[name="fiscal_date"]').value = now.toISOString().slice(0, 16);
    }
    
    function closeTambahModal() {
        document.getElementById('tambahModal').style.display = 'none';
        document.getElementById('formTambahSimpanan').reset();
        document.getElementById('tambahError').style.display = 'none';
        document.getElementById('tambahError').innerText = '';
        
        const submitBtn = document.getElementById('formTambahSimpanan').querySelector('button[type=submit]');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '💾 Simpan Data';
        
        // Clear customer fields
        document.getElementById('tambah-customer-name').value = '';
        document.getElementById('tambah-customer-id').value = '';
    }
    
    // Form Submit Handler
    document.getElementById('formTambahSimpanan')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = $(e.target); // Wrap the native form element with jQuery
        const btn = form.find('button[type=submit]'); // Use jQuery find
        const errorDiv = $('#tambahError'); // Use jQuery for error div
        
        // Reset error
        errorDiv.hide();
        errorDiv.text('');
        
        // Disable button and show loading
        btn.prop('disabled', true); // Use prop for properties
        btn.html('<span class="spinner"></span> Menyimpan...');
        
        // Validate form fields
        const customer_id = form.find('input[name=customer_id]').val();
        const type = form.find('input[name=type]').val();
        const plan = form.find('input[name=plan]').val();
        const subtotal = form.find('input[name=subtotal]').val();
        const fee = form.find('input[name=fee]').val();
        const total = form.find('input[name=total]').val();
        const fiscal_date = form.find('input[name=fiscal_date]').val();
        const status = form.find('select[name=status]').val();

        if (!customer_id || !type || !plan || !subtotal || !fee || !total || !fiscal_date || !status) {
            errorDiv.text('Semua field harus diisi').show();
            btn.prop('disabled', false);
            btn.html('💾 Simpan Data');
            return;
        }

        if (parseFloat(total) <= 0) {
            errorDiv.text('Jumlah total harus lebih dari 0').show();
            btn.prop('disabled', false);
            btn.html('💾 Simpan Data');
            return;
        }

        $.ajax({
            url: '/project_akhirfix/add_simpanan.php',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                console.log('Add Response:', response);
                if (response.success) {
                    alert('Simpanan berhasil ditambahkan!');
                    location.reload();
                } else {
                    errorDiv.text(response.message).show();
                    btn.prop('disabled', false);
                    btn.html('💾 Simpan Data');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
                let errorMessage = 'Terjadi kesalahan jaringan saat menyimpan data.';
                if (textStatus === 'parsererror') {
                    errorMessage = 'Respons server tidak valid. Mungkin ada output non-JSON. Detail: ' + errorThrown;
                    if (jqXHR.responseText) {
                        errorMessage += '<br>Server respons:<br><pre style="max-height: 150px; overflow-y: scroll; white-space: pre-wrap; word-break: break-all; background-color: #f0f0f0; padding: 10px; border-radius: 5px;">' + htmlEscape(jqXHR.responseText.substring(0, 500)) + '...</pre>';
                    }
                } else if (jqXHR.responseText) {
                    errorMessage += ' Server respons: ' + jqXHR.responseText.substring(0, 200) + '...';
                }
                errorDiv.html(errorMessage).show(); // Use .html() to render <br>
                btn.prop('disabled', false);
                btn.html('💾 Simpan Data');
            }
        });
    });
    
    function htmlEscape(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }
    
    // Edit Modal Functions
    function openEditModal(id) {
        currentFormType = 'edit';
        document.getElementById('editModal').style.display = 'flex';
        document.getElementById('editContent').innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <div class="spinner"></div>
                <div>Memuat form edit...</div>
            </div>
        `;
        
        fetch('get_simpanan_edit.php?id=' + id)
            .then(response => response.text())
            .then(html => { 
                document.getElementById('editContent').innerHTML = html;
                
                // Add event listener for edit form submit
                const form = document.querySelector('#editContent form');
                if (form) {
                    form.onsubmit = function(e) {
                        submitEditSimpanan(e, id);
                    };
                }
            })
            .catch(error => {
                document.getElementById('editContent').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #dc3545;">
                        <div style="font-size: 48px; margin-bottom: 15px;">❌</div>
                        <div>Gagal memuat form edit</div>
                    </div>
                `;
            });
    }
    
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('editContent').innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <div class="spinner"></div>
                <div>Memuat form edit...</div>
            </div>
        `;
    }
    
    function submitEditSimpanan(e, id) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        formData.append('id', id);
        
        const btn = form.querySelector('button[type=submit]');
        const errorDiv = document.getElementById('editError');
        
        if (errorDiv) {
            errorDiv.style.display = 'none';
            errorDiv.innerText = '';
        }
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Menyimpan...';
        
        fetch('aksi_edit_simpanan.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) { 
                location.reload(); 
            } else { 
                if (errorDiv) {
                    errorDiv.innerText = result.error || 'Gagal mengedit data.';
                    errorDiv.style.display = 'block';
                }
                btn.disabled = false;
                btn.innerHTML = '💾 Simpan Perubahan';
            }
        })
        .catch(error => {
            if (errorDiv) {
                errorDiv.innerText = 'Terjadi kesalahan sistem.';
                errorDiv.style.display = 'block';
            }
            btn.disabled = false;
            btn.innerHTML = '💾 Simpan Perubahan';
        });
    }
    
    // Customer Selection Modal Functions
    function openCustomerSelectionModal(formType) {
        currentFormType = formType; // 'tambah' or 'edit'
        document.getElementById('customerSelectionModal').style.display = 'flex';
        document.getElementById('customerSearchInput').value = '';
        document.getElementById('customerResults').innerHTML = `
            <div style="text-align: center; padding: 40px; color: #6c757d;">
                <div style="font-size: 24px; margin-bottom: 10px;">👥</div>
                <div>Ketik untuk mencari customer</div>
            </div>
        `;
        document.getElementById('customerSelectionError').style.display = 'none';
        
        // Focus on search input
        setTimeout(() => {
            document.getElementById('customerSearchInput').focus();
        }, 100);
    }

    function closeCustomerSelectionModal() {
        document.getElementById('customerSelectionModal').style.display = 'none';
        document.getElementById('customerResults').innerHTML = '';
        document.getElementById('customerSelectionError').style.display = 'none';
    }

    function searchCustomers() {
        const query = document.getElementById('customerSearchInput').value;
        const resultsDiv = document.getElementById('customerResults');
        const errorDiv = document.getElementById('customerSelectionError');
        
        if (query.length < 2) {
            resultsDiv.innerHTML = `
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <div style="font-size: 24px; margin-bottom: 10px;">👥</div>
                    <div>Ketik minimal 2 karakter untuk mencari</div>
                </div>
            `;
            return;
        }
        
        resultsDiv.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <div class="spinner"></div>
                <div>Mencari customer...</div>
            </div>
        `;
        errorDiv.style.display = 'none';

        fetch('get_customers.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.customers.length > 0) {
                        let html = '';
                        data.customers.forEach(customer => {
                            html += `
                                <div class="customer-item" onclick="selectCustomer(${customer.id}, '${customer.name.replace(/'/g, '\\\'')}')" 
                                     style="padding: 15px; border-bottom: 1px solid #f1f3f4; cursor: pointer; transition: background-color 0.2s; border-radius: 6px; margin-bottom: 5px;">
                                    <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                                        👤 ${customer.name}
                                    </div>
                                    <div style="font-size: 13px; color: #6c757d;">
                                        🏷️ ${customer.role}
                                    </div>
                                </div>
                            `;
                        });
                        resultsDiv.innerHTML = html;
                        
                        // Add hover effects
                        document.querySelectorAll('.customer-item').forEach(item => {
                            item.addEventListener('mouseenter', function() {
                                this.style.backgroundColor = '#f8f9fa';
                            });
                            item.addEventListener('mouseleave', function() {
                                this.style.backgroundColor = 'transparent';
                            });
                        });
                    } else {
                        resultsDiv.innerHTML = `
                            <div style="text-align: center; padding: 40px; color: #6c757d;">
                                <div style="font-size: 48px; margin-bottom: 15px;">🔍</div>
                                <div style="font-weight: 600; margin-bottom: 5px;">Customer tidak ditemukan</div>
                                <div style="font-size: 14px;">Coba gunakan kata kunci lain</div>
                            </div>
                        `;
                    }
                } else {
                    errorDiv.innerText = 'Error: ' + (data.error || 'Gagal memuat data customer');
                    errorDiv.style.display = 'block';
                    resultsDiv.innerHTML = `
                        <div style="text-align: center; padding: 40px; color: #dc3545;">
                            <div style="font-size: 48px; margin-bottom: 15px;">❌</div>
                            <div>Gagal memuat data customer</div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error fetching customers:', error);
                errorDiv.innerText = 'Terjadi kesalahan jaringan.';
                errorDiv.style.display = 'block';
                resultsDiv.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #dc3545;">
                        <div style="font-size: 48px; margin-bottom: 15px;">🌐</div>
                        <div>Kesalahan jaringan</div>
                    </div>
                `;
            });
    }

    function selectCustomer(id, name) {
        if (currentFormType === 'tambah') {
            document.getElementById('tambah-customer-id').value = id;
            document.getElementById('tambah-customer-name').value = name;
        } else if (currentFormType === 'edit') {
            document.getElementById('edit-customer-id').value = id;
            document.getElementById('edit-customer-name').value = name;
        }
        closeCustomerSelectionModal();
    }

    // Search Simpanan Function
    function searchSimpanan() {
        const keyword = document.getElementById('searchInput').value;
        const tableBody = document.querySelector('#simpananTable tbody');
        
        if (!tableBody) return;
        
        if (keyword.trim() === '') {
            location.reload();
            return;
        }
        
        tableBody.innerHTML = `
            <tr>
                <td colspan="10" style="text-align: center; padding: 40px;">
                    <div class="spinner" style="margin: 0 auto 15px;"></div>
                    <div>Mencari data...</div>
                </td>
            </tr>
        `;
        
        fetch('get_simpanan_search.php?q=' + encodeURIComponent(keyword))
            .then(response => response.text())
            .then(html => {
                document.getElementById('simpananTable').innerHTML = html;
            })
            .catch(error => {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 40px; color: #dc3545;">
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
            searchSimpanan();
        }
    });
    
    // Modal dragging functionality
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
            
            // Prevent dragging outside viewport
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
        const subtotalInput = document.querySelector('input[name="subtotal"]');
        const feeInput = document.querySelector('input[name="fee"]');
        const totalInput = document.querySelector('input[name="total"]');
        
        if (subtotalInput && feeInput && totalInput) {
            function calculateTotal() {
                const subtotal = parseFloat(subtotalInput.value) || 0;
                const fee = parseFloat(feeInput.value) || 0;
                totalInput.value = subtotal + fee;
            }
            
            subtotalInput.addEventListener('input', calculateTotal);
            feeInput.addEventListener('input', calculateTotal);
        }
    }
    
    // Initialize auto-calculation when tambah modal opens
    const originalOpenTambahModal = openTambahModal;
    openTambahModal = function() {
        originalOpenTambahModal();
        setTimeout(setupAutoCalculation, 100);
    };

    // Print Full Table Function
    let originalDisplay = {}; // To store original display styles
    function printFullTableSimpanan() {
        const tableBody = document.querySelector('#simpananTable tbody');
        const rows = tableBody ? tableBody.querySelectorAll('tr') : [];
        const paginationControls = document.querySelector('.table-pagination');
        const searchContainer = document.querySelector('.search-container');
        const actionLeft = document.querySelector('.action-left');

        // Store original display and show all rows
        rows.forEach((row, index) => {
            originalDisplay[index] = row.style.display; // Store original display
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
                row.style.display = originalDisplay[index]; // Restore original display
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
    </script>
</body>
</html>