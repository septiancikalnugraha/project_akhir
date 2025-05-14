<?php
session_start();
require 'db.php';

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Ambil data pinjaman (loans) join customer
$sql = "SELECT l.*, c.name as customer_name FROM loans l
        LEFT JOIN customers c ON l.customer_id = c.id
        WHERE l.deleted_at IS NULL
        ORDER BY l.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pinjaman - SIKOPIN</title>
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
        .badge.loaned { background: #d4edda; color: #388e3c; }
        .btn { padding: 5px 15px; border-radius: 5px; border: 1px solid #bbb; background: #fff; cursor: pointer; font-size: 14px; }
        .btn-view { color: #4a7c59; border-color: #4a7c59; }
        .table-actions { text-align: right; }
        .table-search { border-radius: 5px; border: 1px solid #bbb; padding: 5px 10px; font-size: 14px; }
        .table-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .table-toolbar-right { display: flex; gap: 8px; align-items: center; }
        .table-pagination { margin-top: 10px; display: flex; justify-content: space-between; align-items: center; }
        .per-halaman-select { border-radius: 5px; border: 1px solid #bbb; padding: 3px 8px; font-size: 14px; }
        .sidebar {
            width: 220px;
            background: #fff;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            border-right: 1px solid #eee;
            padding-top: 0;
            z-index: 10;
        }
        .sidebar h2 {
            text-align: left;
            margin: 25px 0 25px 30px;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar ul li {
            padding: 12px 30px;
            display: flex;
            align-items: center;
            font-size: 17px;
            color: #222;
            border-left: 4px solid transparent;
            cursor: pointer;
            transition: background 0.15s;
            gap: 10px;
        }
        .sidebar ul li.active, .sidebar ul li:hover {
            background: #f3f3f3;
            border-left: 4px solid #6b6b3d;
            color: #6b6b3d;
        }
        .sidebar ul li.section {
            color: #aaa;
            font-size: 14px;
            font-weight: normal;
            margin-top: 18px;
            margin-bottom: 0;
            padding: 8px 30px 2px 30px;
            background: none;
            border: none;
            cursor: default;
        }
        .sidebar ul li a {
            color: inherit;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>SIKOPIN</h2>
        <ul>
            <li class="<?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo 'active'; ?>">
                <a href="dashboard.php">
                    <img src="https://img.icons8.com/fluency/24/000000/combo-chart.png" style="margin-right:8px;"/> Dasbor
                </a>
            </li>
            <li class="<?php if(basename($_SERVER['PHP_SELF'])=='simpanan.php') echo 'active'; ?>">
                <a href="simpanan.php">
                    <img src="https://img.icons8.com/color/24/000000/bank-cards.png" style="margin-right:8px;"/> Simpanan
                </a>
            </li>
            <li class="<?php if(basename($_SERVER['PHP_SELF'])=='pinjaman.php') echo 'active'; ?>">
                <a href="pinjaman.php">
                    <img src="https://img.icons8.com/color/24/000000/money-bag.png" style="margin-right:8px;"/> Pinjaman
                </a>
            </li>
            <li class="section">Master Data</li>
            <li>
                <a href="#">
                    <img src="https://img.icons8.com/color/24/000000/conference-call.png" style="margin-right:8px;"/> Anggota
                </a>
            </li>
            <li class="section">Settings</li>
            <li>
                <a href="#">
                    <img src="https://img.icons8.com/ios-filled/24/000000/settings.png" style="margin-right:8px;"/> User
                </a>
            </li>
        </ul>
    </div>
    <div class="topbar">
        <div></div>
        <div class="profile-dot"></div>
    </div>
    <div class="main-content">
        <div class="breadcrumb">Pinjaman &gt; Daftar</div>
        <div class="page-title">Pinjaman</div>
        <div class="card-table">
            <div class="table-toolbar">
                <button class="btn">Buat</button>
                <div class="table-toolbar-right">
                    <input type="text" class="table-search" placeholder="Search">
                    <button class="btn">Unduh</button>
                </div>
            </div>
            <table class="table">
                <tr>
                    <th>No</th>
                    <th>Anggota</th>
                    <th>Status</th>
                    <th>Instalment</th>
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
                            <td><span class='badge loaned'>{$row['status']}</span></td>
                            <td>{$row['instalment']}</td>
                            <td>Rp " . number_format($row['subtotal'],0,',','.') . "</td>
                            <td>Rp " . number_format($row['fee'],0,',','.') . "</td>
                            <td>Rp " . number_format($row['total'],0,',','.') . "</td>
                            <td>" . ($row['fiscal_date'] ? date('d F Y H:i', strtotime($row['fiscal_date'])) : '-') . "</td>
                            <td class='table-actions'><button class='btn btn-view'>View</button></td>
                        </tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='9' style='text-align:center;'>Tidak ada data</td></tr>";
                }
                ?>
            </table>
            <div class="table-pagination">
                <span>Menampilkan 1 dari <?php echo $no-1; ?></span>
                <span>
                    Per halaman
                    <select class="per-halaman-select">
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
