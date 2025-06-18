<?php
session_start();
require 'db.php';

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Query jumlah customer
function get_count($conn, $table) {
    $sql = "SELECT COUNT(*) as total FROM $table WHERE deleted_at IS NULL";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    return 0;
}

// Query total deposit dan loan
function get_sum($conn, $table) {
    $sql = "SELECT SUM(total) as total FROM $table WHERE deleted_at IS NULL";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'] ?? 0;
    }
    return 0;
}

$sql = "SELECT COUNT(*) as total FROM users WHERE role = 'anggota' AND deleted_at IS NULL";
$result = $conn->query($sql);
$customer_count = 0;
if ($result && $row = $result->fetch_assoc()) {
    $customer_count = $row['total'];
}
$deposit_total = get_sum($conn, "deposits");
$loan_total = get_sum($conn, "loans");

// Query statistik deposit dan loan per bulan (seluruh waktu)
$deposit_stats = [];
$loan_stats = [];

// Query deposit
$sql_deposit = "SELECT DATE_FORMAT(fiscal_date, '%Y-%m') as ym, SUM(total) as total FROM deposits WHERE deleted_at IS NULL AND fiscal_date IS NOT NULL AND fiscal_date != '0000-00-00' AND YEAR(fiscal_date) > 0 GROUP BY ym ORDER BY ym";
$result_deposit = $conn->query($sql_deposit);
if ($result_deposit) {
    while ($row = $result_deposit->fetch_assoc()) {
        $deposit_stats[$row['ym']] = (float)$row['total'];
    }
}
// Query loan
$sql_loan = "SELECT DATE_FORMAT(fiscal_date, '%Y-%m') as ym, SUM(total) as total FROM loans WHERE deleted_at IS NULL AND fiscal_date IS NOT NULL AND fiscal_date != '0000-00-00' AND YEAR(fiscal_date) > 0 GROUP BY ym ORDER BY ym";
$result_loan = $conn->query($sql_loan);
if ($result_loan) {
    while ($row = $result_loan->fetch_assoc()) {
        $loan_stats[$row['ym']] = (float)$row['total'];
    }
}
// Gabungkan semua bulan yang ada di deposit maupun loan
$all_months = array_unique(array_merge(array_keys($deposit_stats), array_keys($loan_stats)));
sort($all_months);

// Debugging: Periksa all_months
// echo '<pre>'; print_r($all_months); echo '</pre>';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard SIKOPIN</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body { 
            background: #f8f9fa; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: #FFB266;
            border-right: 1px solid #e0e0e0;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }
        
        .sidebar.mobile-hidden {
            transform: translateX(-100%);
        }
        
        .sidebar h2 {
            text-align: center;
            font-size: 28px;
            margin-bottom: 40px;
            font-weight: bold;
            color: #333;
            padding: 0 20px;
        }
        
        .sidebar ul {
            list-style: none;
        }
        
        .sidebar li {
            margin: 2px 0;
        }
        
        .sidebar li a {
            display: block;
            padding: 15px 25px;
            font-size: 16px;
            color: #333;
            text-decoration: none;
            border-radius: 0 25px 25px 0;
            margin-right: 20px;
            transition: all 0.3s ease;
        }
        
        .sidebar li.active a {
            background-color: #fff;
            color: #e67e22;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .sidebar li:hover a {
            background-color: rgba(255,255,255,0.2);
        }
        
        .sidebar .section-title {
            margin: 30px 0 10px 0;
            color: #666;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 25px;
        }
        
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1001;
            background: #FFB266;
            border: none;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 20px;
        }
        
        .topbar {
            height: 70px;
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            margin-left: 260px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        
        .profile-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .profile-dot { 
            width: 40px; 
            height: 40px; 
            border-radius: 50%; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 30px;
            min-height: calc(100vh - 70px);
        }
        
        .dashboard-header {
            margin-bottom: 40px;
        }
        
        .dashboard-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        .dashboard-subtitle {
            color: #6c757d;
            font-size: 16px;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .dashboard-card {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 30px;
            text-align: center;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4a7c59, #e67e22, #3498db);
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        
        .dashboard-card .icon {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .dashboard-card .label {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 10px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .dashboard-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        
        .dashboard-card.customer .value { color: #3498db; }
        .dashboard-card.deposit .value { color: #4a7c59; }
        .dashboard-card.loan .value { color: #e67e22; }
        
        .annual-report {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #e9ecef;
            padding: 35px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .annual-report-title {
            font-size: 24px;
            margin-bottom: 25px;
            font-weight: 700;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .annual-report-title::before {
            content: '📊';
            font-size: 28px;
        }
        
        .table-container {
            overflow-x: auto;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            font-size: 14px;
            min-width: 600px;
        }
        
        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-size: 14px;
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .deposit-value {
            color: #4a7c59;
            font-weight: 600;
        }
        
        .loan-value {
            color: #e67e22;
            font-weight: 600;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
            margin: 20px 0;
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        
        #annualChart {
            height: 100% !important;
        }
        
        .annual-report-legend {
            display: flex;
            gap: 30px;
            margin-top: 20px;
            font-size: 16px;
            align-items: center;
            justify-content: center;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
            user-select: none;
        }
        
        .legend-item:hover {
            background-color: #f8f9fa;
        }
        
        .legend-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
        }
        
        .legend-deposit { background: #4a7c59; }
        .legend-loan { background: #e67e22; }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }
            .main-content {
                margin-left: 240px;
                padding: 25px;
            }
            .topbar {
                margin-left: 240px;
            }
        }
        
        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px 15px;
                padding-top: 70px;
            }
            
            .topbar {
                margin-left: 0;
                padding: 0 60px 0 20px;
            }
            
            .dashboard-title {
                font-size: 28px;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .dashboard-card {
                padding: 25px 20px;
            }
            
            .dashboard-card .value {
                font-size: 28px;
            }
            
            .annual-report {
                padding: 25px 20px;
            }
            
            .annual-report-title {
                font-size: 20px;
            }
            
            .annual-report-legend {
                flex-direction: column;
                gap: 15px;
            }
            
            .chart-container {
                height: 350px;
                padding: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px 10px;
                padding-top: 65px;
            }
            
            .dashboard-title {
                font-size: 24px;
            }
            
            .dashboard-card .value {
                font-size: 24px;
            }
            
            .annual-report {
                padding: 20px 15px;
            }
            
            th, td {
                padding: 12px 15px;
                font-size: 13px;
            }
            
            .chart-container {
                height: 300px;
                padding: 10px;
            }
        }
    </style>
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
                <div class="value">Rp <?php echo number_format($deposit_total, 0, ',', '.'); ?></div>
            </div>
            <div class="dashboard-card">
                <div class="label">Loan</div>
                <div class="value">Rp <?php echo number_format($loan_total, 0, ',', '.'); ?></div>
            </div>
        </div>
        <div class="annual-report">
            <div class="annual-report-title">Annual Report</div>
            
            <div style="overflow-x:auto; margin-bottom:30px; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.04);">
                <table style="width:100%; border-collapse:collapse; background:#fff; font-size:14px; min-width:600px;">
                    <thead>
                        <tr style="background:linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <th style="padding:15px 20px; text-align:left; border-bottom:1px solid #e9ecef; font-size:14px; color:#495057; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Bulan</th>
                            <th style="padding:15px 20px; text-align:left; border-bottom:1px solid #e9ecef; font-size:14px; color:#495057; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Total Deposit</th>
                            <th style="padding:15px 20px; text-align:left; border-bottom:1px solid #e9ecef; font-size:14px; color:#495057; font-weight:600; text-transform:uppercase; letter-spacing:0.5px;">Total Loan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($all_months as $ym): ?>
                        <tr style="transition:background-color 0.2s;">
                            <td style="padding:15px 20px; text-align:left; border-bottom:1px solid #e9ecef;"><?php echo date('F Y', strtotime($ym.'-01')); ?></td>
                            <td style="padding:15px 20px; text-align:left; border-bottom:1px solid #e9ecef; color:#4a7c59; font-weight:600;">Rp <?php echo number_format($deposit_stats[$ym] ?? 0, 0, ',', '.'); ?></td>
                            <td style="padding:15px 20px; text-align:left; border-bottom:1px solid #e9ecef; color:#e67e22; font-weight:600;">Rp <?php echo number_format($loan_stats[$ym] ?? 0, 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="position:relative; height:450px; margin:20px 0; background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.04);">
                <canvas id="annualChart"></canvas>
            </div>
            
            <div style="display:flex; gap:30px; margin-top:20px; font-size:16px; align-items:center; justify-content:center;">
                <div style="display:flex; align-items:center; gap:8px; cursor:pointer; padding:8px 16px; border-radius:20px; transition:all 0.3s ease; user-select:none;" id="legend-deposit">
                    <span style="width:16px; height:16px; border-radius:50%; background:#4a7c59;"></span>
                    <span>Deposit</span>
                </div>
                <div style="display:flex; align-items:center; gap:8px; cursor:pointer; padding:8px 16px; border-radius:20px; transition:all 0.3s ease; user-select:none;" id="legend-loan">
                    <span style="width:16px; height:16px; border-radius:50%; background:#e67e22;"></span>
                    <span>Loan</span>
                </div>
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
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Deposit',
                    data: depositData,
                    backgroundColor: 'rgba(56,142,60,0.8)',
                    borderColor: '#388e3c',
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8,
                    datalabels: {
                        color: '#388e3c',
                        anchor: 'end',
                        align: 'top',
                        font: { weight: 'bold', size: 11 },
                        formatter: function(value) { return value > 0 ? 'Rp ' + value.toLocaleString('id-ID') : ''; }
                    },
                    hidden: false
                },
                {
                    label: 'Loan',
                    data: loanData,
                    backgroundColor: 'rgba(230,126,34,0.8)',
                    borderColor: '#e67e22',
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.6,
                    categoryPercentage: 0.8,
                    datalabels: {
                        color: '#e67e22',
                        anchor: 'end',
                        align: 'top',
                        font: { weight: 'bold', size: 11 },
                        formatter: function(value) { return value > 0 ? 'Rp ' + value.toLocaleString('id-ID') : ''; }
                    },
                    hidden: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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
                    stacked: false,
                    title: {
                        display: true,
                        text: 'Bulan',
                        font: { weight: 'bold' },
                        color: '#333'
                    },
                    grid: {
                        display: false
                    },
                    ticks: { 
                        color: '#333',
                        maxRotation: 45,
                        minRotation: 45
                    }
                },
                y: {
                    stacked: false,
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Nominal (Rupiah)',
                        font: { weight: 'bold' },
                        color: '#333'
                    },
                    grid: {
                        color: '#e0e0e0',
                        lineWidth: 1
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