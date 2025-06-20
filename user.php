<?php
session_start();
require 'db.php';

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$role = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : '';

if ($role !== 'petugas') {
    header('Location: dashboard.php');
    exit;
}

// Ambil data user
$sql = "SELECT * FROM users WHERE deleted_at IS NULL ORDER BY id ASC";
$result = $conn->query($sql);

// Tambahkan fungsi get_count dan inisialisasi variabel count
function get_count($conn, $table) {
    $sql = "SELECT COUNT(*) as total FROM $table WHERE deleted_at IS NULL";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}
$user_count = get_count($conn, "users");

?>
<!DOCTYPE html>
<html>
<head>
    <title>User - SIKOPIN</title>
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
            content: "⚙️"; /* Icon for User */
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
            white-space: normal;
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
        .form-group select,
        .form-group textarea {
            width: 100%; 
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
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
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='simpanan.php') echo 'active'; ?>">
                    <a href="simpanan.php">
                        <span>💰</span> Simpanan
                    </a>
                </li>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='pinjaman.php') echo 'active'; ?>">
                    <a href="pinjaman.php">
                        <span>💳</span> Pinjaman
                    </a>
                </li>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='anggota.php') echo 'active'; ?>">
                    <a href="anggota.php">
                        <span>👥</span> Anggota
                    </a>
                </li>
                <?php if($role == 'petugas'): // Only petugas can see user management ?>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='user.php') echo 'active'; ?>">
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
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='pinjaman_anggota.php') echo 'active'; ?>">
                    <a href="pinjaman_anggota.php">
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
        <div class="breadcrumb">User › Daftar</div>
        <div class="page-title">User</div>
        
        <div class="card-table">
            <div class="action-bar">
                <div class="action-left">
                    <button class="btn btn-primary" onclick="openTambahUserModal()">
                        ➕ Tambah User
                    </button>
                    <button class="btn btn-secondary" onclick="printFullTableUser()" id="printUserButton">
                        🖨️ Cetak
                    </button>
                </div>
                <div class="action-right">
                    <div class="search-container">
                        <input type="text" class="search-input" id="searchInput" placeholder="Cari user...">
                        <button class="btn btn-outline" onclick="searchUsers()">🔍 Cari</button>
                    </div>
                </div>
            </div>
            
            <div class="table-container">
            <table class="table" id="userTable">
                    <thead>
                <tr>
                            <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                            <th>Role</th>
                            <th>Aksi</th>
                </tr>
                    </thead>
                    <tbody>
                <?php
                $no = 1;
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $roleBadgeClass = '';
                        switch($row['role']) {
                            case 'petugas': $roleBadgeClass = 'badge-info'; break;
                            case 'ketua': $roleBadgeClass = 'badge-warning'; break;
                            case 'anggota': $roleBadgeClass = 'badge-default'; break;
                            default: $roleBadgeClass = 'badge-default'; break;
                        }
                        echo "<tr>
                            <td>{$no}</td>
                            <td><strong>{$row['name']}</strong></td>
                            <td>{$row['email']}</td>
                            <td><span class=\"badge {$roleBadgeClass}\">" . ucfirst($row['role']) . "</span></td>
                            <td class=\"table-actions\">
                                <button class=\"btn btn-outline\" onclick=\"showUserDetailModal({$row['id']})\">👁️ View</button>
                                <button class=\"btn btn-outline\" onclick=\"openEditUserModal({$row['id']})\">✏️ Edit</button>
                                <button class=\"btn btn-danger\" onclick=\"hapusUser({$row['id']})\">🗑️ Hapus</button>
                            </td>
                        </tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>Tidak ada data</td></tr>";
                }
                ?>
                    </tbody>
            </table>
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
    </div>
    <!-- Modal Tambah User -->
    <div id="tambahUserModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeTambahUserModal()" class="custom-modal-close">&times;</button>
            <form id="formTambahUser">
                <h3 class="modal-title">➕ Tambah User Baru</h3>
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="petugas">Petugas</option>
                        <option value="ketua">Ketua</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                
                <div id="tambahUserError" class="error-message" style="display: none;"></div>
                
                <button type="submit" class="btn btn-primary" style="width:100%; padding: 15px; font-size: 16px; margin-top: 20px;">
                    💾 Simpan Data
                </button>
            </form>
        </div>
    </div>
    <!-- Modal Edit User -->
    <div id="editUserModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button class="custom-modal-close" onclick="closeEditUserModal()">&times;</button>
            <div id="editUserContent">
                <h3 class="modal-title">✏️ Edit User</h3>
                <div style="text-align: center; padding: 40px;">
                    <div class="spinner" style="margin: 0 auto 15px;"></div>
                    <div>Memuat form edit...</div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Detail User -->
    <div id="userDetailModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeUserDetailModal()" class="custom-modal-close">&times;</button>
            <div id="userDetailContent">
                <div style="text-align: center; padding: 40px;">
                    <div class="spinner" style="margin: 0 auto 15px;"></div>
                    <div>Memuat data...</div>
                </div>
            </div>
        </div>
    </div>
    <!-- Add jQuery before other scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Function to show detail modal
    function showUserDetailModal(id) {
        $('#userDetailModal').fadeIn();
        $.ajax({
            url: 'get_user_detail.php',
            type: 'GET',
            data: { id: id },
            dataType: 'json', // Explicitly expect JSON
            success: function(response) {
                console.log('User Detail Response:', response); // For debugging
                if (response.success) {
                    const data = response.data;
                    let content = `
                        <h3 class="modal-title">👁️ Detail User</h3>
                        <div class="detail-row"><span class="detail-label">ID:</span> <span class="detail-value">${data.id}</span></div>
                        <div class="detail-row"><span class="detail-label">Nama:</span> <span class="detail-value"><strong>${data.name}</strong></span></div>
                        <div class="detail-row"><span class="detail-label">Email:</span> <span class="detail-value">${data.email}</span></div>
                        <div class="detail-row"><span class="detail-label">Role:</span> <span class="detail-value"><strong>${data.role}</strong></span></div>
                        <div class="detail-row"><span class="detail-label">Tanggal Dibuat:</span> <span class="detail-value">${data.created_at}</span></div>
                    `;
                    $('#userDetailContent').html(content);
                } else {
                    $('#userDetailContent').html(`<div class="error-message">Gagal memuat detail user: ${response.message}</div>`);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
                let errorMessage = 'Terjadi kesalahan jaringan saat memuat detail user.';
                if (textStatus === 'parsererror') {
                    errorMessage = 'Respons server tidak valid. Mungkin ada output non-JSON. Detail: ' + errorThrown;
                } else if (jqXHR.responseText) {
                    errorMessage += ' Server respons: ' + jqXHR.responseText.substring(0, 200) + '...'; // Log first 200 chars
                }
                $('#userDetailContent').html(`<div class="error-message">${errorMessage}</div>`);
            }
        });
    }

    // Function to close detail modal
    function closeUserDetailModal() {
        $('#userDetailModal').fadeOut();
    }

    // Function to open add modal
    function openTambahUserModal() {
        $('#tambahUserModal').fadeIn();
        setTimeout(function(){
            $('#formTambahUser input[name=name]').focus();
        }, 200);
    }

    // Function to close add modal
    function closeTambahUserModal() {
        $('#tambahUserModal').fadeOut();
        $('#formTambahUser')[0].reset();
        $('#tambahUserError').text('').hide();
    }

    // Function to open edit modal
    function openEditUserModal(id) {
        $('#editUserModal').fadeIn();
        $.ajax({
            url: 'get_user_detail.php',
            type: 'GET',
            data: { id: id },
            dataType: 'json', // Explicitly expect JSON
            success: function(response) {
                console.log('User Edit Response:', response); // For debugging
                if (response.success) {
                    const data = response.data;
                    let content = `
                        <form id="formEditUser">
                            <input type="hidden" name="id" value="${data.id}">
                            <div class="form-group">
                                <label>Nama</label>
                                <input type="text" name="name" value="${data.name}" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="${data.email}" required>
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <select name="role" required>
                                    <option value="petugas" ${data.role === 'petugas' ? 'selected' : ''}>Petugas</option>
                                    <option value="ketua" ${data.role === 'ketua' ? 'selected' : ''}>Ketua</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Password Baru (kosongkan jika tidak ingin mengubah)</label>
                                <input type="password" name="password">
                            </div>
                            <div id="editUserError" class="error-message" style="display: none;"></div>
                            <button type="submit" class="btn btn-primary" style="width:100%; padding: 15px; font-size: 16px; margin-top: 20px;">
                                💾 Simpan Perubahan
                            </button>
                        </form>
                    `;
                    $('#editUserContent').html(content);
                } else {
                    $('#editUserContent').html(`<div class="error-message">Gagal memuat form edit user: ${response.message}</div>`);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
                let errorMessage = 'Terjadi kesalahan jaringan saat memuat form edit user.';
                if (textStatus === 'parsererror') {
                    errorMessage = 'Respons server tidak valid. Mungkin ada output non-JSON. Detail: ' + errorThrown;
                } else if (jqXHR.responseText) {
                    errorMessage += ' Server respons: ' + jqXHR.responseText.substring(0, 200) + '...';
                }
                $('#editUserContent').html(`<div class="error-message">${errorMessage}</div>`);
            }
        });
    }

    // Function to close edit modal
    function closeEditUserModal() {
        $('#editUserModal').fadeOut();
    }

    // Function to delete user
    window.hapusUser = function(id) {
        if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
            $.ajax({
                url: 'delete_user.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json', // Explicitly expect JSON
                success: function(response) {
                    console.log('User Delete Response:', response); // For debugging
                    if (response.success) {
                        alert('User berhasil dihapus!');
                    location.reload();
                } else {
                        alert(response.message || 'Gagal menghapus user.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
                    let errorMessage = 'Terjadi kesalahan jaringan saat menghapus user.';
                    if (textStatus === 'parsererror') {
                        errorMessage = 'Respons server tidak valid. Mungkin ada output non-JSON. Detail: ' + errorThrown;
                    }
                    alert(errorMessage);
                }
            });
        }
    };

    // Function to search users
    function searchUsers() {
        const searchText = $('#searchInput').val().toLowerCase();
        $('#userTable tbody tr').each(function() {
            const row = $(this);
            let found = false;
            row.find('td').each(function() {
                if ($(this).text().toLowerCase().indexOf(searchText) > -1) {
                    found = true;
                    return false;
                }
            });
            row.toggle(found);
        });
    }

    // Function to print table
    function printFullTableUser() {
        window.print();
    }

    // Event handlers
    $(document).ready(function() {
        // Handle add form submission
        $('#formTambahUser').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type=submit]');
            submitBtn.prop('disabled', true);
            submitBtn.html('<span class="spinner"></span> Menyimpan...');

            $.ajax({
                url: 'add_user.php',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json', // Explicitly expect JSON
                success: function(response) {
                    console.log('User Add Response:', response); // For debugging
                    if (response.success) {
                        alert('User berhasil ditambahkan!');
                    location.reload();
                } else {
                        $('#tambahUserError').text(response.message).show();
                        submitBtn.prop('disabled', false);
                        submitBtn.html('💾 Simpan Data');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
                    let errorMessage = 'Terjadi kesalahan jaringan saat menyimpan data user.';
                    if (textStatus === 'parsererror') {
                        errorMessage = 'Respons server tidak valid. Mungkin ada output non-JSON. Detail: ' + errorThrown;
                    } else if (jqXHR.responseText) {
                        errorMessage += ' Server respons: ' + jqXHR.responseText.substring(0, 200) + '...';
                    }
                    $('#tambahUserError').text(errorMessage).show();
                    submitBtn.prop('disabled', false);
                    submitBtn.html('💾 Simpan Data');
                }
            });
        });

        // Handle edit form submission
        $(document).on('submit', '#formEditUser', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type=submit]');
            submitBtn.prop('disabled', true);
            submitBtn.html('<span class="spinner"></span> Menyimpan...');

            $.ajax({
                url: 'update_user.php',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json', // Explicitly expect JSON
                success: function(response) {
                    console.log('User Update Response:', response); // For debugging
                    if (response.success) {
                        alert('Data user berhasil diperbarui!');
                        location.reload();
                    } else {
                        $('#editUserError').text(response.message).show();
                        submitBtn.prop('disabled', false);
                        submitBtn.html('💾 Simpan Perubahan');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
                    let errorMessage = 'Terjadi kesalahan jaringan saat menyimpan data user.';
                    if (textStatus === 'parsererror') {
                        errorMessage = 'Respons server tidak valid. Mungkin ada output non-JSON. Detail: ' + errorThrown;
                    }
                    $('#editUserError').text(errorMessage).show();
                    submitBtn.prop('disabled', false);
                    submitBtn.html('💾 Simpan Perubahan');
                }
            });
        });

        // Handle search input
        $('#searchInput').on('keyup', function(e) {
            if (e.key === 'Enter') {
                searchUsers();
            }
        });

        // Close modals when clicking outside
        $(window).on('click', function(e) {
            if ($(e.target).hasClass('custom-modal')) {
                $('.custom-modal').fadeOut();
            }
        });

        // Close modals when pressing Escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('.custom-modal').fadeOut();
            }
        });
    });
    </script>
</body>
</html>