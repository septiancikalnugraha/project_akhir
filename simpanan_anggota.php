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

// Ambil data simpanan (deposits) hanya untuk anggota yang login
// Pertama, cari customer_id berdasarkan user_id
$customer_sql = "SELECT id FROM customers WHERE user_id = $user_id AND deleted_at IS NULL LIMIT 1";
$customer_result = $conn->query($customer_sql);
$customer_id = null;

if ($customer_result && $customer_result->num_rows > 0) {
    $customer_row = $customer_result->fetch_assoc();
    $customer_id = $customer_row['id'];
    
    // Jika customer_id ditemukan, ambil data simpanan
    $sql = "SELECT d.*, c.name as customer_name FROM deposits d
            LEFT JOIN customers c ON d.customer_id = c.id
            WHERE d.deleted_at IS NULL AND d.customer_id = $customer_id
            ORDER BY d.created_at DESC";
            
    $result = $conn->query($sql);
    
} else {
    // Jika customer_id tidak ditemukan, buat hasil kosong
    $sql = ""; // Kosongkan query
    // Buat objek resultset kosong jika query gagal atau customer tidak ditemukan
    $result = new mysqli_result($conn); // Membuat objek mysqli_result kosong
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Simpanan Saya - SIKOPIN</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Gaya dari simpanan.php untuk tampilan tabel, modal, dll */
        .main-content { margin-left: 220px; padding: 30px; }
        .page-title { font-size: 28px; font-weight: bold; margin-bottom: 10px; }
        .breadcrumb { color: #888; font-size: 14px; margin-bottom: 10px; }
        .card-table { background: #fff; border-radius: 8px; border: 1px solid #ddd; padding: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .table th, .table td { border-bottom: 1px solid #eee; padding: 10px 8px; text-align: left; }
        .table th { background: #fafafa; font-size: 15px; }
        .table td { font-size: 15px; }
        .badge { padding: 2px 10px; border-radius: 12px; font-size: 13px; background: #eee; color: #555; }
        .badge.verified { background: #d4edda; color: #388e3c; }
        .btn {
            padding: 5px 15px;
            border-radius: 5px;
            border: none;
            background: #e67e22;
            color: #fff;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-view {
            color: #e67e22;
            background: #fff3e0;
            border: 1px solid #e67e22;
        }
        .btn-view:hover {
            background: #ffe0b2;
        }
        .table-actions { text-align: right; }
        .table-search { float: right; margin-bottom: 10px; }
        .table-search input { padding: 5px 10px; border-radius: 5px; border: 1px solid #bbb; }
        .table-pagination { margin-top: 10px; display: flex; justify-content: space-between; align-items: center; }
        
        /* Updated sidebar styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 220px;
            height: 100%;
            background: #FFB266;
            border-right: 1px solid #e0e0e0;
            padding-top: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .sidebar h2 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: bold;
            color: #333;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar li {
            padding: 12px 20px;
            font-size: 16px;
            color: #333;
            display: flex;
            align-items: center;
            border-radius: 8px 0 0 8px;
            margin-bottom: 2px;
        }
        .sidebar li.active {
            background-color: #fff;
            border-left: 4px solid #e67e22;
            color: #e67e22;
            font-weight: bold;
        }
        .sidebar li a {
            text-decoration: none;
            color: inherit;
            width: 100%;
            display: inline-block;
        }
        .sidebar li:hover {
            background-color: #ffe0b2;
        }
        .sidebar .section-title {
            margin-top: 20px;
            color: #888;
            font-size: 13px;
            padding-left: 20px;
        }
        .custom-modal {
            position: fixed;
            z-index: 9999;
            left: 0; top: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .custom-modal-content {
            background: #fff;
            border-radius: 14px;
            max-width: 420px;
            width: 92vw;
            padding: 32px 28px 24px 28px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            position: relative;
            animation: modalIn 0.18s cubic-bezier(.4,2,.6,1) both;
            cursor: default;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(40px) scale(0.98); }
            to   { opacity: 1; transform: none; }
        }
        .custom-modal-close {
            position: absolute;
            top: 12px; right: 18px;
            background: none;
            border: none;
            font-size: 26px;
            color: #e67e22;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }
        .custom-modal-close:hover {
            color: #d35400;
        }
        .modal-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 18px;
            color: #e67e22;
        }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 6px; color: #333; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 8px 10px; border-radius: 5px; border: 1px solid #bbb; font-size: 15px; }
        .detail-row { margin-bottom: 10px; }
        .detail-label { font-weight: 500; color: #333; display: inline-block; width: 120px; }
        .custom-modal-drag {
            cursor: move;
            user-select: none;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <!-- Sidebar untuk Anggota -->
    <div class="sidebar">
        <h2>SIKOPIN</h2>
        <ul>
            <li class="<?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo 'active'; ?>">
                <a href="dashboard.php">
                    <span>&#128200; Dasboard</span>
                </a>
            </li>
            <li class="active">
                <a href="simpanan_anggota.php">
                    <span>&#128179; Simpanan Saya</span>
                </a>
            </li>
             <li>
                <a href="pinjaman.php">
                    <span>&#128181; Pinjaman Saya</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="topbar">
        <div></div>
        <div class="profile-dot"></div>
    </div>
    
    <div class="main-content">
        <div class="breadcrumb">Simpanan &gt; Daftar</div>
        <div class="page-title">Simpanan Saya</div>
        <div class="card-table">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div>
                    <!-- Tombol buat/cetak mungkin tidak relevan untuk anggota -->
                </div>
                <div>
                     <!-- Search mungkin tidak relevan untuk anggota -->
                </div>
            </div>
            <table class="table" id="simpananTable">
                <tr>
                    <th>No</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Subtotal</th>
                    <th>Fee</th>
                    <th>Total</th>
                    <th>Fiscal date</th>
                    <th></th>
                </tr>
                <?php
                $no = 1;

                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$no}</td>
                            <td>{$row['customer_name']}</td>
                            <td><span class='badge'>{$row['type']}</span></td>
                            <td><span class='badge'>{$row['plan']}</span></td>
                            <td><span class='badge verified'>{$row['status']}</span></td>
                            <td>Rp " . number_format($row['subtotal'],0,',','.') . "</td>
                            <td>Rp " . number_format($row['fee'],0,',','.') . "</td>
                            <td>Rp " . number_format($row['total'],0,',','.') . "</td>
                            <td>" . date('d F Y H:i', strtotime($row['fiscal_date'])) . "</td>
                            <td class='table-actions'>
                                <button class='btn btn-view' onclick='showDetailModal({$row['id']})'>View</button>
                                <!-- Tombol Edit/Hapus hanya untuk petugas/admin, tidak di sini -->
                            </td>
                        </tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='10' style='text-align:center;'>Tidak ada data</td></tr>";
                }
                ?>
            </table>
            <div class="table-pagination">
                <span>Menampilkan 1 dari <?php echo $no-1; ?></span>
                <span>
                    Per halaman
                    <select>
                        <option>10</option>
                        <option>20</option>
                        <option>50</option>
                    </select>
                </span>
            </div>
        </div>
    </div>
    <!-- Modal Detail Simpanan -->
    <div id="detailModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeDetailModal()" class="custom-modal-close">&times;</button>
            <div id="modalContent">Loading...</div>
        </div>
    </div>

    <script>
    
    function showDetailModal(id) {
        document.getElementById('detailModal').style.display = 'flex';
        document.getElementById('modalContent').innerHTML = 'Loading...';
        fetch('get_simpanan_detail.php?id='+id) // Asumsi get_simpanan_detail.php bisa diakses oleh anggota
            .then(r=>r.text())
            .then(html=>{
                document.getElementById('modalContent').innerHTML = html;
            });
    }
    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
    }
    
    // DRAGGABLE MODAL (Jika menggunakan modal yang sama)
    function makeModalDraggable(modalSelector, dragSelector) {
        const modal = document.querySelector(modalSelector);
        const dragArea = modal.querySelector(dragSelector);
        let isDown = false, offsetX = 0, offsetY = 0;
        if (!dragArea) return; // Check if drag area exists
        dragArea.addEventListener('mousedown', function(e) {
            isDown = true;
            const content = modal.querySelector('.custom-modal-content');
            const rect = content.getBoundingClientRect();
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;
            content.style.position = 'fixed'; // Ensure positioned for dragging
            content.style.margin = 0; // Remove margin during dragging
            document.body.style.userSelect = 'none';
        });
        document.addEventListener('mousemove', function(e) {
            if (!isDown) return;
            const content = modal.querySelector('.custom-modal-content');
            content.style.left = (e.clientX - offsetX) + 'px';
            content.style.top = (e.clientY - offsetY) + 'px';
        });
        document.addEventListener('mouseup', function() {
            isDown = false;
            document.body.style.userSelect = '';
        });
    }

    window.addEventListener('DOMContentLoaded', function() {
        makeModalDraggable('#detailModal', '.modal-title');
    });
    
    // Fungsi search dan hapus tidak relevan untuk anggota di sini
    // function searchSimpanan() { ... }
    // function hapusSimpanan(id) { ... }
    
    </script>
</body>
</html> 