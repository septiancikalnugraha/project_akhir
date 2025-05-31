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
        .form-group input, .form-group select { 
            width: 100%; 
            padding: 8px 10px; 
            border-radius: 5px; 
            border: 1px solid #bbb; 
            font-size: 15px; 
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #e67e22;
            outline: none;
            box-shadow: 0 0 0 2px rgba(230,126,34,0.1);
        }
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
        }
        .success-message {
            color: #27ae60;
            font-size: 14px;
            margin-top: 5px;
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
            <li><a href="dashboard.php"><span>&#128200; Dasboard</span></a></li>
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
                <button class="btn" onclick="openCreateUserModal()">Buat</button>
                <div class="table-toolbar-right">
                    <input type="text" class="table-search" placeholder="Search" onkeyup="searchUsers()">
                    <button class="btn" onclick="searchUsers()">&#128269;</button>
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
                    <select class="per-halaman-select" onchange="changePerPage(this.value)">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
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
    <!-- Modal Buat User -->
    <div id="userCreateModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeCreateUserModal()" class="custom-modal-close">&times;</button>
            <div class="modal-title">Buat User Baru</div>
            <form id="createUserForm" onsubmit="return submitCreateUser(event)">
                <div class="form-group">
                    <label for="create-name">Nama:</label>
                    <input type="text" id="create-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="create-email">Email:</label>
                    <input type="email" id="create-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="create-password">Password:</label>
                    <input type="password" id="create-password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="create-role">Role:</label>
                    <select id="create-role" name="role" required>
                        <option value="petugas">Petugas</option>
                        <option value="admin">Admin</option>
                        <option value="anggota">Anggota</option>
                    </select>
                </div>
                <button type="submit" class="btn">Simpan User Baru</button>
                <div id="createError" class="error-message"></div>
            </form>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Make functions global
        window.showUserDetailModal = function(id) {
            document.getElementById('userDetailModal').style.display = 'flex';
            document.getElementById('userDetailContent').innerHTML = 'Loading...';
            fetch('get_user_detail.php?id='+id)
                .then(r=>r.text())
                .then(html=>{
                    document.getElementById('userDetailContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('userDetailContent').innerHTML = 'Error loading data: ' + error.message;
                });
        }

        window.closeUserDetailModal = function() {
            document.getElementById('userDetailModal').style.display = 'none';
        }

        window.openUserEditModal = function(id) {
            document.getElementById('userEditModal').style.display = 'flex';
            document.getElementById('userEditContent').innerHTML = 'Loading...';
            fetch('get_user_edit.php?id='+id)
                .then(r=>r.text())
                .then(html=>{ 
                    document.getElementById('userEditContent').innerHTML = html; 
                })
                .catch(error => {
                    document.getElementById('userEditContent').innerHTML = 'Error loading data: ' + error.message;
                });
        }

        window.closeUserEditModal = function() {
            document.getElementById('userEditModal').style.display = 'none';
        }

        window.openCreateUserModal = function() {
            document.getElementById('userCreateModal').style.display = 'flex';
            document.getElementById('createError').innerText = '';
            document.getElementById('createUserForm').reset();
        }

        window.closeCreateUserModal = function() {
            document.getElementById('userCreateModal').style.display = 'none';
        }

        window.submitCreateUser = function(e) {
            e.preventDefault();
            var form = e.target;
            var data = new FormData(form);
            
            // Disable submit button
            var submitBtn = form.querySelector('button[type="submit"]');
            var originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = 'Menyimpan...';
            submitBtn.disabled = true;
            
            fetch('aksi_create_user.php', {
                method: 'POST',
                body: data
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    alert('User berhasil dibuat!');
                    location.reload();
                } else {
                    document.getElementById('createError').innerText = res.error || 'Gagal membuat user';
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(err => {
                document.getElementById('createError').innerText = 'Network error: ' + err.message;
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
            
            return false;
        }

        window.submitEditUser = function(e, id) {
            e.preventDefault();
            var form = e.target;
            var data = new FormData(form);
            data.append('id', id);
            
            // Disable submit button
            var submitBtn = form.querySelector('button[type="submit"]');
            var originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = 'Menyimpan...';
            submitBtn.disabled = true;
            
            fetch('aksi_edit_user.php', {
                method: 'POST',
                body: data
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    alert('User berhasil diupdate!');
                    location.reload();
                } else {
                    document.getElementById('editError').innerText = res.error || 'Gagal mengedit data';
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(err => {
                document.getElementById('editError').innerText = 'Network error: ' + err.message;
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
            
            return false;
        }

        window.hapusUser = function(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) {
                return;
            }

            var currentUserId = <?php echo json_encode($_SESSION['user']['id']); ?>;
            if(id == currentUserId) {
                alert('Anda tidak dapat menghapus user yang sedang login.');
                return;
            }

            // Show loading state
            var buttons = document.querySelectorAll('button[onclick*="hapusUser(' + id + ')"]');
            var btn = buttons[0];
            var originalText = btn.innerHTML;
            btn.innerHTML = 'Menghapus...';
            btn.disabled = true;
            btn.style.opacity = '0.6';
            btn.style.cursor = 'not-allowed';

            fetch('hapus_user.php?id=' + id, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    alert(data.message || 'User berhasil dihapus!');
                    location.reload();
                } else {
                    alert('Gagal menghapus user: ' + (data.error || 'Error tidak diketahui.'));
                    resetButton();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi error: ' + error.message);
                resetButton();
            });

            function resetButton() {
                btn.innerHTML = originalText;
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
            }
        }

        window.searchUsers = function() {
            var input = document.querySelector(".table-search");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("userTable");
            var tr = table.getElementsByTagName("tr");

            for (var i = 1; i < tr.length; i++) {
                var td = tr[i].getElementsByTagName("td");
                var found = false;
                
                if (td.length > 3) {
                    var nameCol = td[2];
                    var emailCol = td[3];

                    if (nameCol) {
                        var txtValue = nameCol.textContent || nameCol.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                        }
                    }

                    if (!found && emailCol) {
                        var txtValue = emailCol.textContent || emailCol.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                        }
                    }
                }

                tr[i].style.display = found ? "" : "none";
            }
        }

        window.changePerPage = function(value) {
            // Implement pagination logic here
            console.log('Changing items per page to:', value);
        }
    });
    </script>
</body>
</html>
