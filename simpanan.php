<?php
session_start();
require 'db.php';

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Ambil data simpanan (deposits) join customer
$sql = "SELECT d.*, c.name as customer_name FROM deposits d
        LEFT JOIN customers c ON d.customer_id = c.id
        WHERE d.deleted_at IS NULL
        ORDER BY d.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simpanan - SIKOPIN</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
        .btn { padding: 5px 15px; border-radius: 5px; border: 1px solid #bbb; background: #fff; cursor: pointer; font-size: 14px; }
        .btn-view { color: #4a7c59; border-color: #4a7c59; }
        .table-actions { text-align: right; }
        .table-search { float: right; margin-bottom: 10px; }
        .table-search input { padding: 5px 10px; border-radius: 5px; border: 1px solid #bbb; }
        .table-pagination { margin-top: 10px; display: flex; justify-content: space-between; align-items: center; }
        
        /* Updated sidebar styles to match pinjaman.php */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 220px;
            height: 100%;
            background: white;
            border-right: 1px solid #e0e0e0;
            padding-top: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .sidebar h2 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar li {
            padding: 12px 20px;
            font-size: 16px;
            color: #555;
            display: flex;
            align-items: center;
        }
        .sidebar li.active {
            background-color: #f8f9fa;
            border-left: 4px solid #4a7c59;
            color: #4a7c59;
            font-weight: bold;
        }
        .sidebar li a {
            text-decoration: none;
            color: inherit;
            width: 100%;
            display: inline-block;
        }
        .sidebar li:hover {
            background-color: #f5f5f5;
        }
        .sidebar .section-title {
            margin-top: 20px;
            color: #888;
            font-size: 13px;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <!-- Updated sidebar to match pinjaman.php style -->
    <div class="sidebar">
        <h2>SIKOPIN</h2>
        <ul>
            <li>
                <a href="dashboard.php">
                    <span>&#128200; Dasbor</span>
                </a>
            </li>
            <li class="active">
                <a href="simpanan.php">
                    <span>&#128179; Simpanan</span>
                </a>
            </li>
            <li>
                <a href="pinjaman.php">
                    <span>&#128181; Pinjaman</span>
                </a>
            </li>
            
            <div class="section-title">Master Data</div>
            <li>
                <a href="anggota.php">
                    <span>&#128101; Anggota</span>
                </a>
            </li>
            
            <div class="section-title">Settings</div>
            <li>
                <a href="user.php">
                    <span>&#9881; User</span>
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
        <div class="page-title">Simpanan</div>
        <div class="card-table">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div>
                    <button class="btn">Buat</button>
                </div>
                <div>
                    <input type="text" class="table-search" placeholder="Search">
                    <button class="btn">Unduh</button>
                </div>
            </div>
            <table class="table">
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
                            <td class='table-actions'><button class='btn btn-view'>View</button></td>
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
</body>
</html>