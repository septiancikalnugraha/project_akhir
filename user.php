<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Ambil data user
$sql = "SELECT * FROM users ORDER BY id ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>User - SIKOPIN</title>
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
        .table-search { border-radius: 5px; border: 1px solid #bbb; padding: 5px 10px; font-size: 14px; }
        .table-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .table-toolbar-right { display: flex; gap: 8px; align-items: center; }
        .table-pagination { margin-top: 10px; display: flex; justify-content: space-between; align-items: center; }
        .per-halaman-select { border-radius: 5px; border: 1px solid #bbb; padding: 3px 8px; font-size: 14px; }
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
        .custom-modal-drag { cursor: move; user-select: none; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .btn[disabled] {
            opacity: 0.8;
            cursor: not-allowed;
            display: flex !important;
            align-items: center;
            justify-content: center;
        }
        /* Sidebar fix: remove div in ul */
        .sidebar ul .section-title { display: block; margin: 16px 0 4px 0; padding-left: 20px; color: #888; font-size: 13px; font-weight: bold; }
        .sidebar ul .section-title:not(:first-child) { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>SIKOPIN</h2>
        <ul>
            <li><a href="dashboard.php"><span>&#128200; Dasbor</span></a></li>
            <li><a href="simpanan.php"><span>&#128179; Simpanan</span></a></li>
            <li><a href="pinjaman.php"><span>&#128181; Pinjaman</span></a></li>
            <li class="section-title">Master Data</li>
            <li><a href="anggota.php"><span>&#128101; Anggota</span></a></li>
            <li class="section-title">Settings</li>
            <li class="active"><a href="user.php"><span>&#9881; User</span></a></li>
        </ul>
    </div>
    <div class="topbar">
        <div></div>
        <div class="profile-dot"></div>
    </div>
    <div class="main-content">
        <div class="breadcrumb">User &gt; Daftar</div>
        <div class="page-title">User</div>
        <div class="card-table">
            <div class="table-toolbar">
                <button class="btn">Buat</button>
                <div class="table-toolbar-right">
                    <input type="text" class="table-search" placeholder="Search">
                    <button class="btn">&#128269;</button>
                </div>
            </div>
            <table class="table" id="userTable">
                <tr>
                    <th>Index</th>
                    <th>Role</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th></th>
                </tr>
                <?php
                $no = 1;
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$no}</td>
                            <td><span class='badge'>".ucfirst($row['role'])."</span></td>
                            <td>{$row['name']}</td>
                            <td>{$row['email']}</td>
                            <td class='table-actions'>
                                <button class='btn btn-view' onclick='showUserDetailModal({$row['id']})'>View</button>
                                <button class='btn btn-view' onclick='openUserEditModal({$row['id']})'>Edit</button>
                                <button class='btn btn-view' style='color:#e74c3c;border-color:#e74c3c;' onclick='hapusUser({$row['id']})'>Hapus</button>
                            </td>
                        </tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>Tidak ada data</td></tr>";
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
    <!-- Modal Detail User -->
    <div id="userDetailModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeUserDetailModal()" class="custom-modal-close">&times;</button>
            <div id="userDetailContent">Loading...</div>
        </div>
    </div>
    <!-- Modal Edit User -->
    <div id="userEditModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeUserEditModal()" class="custom-modal-close">&times;</button>
            <div id="userEditContent">Loading...</div>
        </div>
    </div>
    <script>
    function showUserDetailModal(id) {
        document.getElementById('userDetailModal').style.display = 'flex';
        document.getElementById('userDetailContent').innerHTML = 'Loading...';
        fetch('get_user_detail.php?id='+id)
            .then(r=>r.text())
            .then(html=>{
                document.getElementById('userDetailContent').innerHTML = html;
            });
    }
    function closeUserDetailModal() {
        document.getElementById('userDetailModal').style.display = 'none';
    }
    function openUserEditModal(id) {
        document.getElementById('userEditModal').style.display = 'flex';
        document.getElementById('userEditContent').innerHTML = 'Loading...';
        fetch('get_user_edit.php?id='+id)
            .then(r=>r.text())
            .then(html=>{ document.getElementById('userEditContent').innerHTML = html; });
    }
    function closeUserEditModal() {
        document.getElementById('userEditModal').style.display = 'none';
    }
    function submitEditUser(e, id) {
        e.preventDefault();
        var form = e.target;
        var data = new FormData(form);
        data.append('id', id);
        fetch('aksi_edit_user.php', {method:'POST',body:data})
            .then(r=>r.json())
            .then(res=>{
                if(res.success) location.reload();
                else { document.getElementById('editError').innerText = res.error||'Gagal mengedit data.'; }
            });
    }
    function hapusUser(id) {
        var currentUserId = <?php echo json_encode($_SESSION['user']['id']); ?>;
        if(id == currentUserId) {
            alert('Anda tidak dapat menghapus user yang sedang login.');
            return;
        }
        if(!confirm('Yakin ingin menghapus user ini?')) return;
        var btn = document.querySelector(`#userTable button[onclick*='hapusUser(${id})']`);
        if(btn) {
            btn.disabled = true;
            btn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid #fff;border-right-color:transparent;border-radius:50%;animation:spin 1s linear infinite;margin-right:8px;"></span> Menghapus...';
        }
        fetch('hapus_user.php?id='+id)
            .then(r => {
                if (!r.ok) throw new Error('Network response was not ok');
                return r.json();
            })
            .then(res => {
                if(res.success) {
                    alert('User berhasil dihapus.');
                    var row = document.querySelector(`#userTable button[onclick*='hapusUser(${id})']`)?.closest('tr');
                    if(row) row.remove();
                } else {
                    alert(res.error||'Gagal menghapus data.');
                    if(btn) {
                        btn.disabled = false;
                        btn.innerHTML = 'Hapus';
                    }
                }
            })
            .catch(function(err) {
                alert('Terjadi error jaringan: ' + err.message);
                if(btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Hapus';
                }
            });
    }
    </script>
</body>
</html>
