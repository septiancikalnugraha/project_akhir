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

// Query statistik deposit dan loan per bulan (seluruh waktu)
$deposit_stats = [];
$loan_stats = [];

// Query deposit
$sql_deposit = "SELECT DATE_FORMAT(fiscal_date, '%Y-%m') as ym, SUM(total) as total FROM deposits WHERE deleted_at IS NULL AND fiscal_date IS NOT NULL GROUP BY ym ORDER BY ym";
$result_deposit = $conn->query($sql_deposit);
if ($result_deposit) {
    while ($row = $result_deposit->fetch_assoc()) {
        $deposit_stats[$row['ym']] = (float)$row['total'];
    }
}
// Query loan
$sql_loan = "SELECT DATE_FORMAT(fiscal_date, '%Y-%m') as ym, SUM(total) as total FROM loans WHERE deleted_at IS NULL AND fiscal_date IS NOT NULL GROUP BY ym ORDER BY ym";
$result_loan = $conn->query($sql_loan);
if ($result_loan) {
    while ($row = $result_loan->fetch_assoc()) {
        $loan_stats[$row['ym']] = (float)$row['total'];
    }
}
// Gabungkan semua bulan yang ada di deposit maupun loan
$all_months = array_unique(array_merge(array_keys($deposit_stats), array_keys($loan_stats)));
sort($all_months);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard SIKOPIN</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { margin:0; background:#f5f5f5; font-family: 'Segoe UI', Arial, sans-serif; }
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
            z-index: 10;
            transition: left 0.2s;
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
        .topbar {
            height: 56px;
            background: #fff;
            border-bottom: 1px solid #ddd;
            margin-left: 220px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 5;
        }
        .profile-dot { width: 36px; height: 36px; border-radius: 50%; background: #888; }
        .main-content {
            margin-left: 220px;
            padding: 32px 24px 32px 24px;
            min-height: 100vh;
        }
        .dashboard-title {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 32px;
            color: #222;
        }
        .dashboard-cards {
            display: flex;
            gap: 24px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }
        .dashboard-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px #eee;
            padding: 32px 36px 24px 36px;
            min-width: 200px;
            text-align: center;
            border: 1px solid #e0e0e0;
            flex: 1 1 220px;
            transition: box-shadow 0.2s;
        }
        .dashboard-card:hover {
            box-shadow: 0 4px 24px #e0e0e0;
        }
        .dashboard-card .label {
            font-size: 16px;
            color: #888;
            margin-bottom: 8px;
        }
        .dashboard-card .value {
            font-size: 38px;
            font-weight: bold;
            color: #4a7c59;
            margin-bottom: 0;
        }
        .annual-report {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e0e0e0;
            padding: 32px 28px 24px 28px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            margin-bottom: 32px;
        }
        .annual-report-title {
            font-size: 22px;
            margin-bottom: 18px;
            font-weight: bold;
            color: #333;
        }
        .annual-report-legend {
            display: flex;
            gap: 30px;
            margin-top: 18px;
            font-size: 16px;
            align-items: center;
        }
        .legend-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .legend-deposit { background: #388e3c; }
        .legend-loan { background: #e67e22; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            font-size: 15px;
        }
        th, td {
            padding: 10px 8px;
            border: 1px solid #eee;
            text-align: left;
        }
        th {
            background: #fafafa;
            font-size: 15px;
            color: #444;
        }
        tr:nth-child(even) { background: #fcfcfc; }
        @media (max-width: 900px) {
            .main-content { margin-left: 0; padding: 16px 4vw; }
            .sidebar { left: -220px; }
            .topbar { margin-left: 0; padding: 0 12px; }
            .dashboard-cards { flex-direction: column; gap: 16px; }
            .dashboard-card { min-width: 0; padding: 24px 12px; }
            .annual-report { padding: 18px 6px 12px 6px; }
        }
        @media (max-width: 600px) {
            .dashboard-title { font-size: 22px; margin-bottom: 18px; }
            .dashboard-card .value { font-size: 24px; }
            .annual-report-title { font-size: 16px; }
            th, td { font-size: 13px; padding: 7px 4px; }
        }
        /* Scrollable table on small screens */
        .annual-report table { min-width: 420px; }
        .annual-report > div[style*='overflow-x:auto'] { overflow-x: auto; }
    </style>
    <!-- Untuk icon sidebar, bisa pakai fontawesome atau svg, di sini pakai unicode -->
</head>
<body>
    <div class="sidebar">
        <h2>SIKOPIN</h2>
        <ul>
            <li class="active">
                <a href="dashboard.php">
                    <span>&#128200; Dasboard</span>
                </a>
            </li>
            <li>
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
            <li>
                <a href="logout.php" onclick="return confirm('Yakin ingin logout?')">
                    <span style="font-size:18px;color:#e74c3c;margin-right:8px;">🔓</span>Logout
                </a>
            </li>
        </ul>
    </div>
    <div class="topbar">
        <div></div>
        <div class="profile-dot"></div>
    </div>
    <div class="main-content">
        <div class="dashboard-title">Dasboard</div>
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
            <div style="overflow-x:auto; margin-bottom:20px;">
                <table style="width:100%; border-collapse:collapse; margin-bottom:10px;">
                    <thead>
                        <tr style="background:#fafafa;">
                            <th style="padding:8px; border:1px solid #eee;">Bulan</th>
                            <th style="padding:8px; border:1px solid #eee;">Total Deposit</th>
                            <th style="padding:8px; border:1px solid #eee;">Total Loan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($all_months as $ym): ?>
                        <tr>
                            <td style="padding:8px; border:1px solid #eee;">
                                <?php echo date('F Y', strtotime($ym.'-01')); ?>
                            </td>
                            <td style="padding:8px; border:1px solid #eee; color:#4a7c59; font-weight:bold;">
                                Rp <?php echo number_format($deposit_stats[$ym] ?? 0, 0, ',', '.'); ?>
                            </td>
                            <td style="padding:8px; border:1px solid #eee; color:#e67e22; font-weight:bold;">
                                Rp <?php echo number_format($loan_stats[$ym] ?? 0, 0, ',', '.'); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <canvas id="annualChart" height="100"></canvas>
            <div class="annual-report-legend" id="annualLegend" style="cursor:pointer;">
                <span id="legend-deposit" style="user-select:none;"><span class="legend-dot legend-deposit"></span>Deposit</span>
                <span id="legend-loan" style="user-select:none;"><span class="legend-dot legend-loan"></span>Loan</span>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
    const months = <?php echo json_encode(array_map(function($ym){ return date('M Y', strtotime($ym.'-01')); }, $all_months)); ?>;
    const depositData = <?php echo json_encode(array_values(array_map(function($ym) use ($deposit_stats){ return (float)($deposit_stats[$ym] ?? 0); }, $all_months))); ?>;
    const loanData = <?php echo json_encode(array_values(array_map(function($ym) use ($loan_stats){ return (float)($loan_stats[$ym] ?? 0); }, $all_months))); ?>;
    const ctx = document.getElementById('annualChart').getContext('2d');
    const annualChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Deposit',
                    data: depositData,
                    borderColor: '#388e3c',
                    backgroundColor: 'rgba(56,142,60,0.08)',
                    pointBackgroundColor: '#388e3c',
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.1,
                    datalabels: {
                        color: '#388e3c',
                        anchor: 'end',
                        align: 'top',
                        font: { weight: 'bold' },
                        formatter: function(value) { return value > 0 ? 'Rp ' + value.toLocaleString('id-ID') : ''; }
                    },
                    hidden: false
                },
                {
                    label: 'Loan',
                    data: loanData,
                    borderColor: '#e67e22',
                    backgroundColor: 'rgba(230,126,34,0.08)',
                    pointBackgroundColor: '#e67e22',
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.1,
                    datalabels: {
                        color: '#e67e22',
                        anchor: 'end',
                        align: 'top',
                        font: { weight: 'bold' },
                        formatter: function(value) { return value > 0 ? 'Rp ' + value.toLocaleString('id-ID') : ''; }
                    },
                    hidden: false
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Statistik Deposit & Loan per Bulan',
                    font: { size: 18, weight: 'bold' },
                    color: '#333',
                    padding: { bottom: 16 }
                },
                datalabels: {
                    display: true
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            return label;
                        }
                    },
                    backgroundColor: '#fff',
                    titleColor: '#333',
                    bodyColor: '#333',
                    borderColor: '#888',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 6
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Bulan',
                        font: { weight: 'bold' },
                        color: '#333'
                    },
                    grid: {
                        color: '#e0e0e0',
                        lineWidth: 1.5
                    },
                    ticks: { color: '#333' }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Nominal (Rupiah)',
                        font: { weight: 'bold' },
                        color: '#333'
                    },
                    grid: {
                        color: '#e0e0e0',
                        lineWidth: 1.5
                    },
                    ticks: {
                        color: '#333',
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
    // Interaktif legend
    const legendDeposit = document.getElementById('legend-deposit');
    const legendLoan = document.getElementById('legend-loan');
    legendDeposit.addEventListener('click', function() {
        const ds = annualChart.data.datasets[0];
        ds.hidden = !ds.hidden;
        legendDeposit.style.opacity = ds.hidden ? 0.4 : 1;
        annualChart.update();
    });
    legendLoan.addEventListener('click', function() {
        const ds = annualChart.data.datasets[1];
        ds.hidden = !ds.hidden;
        legendLoan.style.opacity = ds.hidden ? 0.4 : 1;
        annualChart.update();
    });
    </script>
</body>
</html>