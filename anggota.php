<?php
session_start();
require 'db.php';

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$role = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : '';

if ($_SESSION['user']['role'] != 'petugas') {
    header('Location: dashboard.php');
    exit;
}

// Ambil data anggota (customers)
$sql = "SELECT c.*, 
        COALESCE(SUM(d.total), 0) as total_simpanan,
        COALESCE(SUM(l.total), 0) as total_pinjaman
        FROM customers c
        LEFT JOIN deposits d ON c.id = d.customer_id AND d.deleted_at IS NULL AND d.status = 'verified'
        LEFT JOIN loans l ON c.id = l.customer_id AND l.deleted_at IS NULL AND l.status = 'loaned'
        WHERE c.deleted_at IS NULL 
        GROUP BY c.id
        ORDER BY c.id ASC";
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
$customer_count = get_count($conn, "customers");
$deposit_count = get_count($conn, "deposits");
$loan_count = get_count($conn, "loans");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Anggota - SIKOPIN</title>
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
        .btn { padding: 5px 15px; border-radius: 5px; border: none; background: #e67e22; color: #fff; cursor: pointer; font-size: 14px; }
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
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>SIKOPIN</h2>
        <ul>
            <li class="<?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo 'active'; ?>">
                <a href="dashboard.php">
                    <span>&#128200; Dasbor</span>
                </a>
            </li>
            <?php if($role == 'petugas'): ?>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='simpanan.php') echo 'active'; ?>">
                    <a href="simpanan.php">
                        <span>&#128179; Simpanan</span>
                    </a>
                </li>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='pinjaman.php') echo 'active'; ?>">
                    <a href="pinjaman.php">
                        <span>&#128181; Pinjaman</span>
                    </a>
                </li>
                <li class="active">
                    <a href="anggota.php">
                        <span>&#128101; Anggota</span>
                    </a>
                </li>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='user.php') echo 'active'; ?>">
                    <a href="user.php">
                        <span>&#9881; User</span>
                    </a>
                </li>
            <?php elseif($role == 'ketua'): ?>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='simpanan.php') echo 'active'; ?>">
                    <a href="simpanan.php">
                        <span>&#128179; Simpanan</span>
                    </a>
                </li>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='pinjaman.php') echo 'active'; ?>">
                    <a href="pinjaman.php">
                        <span>&#128181; Pinjaman</span>
                    </a>
                </li>
                <li class="active">
                    <a href="anggota.php">
                        <span>&#128101; Anggota</span>
                    </a>
                </li>
            <?php elseif($role == 'anggota'): ?>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='simpanan.php') echo 'active'; ?>">
                    <a href="simpanan.php">
                        <span>&#128179; Simpanan Saya</span>
                    </a>
                </li>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='pinjaman.php') echo 'active'; ?>">
                    <a href="pinjaman.php">
                        <span>&#128181; Pinjaman Saya</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="topbar">
        <div></div>
        <div class="profile-dot"></div>
    </div>
    <div class="main-content">
        <div class="breadcrumb">Anggota &gt; Daftar</div>
        <div class="page-title">Anggota</div>
        <div class="card-table">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div>
                    <?php if($role == 'petugas'): ?>
                        <button class="btn" onclick="openTambahModal()">Buat</button>
                    <?php endif; ?>
                </div>
                <div>
                    <input type="text" class="table-search" id="searchInput" placeholder="Search">
                    <button class="btn" onclick="searchAnggota()">Cari</button>
                </div>
            </div>
            <table class="table" id="anggotaTable">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Total Simpanan</th>
                    <th>Total Pinjaman</th>
                    <th></th>
                </tr>
                <?php
                $no = 1;
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$no}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['phone']}</td>
                            <td>Rp {$row['total_simpanan']}</td>
                            <td>Rp {$row['total_pinjaman']}</td>
                            <td class='table-actions'>
                                <button class='btn btn-view' onclick='showDetailModal({$row['id']})'>View</button>";
                        if($role == 'petugas') {
                            echo " <button class='btn btn-view' onclick='openEditModal({$row['id']})'>Edit</button>
                                <button class='btn btn-view' style='color:#e74c3c;border-color:#e74c3c;' onclick='hapusAnggota({$row['id']})'>Hapus</button>";
                        }
                        echo "</td>
                        </tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='7' style='text-align:center;'>Tidak ada data</td></tr>";
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
    <!-- Modal Tambah Anggota -->
    <div id="tambahModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeTambahModal()" class="custom-modal-close">&times;</button>
            <div id="tambahContent">
                <form id="formTambahAnggota">
                    <h3 class="modal-title custom-modal-drag">Tambah Anggota</h3>
                    <div class="form-group"><label>Nama</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                    <div class="form-group"><label>Telepon</label><input type="text" name="phone" required></div>
                    <div class="form-group"><label>Alamat</label><input type="text" name="address" required></div>
                    <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
                    <div id="tambahError" style="color:#e74c3c;margin-bottom:8px;"></div>
                    <button type="submit" class="btn" style="width:100%;margin-top:10px;">Tambahkan</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Edit Anggota -->
    <div id="editModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeEditModal()" class="custom-modal-close">&times;</button>
            <div id="editContent">Loading...</div>
        </div>
    </div>
    <!-- Modal Detail Anggota -->
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
        fetch('get_anggota_detail.php?id='+id)
            .then(r=>r.text())
            .then(html=>{
                document.getElementById('modalContent').innerHTML = html;
            });
    }
    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
    }
    function openTambahModal() {
        document.getElementById('tambahModal').style.display = 'flex';
        setTimeout(function(){
            document.querySelector('#formTambahAnggota input[name=name]').focus();
        }, 200);
    }
    function closeTambahModal() {
        document.getElementById('tambahModal').style.display = 'none';
        document.getElementById('formTambahAnggota').reset();
        document.getElementById('tambahError').innerText = '';
        document.getElementById('formTambahAnggota').querySelector('button[type=submit]').disabled = false;
        document.getElementById('formTambahAnggota').querySelector('button[type=submit]').innerHTML = 'Tambahkan';
    }
    document.getElementById('formTambahAnggota')?.addEventListener('submit', function(e) {
        e.preventDefault();
        var form = e.target;
        var btn = form.querySelector('button[type=submit]');
        btn.disabled = true;
        btn.innerHTML = `
          <span style="display: inline-flex; align-items: center; justify-content: center; width: 100%;">
            <span style=\"display:inline-block;width:18px;height:18px;border:2px solid #fff;border-right-color:transparent;border-radius:50%;animation:spin 1s linear infinite;margin-right:10px;\"></span>
            Menyimpan...
          </span>
        `;
        var data = new FormData(form);
        fetch('aksi_tambah_anggota.php', {method:'POST',body:data})
            .then(r=>r.json())
            .then(res=>{
                if(res.success) {
                    closeTambahModal();
                    location.reload();
                }
                else {
                    document.getElementById('tambahError').innerText = res.error||'Gagal menambah data.';
                    btn.disabled = false;
                    btn.innerHTML = 'Tambahkan';
                }
            });
    });
    function openEditModal(id) {
        document.getElementById('editModal').style.display = 'flex';
        document.getElementById('editContent').innerHTML = 'Loading...';
        fetch('get_anggota_edit.php?id='+id)
            .then(r=>r.text())
            .then(html=>{ document.getElementById('editContent').innerHTML = html; });
    }
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    function submitEditAnggota(e, id) {
        e.preventDefault();
        var form = e.target;
        var data = new FormData(form);
        data.append('id', id);
        fetch('aksi_edit_anggota.php', {method:'POST',body:data})
            .then(r=>r.json())
            .then(res=>{
                if(res.success) location.reload();
                else { document.getElementById('editError').innerText = res.error||'Gagal mengedit data.'; }
            });
    }
    function hapusAnggota(id) {
        fetch('hapus_anggota.php?id='+id)
            .then(r=>r.json())
            .then(res=>{
                if(res.success) location.reload();
                else alert(res.error||'Gagal menghapus data.');
            });
    }
    // DRAGGABLE MODAL
    function makeModalDraggable(modalSelector, dragSelector) {
        const modal = document.querySelector(modalSelector);
        const dragArea = modal.querySelector(dragSelector);
        let isDown = false, offsetX = 0, offsetY = 0;
        dragArea.addEventListener('mousedown', function(e) {
            isDown = true;
            const rect = modal.querySelector('.custom-modal-content').getBoundingClientRect();
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;
            document.body.style.userSelect = 'none';
        });
        document.addEventListener('mousemove', function(e) {
            if (!isDown) return;
            modal.querySelector('.custom-modal-content').style.position = 'fixed';
            modal.querySelector('.custom-modal-content').style.left = (e.clientX - offsetX) + 'px';
            modal.querySelector('.custom-modal-content').style.top = (e.clientY - offsetY) + 'px';
            modal.querySelector('.custom-modal-content').style.margin = 0;
        });
        document.addEventListener('mouseup', function() {
            isDown = false;
            document.body.style.userSelect = '';
        });
    }
    window.addEventListener('DOMContentLoaded', function() {
        makeModalDraggable('#tambahModal', '.modal-title');
        makeModalDraggable('#editModal', '.modal-title');
        makeModalDraggable('#detailModal', '.modal-title');
    });
    function searchAnggota() {
        var keyword = document.getElementById('searchInput').value;
        fetch('get_anggota_search.php?q='+encodeURIComponent(keyword))
            .then(r=>r.text())
            .then(html=>{
                document.getElementById('anggotaTable').innerHTML = html;
            });
    }
    document.getElementById('searchInput').addEventListener('keydown', function(e) {
        if(e.key === 'Enter') { searchAnggota(); }
    });
    </script>
</body>
</html>
