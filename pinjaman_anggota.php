<?php
session_start();
require 'db.php';

// Cek login dan role anggota
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'anggota') {
    // Jika bukan anggota atau belum login, arahkan ke halaman lain atau tampilkan pesan error
    header("Location: dashboard.php"); // Contoh pengalihan
    exit;
}

$role = 'anggota'; // Role sudah pasti anggota di sini
$user_id = $_SESSION['user']['id'];

// Ambil data pinjaman (loans) hanya untuk anggota yang login
// Pertama, cari customer_id berdasarkan user_id
$customer_sql = "SELECT id FROM customers WHERE user_id = $user_id AND deleted_at IS NULL LIMIT 1";
$customer_result = $conn->query($customer_sql);
$customer_id = null;

if ($customer_result && $customer_result->num_rows > 0) {
    $customer_row = $customer_result->fetch_assoc();
    $customer_id = $customer_row['id'];
    
    // Jika customer_id ditemukan, ambil data pinjaman
    $sql = "SELECT l.*, c.name as customer_name FROM loans l
            LEFT JOIN customers c ON l.customer_id = c.id
            WHERE l.deleted_at IS NULL AND l.customer_id = $customer_id
            ORDER BY l.created_at DESC";
            
    $result = $conn->query($sql);
    
} else {
    // Jika customer_id tidak ditemukan, buat hasil kosong
    $result = false;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Pinjaman Saya - SIKOPIN</title>
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
        
        .btn-view {
            background: #e9ecef;
            color: #495057;
        }
        
        .btn-view:hover {
            background: #dee2e6;
        }

        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 1rem;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            padding: 12px 15px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
            color: #212529;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .table-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        /* Badge Styles */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Search Input */
        .search-input {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            width: 200px;
            transition: border-color 0.15s ease-in-out;
        }
        
        .search-input:focus {
            border-color: #FF9933;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(255,153,51,0.25);
        }

        /* Modal Styles */
        .custom-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .custom-modal-content {
            position: relative;
            background-color: #fff;
            margin: 50px auto;
            padding: 20px;
            width: 90%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .custom-modal-close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #6c757d;
            cursor: pointer;
        }
        
        .custom-modal-close:hover {
            color: #343a40;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #495057;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.15s ease-in-out;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            border-color: #FF9933;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(255,153,51,0.25);
        }

        /* Error Message */
        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 0.5rem;
        }

        /* Print Styles */
        @media print {
            .sidebar,
            .topbar,
            .action-bar,
            .btn,
            .custom-modal,
            .table-pagination {
                display: none !important;
            }
            
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .card-table {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }
            
            .page-title::before {
                content: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>SIKOPIN</h2>
        <ul>
            <li><a href="dashboard.php"><span>📊</span> Dashboard</a></li>
            <li><a href="simpanan_anggota.php"><span>💰</span> Simpanan</a></li>
            <li class="active"><a href="pinjaman_anggota.php"><span>💳</span> Pinjaman</a></li>
        </ul>
    </div>

    <!-- Topbar -->
    <div class="topbar">
        <div class="profile-dot" onclick="window.location.href='profile.php'"></div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="breadcrumb">Dashboard / Pinjaman</div>
        <h1 class="page-title">Pinjaman Saya</h1>

        <div class="card-table">
            <div class="action-bar">
                <div class="action-left">
                    <!-- Tombol Tambah Pinjaman (jika diperlukan untuk anggota) -->
                </div>
                <div class="action-right">
                    <input type="text" id="searchInput" class="search-input" placeholder="Cari pinjaman..." onkeyup="searchPinjaman()">
                    <button class="btn btn-secondary" onclick="printFullTablePinjaman()">
                        <span>🖨️</span> Print
                    </button>
                </div>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Jumlah Pinjaman</th>
                        <th>Jumlah Angsuran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1; // Initialize $no here
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $status_class = '';
                            $status_text = '';
                            
                            switch($row['status']) {
                                case 'loaned':
                                    $status_class = 'badge-success';
                                    $status_text = 'Diberikan';
                                    break;
                                case 'pending':
                                    $status_class = 'badge-warning';
                                    $status_text = 'Menunggu';
                                    break;
                                case 'rejected':
                                    $status_class = 'badge-danger';
                                    $status_text = 'Ditolak';
                                    break;
                                default:
                                    $status_class = 'badge-secondary';
                                    $status_text = 'Unknown';
                            }
                            
                            echo "<tr>
                                <td>{$no}</td>
                                <td>".date('d/m/Y', strtotime($row['created_at']))."</td>
                                <td>Rp ".number_format($row['total'] ?? 0, 0, ',', '.')."</td>
                                <td>Rp ".number_format($row['instalment'] ?? 0, 0, ',', '.')."</td>
                                <td><span class='badge {$status_class}'>{$status_text}</span></td>
                                <td class='table-actions'>
                                    <button class='btn btn-view' onclick='showDetailModal({$row['id']})'>View</button>
                                </td>
                            </tr>";
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center;'>Tidak ada data</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
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

    <!-- Modal Detail Pinjaman -->
    <div id="detailModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeDetailModal()" class="custom-modal-close">&times;</button>
            <div id="modalContent">
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
    function showDetailModal(id) {
        $('#detailModal').fadeIn();
        $('#modalContent').html('<div style="text-align: center; padding: 40px;"><div class="spinner" style="margin: 0 auto 15px;"></div><div>Memuat data...</div></div>');
        $.ajax({
            url: 'get_pinjaman_detail.php',
            type: 'GET',
            data: { id: id },
            success: function(response) {
                $('#modalContent').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
                let errorMessage = 'Terjadi kesalahan jaringan saat memuat detail.';
                if (jqXHR.responseText) {
                    errorMessage += ' Server respons: ' + jqXHR.responseText.substring(0, 200) + '...';
                }
                $('#modalContent').html(`<div class="error-message">${errorMessage}</div>`);
            }
        });
    }

    // Function to close detail modal
    function closeDetailModal() {
        $('#detailModal').fadeOut();
    }

    // Function to search pinjaman
    function searchPinjaman() {
        const searchText = $('#searchInput').val().toLowerCase();
        $('#pinjamanTable tbody tr').each(function() {
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
    function printFullTablePinjaman() {
        window.print();
    }

    // Event handlers
    $(document).ready(function() {
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