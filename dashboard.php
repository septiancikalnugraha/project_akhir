<?php
session_start();
require 'db.php';

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Query jumlah customer, deposit, loan dengan pengecekan error
function get_count($conn, $table) {
    $sql = "SELECT COUNT(*) as total FROM $table WHERE deleted_at IS NULL";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

$customer_count = get_count($conn, "customers");
$deposit_count = get_count($conn, "deposits");
$loan_count = get_count($conn, "loans");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard SIKOPIN</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { margin:0; background:#f5f5f5; }
        .sidebar {
            width: 220px; background: #fff; height: 100vh; position: fixed; left: 0; top: 0; border-right: 1px solid #ddd;
        }
        .sidebar h2 { text-align:center; margin: 20px 0 10px 0; font-size: 22px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { padding: 15px 30px; border-left: 4px solid transparent; cursor:pointer; }
        .sidebar ul li.active, .sidebar ul li:hover { background: #f0f0f0; border-left: 4px solid #4a7c59; }
        .sidebar ul li i { margin-right: 10px; }
        .topbar {
            height: 50px; background: #fff; border-bottom: 1px solid #ddd; margin-left: 220px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px;
        }
        .profile-dot { width: 30px; height: 30px; border-radius: 50%; background: #888; }
        .main-content { margin-left: 220px; padding: 30px; }
        .dashboard-title { font-size: 28px; font-weight: bold; margin-bottom: 25px; }
        .dashboard-cards { display: flex; gap: 20px; margin-bottom: 30px; }
        .dashboard-card {
            background: #fff; border-radius: 8px; box-shadow: 0 1px 4px #eee; padding: 25px 30px; min-width: 180px; text-align: center; border: 1px solid #ddd;
        }
        .dashboard-card .label { font-size: 16px; color: #888; }
        .dashboard-card .value { font-size: 32px; font-weight: bold; color: #4a7c59; }
        .annual-report { background: #fff; border-radius: 8px; border: 1px solid #ddd; padding: 20px; }
        .annual-report-title { font-size: 18px; margin-bottom: 10px; }
        .annual-report-placeholder {
            width: 100%; height: 250px; background: repeating-linear-gradient(0deg, #f5f5f5, #f5f5f5 18px, #eaeaea 18px, #eaeaea 20px);
            border-radius: 6px; margin-bottom: 10px;
        }
        .annual-report-legend { display: flex; gap: 30px; margin-top: 10px; }
        .legend-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 6px; }
        .legend-deposit { background: #4a7c59; }
        .legend-loan { background: #e67e22; }
    </style>
    <!-- Untuk icon sidebar, bisa pakai fontawesome atau svg, di sini pakai unicode -->
</head>
<body>
    <div class="sidebar">
        <h2>SIKOPIN</h2>
        <ul>
            <li class="active">
                <a href="dashboard.php">
                    <img src="https://img.icons8.com/fluency/24/000000/combo-chart.png" style="margin-right:8px;"/> Dasbor
                </a>
            </li>
            <li>
                <a href="simpanan.php">
                    <img src="https://img.icons8.com/color/24/000000/bank-cards.png" style="margin-right:8px;"/> Simpanan
                </a>
            </li>
            <li>
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
        <div class="dashboard-title">Dasbor</div>
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="label">Customer</div>
                <div class="value"><?php echo $customer_count; ?></div>
            </div>
            <div class="dashboard-card">
                <div class="label">Deposit</div>
                <div class="value"><?php echo $deposit_count; ?></div>
            </div>
            <div class="dashboard-card">
                <div class="label">Loan</div>
                <div class="value"><?php echo $loan_count; ?></div>
            </div>
        </div>
        <div class="annual-report">
            <div class="annual-report-title">Annual Report</div>
            <div class="annual-report-placeholder"></div>
            <div class="annual-report-legend">
                <span><span class="legend-dot legend-deposit"></span>Deposit</span>
                <span><span class="legend-dot legend-loan"></span>Loan</span>
            </div>
        </div>
    </div>
</body>
</html>