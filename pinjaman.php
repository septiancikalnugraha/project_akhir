<?php
session_start();
require 'db.php';

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Define the $role variable
$role = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : '';

// Jika role adalah anggota, alihkan ke pinjaman_anggota.php
if ($role == 'anggota') {
    header("Location: pinjaman_anggota.php");
    exit;
}

// --- DEBUG: Tampilkan role saat ini ---
// echo "<!-- Current Role: " . $role . " -->"; // Menghapus baris debug
// --- END DEBUG ---

// Ambil data pinjaman (loans) join customer
$sql = "SELECT l.*, c.name as customer_name FROM loans l
        LEFT JOIN customers c ON l.customer_id = c.id
        WHERE l.deleted_at IS NULL";

// Tambahkan filter untuk menghilangkan data kosong/tidak lengkap saat role adalah 'petugas' (jika diperlukan)
if ($role == 'petugas') {
     // Sesuaikan kondisi filter ini jika ada kriteria lain untuk data 'kosong' pinjaman
    $sql .= " AND (l.subtotal > 0 OR l.total > 0 OR (l.subtotal = 0 AND l.total = 0 AND l.fiscal_date IS NOT NULL AND l.fiscal_date != '1970-01-01 01:00:00'))";
}

$sql .= " ORDER BY l.created_at DESC";
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
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>SIKOPIN</h2>
        <ul>
            <li class="<?php if(basename($_SERVER['PHP_SELF'])=='dashboard.php') echo 'active'; ?>">
                <a href="dashboard.php">
                    <span>&#128200; Dasboard</span>
                </a>
            </li>
            <?php if($role == 'petugas' || $role == 'ketua'): ?>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='simpanan.php') echo 'active'; ?>">
                    <a href="simpanan.php">
                        <span>&#128179; Simpanan</span>
                    </a>
                </li>
                <li class="active">
                    <a href="pinjaman.php">
                        <span>&#128181; Pinjaman</span>
                    </a>
                </li>
                <li>
                    <a href="anggota.php">
                        <span>&#128101; Anggota</span>
                    </a>
                </li>
                <?php if($role == 'petugas'): ?>
                <li>
                    <a href="user.php">
                        <span>&#9881; User</span>
                    </a>
                </li>
                <?php endif; ?>
            <?php elseif($role == 'anggota'): ?>
                 <li>
                    <a href="simpanan_anggota.php">
                        <span>&#128179; Simpanan</span>
                    </a>
                </li>
                <li class="<?php if(basename($_SERVER['PHP_SELF'])=='pinjaman_anggota.php') echo 'active'; ?>">
                    <a href="pinjaman_anggota.php">
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
        <div class="breadcrumb">Pinjaman &gt; Daftar</div>
        <div class="page-title">Pinjaman</div>
        <div class="card-table">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div>
                    <?php if($role == 'petugas'): // Tombol Buat hanya untuk petugas ?>
                        <button class="btn" onclick="openTambahModal()">Buat</button>
                    <?php endif; ?>
                    <?php if($role == 'ketua'): // Tombol Cetak hanya untuk ketua ?>
                        <button class="btn">Cetak</button>
                    <?php endif; ?>
                </div>
                <div>
                    <input type="text" class="table-search" id="searchInput" placeholder="Search">
                    <button class="btn" onclick="searchPinjaman()">Cari</button>
                </div>
            </div>
            <table class="table" id="pinjamanTable">
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
                            <td class='table-actions'>
                                <button class='btn btn-view' onclick='showDetailModal({$row['id']})'>View</button>
                                <?php if($role == 'petugas'): // Only show Edit and Delete buttons for petugas ?>
                                    <button class='btn btn-view' onclick='openEditModal({$row['id']})'>Edit</button>
                                    <button class='btn btn-view' style='color:#e74c3c;border-color:#e74c3c;' onclick='hapusPinjaman({$row['id']})'>Hapus</button>
                                <?php endif; ?>
                            </td>
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
    <!-- Modal Detail Pinjaman -->
    <div id="detailModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeDetailModal()" class="custom-modal-close">&times;</button>
            <div id="modalContent">Loading...</div>
        </div>
    </div>
    <!-- Modal Tambah Pinjaman -->
    <div id="tambahModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button onclick="closeTambahModal()" class="custom-modal-close">&times;</button>
            <div id="tambahContent">
                <form id="formTambahPinjaman">
                    <h3 class="modal-title custom-modal-drag">Tambah Pinjaman</h3>
                     <div class="form-group">
                        <label>Customer</label>
                        <!-- Input field for displaying selected customer name -->
                        <input type="text" id="tambah-customer-name" readonly required placeholder="Pilih Customer">
                        <!-- Hidden input for storing selected customer ID -->
                        <input type="hidden" id="tambah-customer-id" name="customer_id" required>
                        <!-- Button to open customer selection modal (or trigger search) -->
                        <button type="button" class="btn btn-view" onclick="openCustomerSelectionModal('tambah')">Pilih Customer</button>
                    </div>
                    <div class="form-group"><label>Instalment</label><input type="text" name="instalment" required></div>
                    <div class="form-group"><label>Subtotal</label><input type="number" name="subtotal" required></div>
                    <div class="form-group"><label>Fee</label><input type="number" name="fee" required></div>
                    <div class="form-group"><label>Total</label><input type="number" name="total" required></div>
                    <div class="form-group"><label>Fiscal Date</label><input type="datetime-local" name="fiscal_date" required></div>
                    <div class="form-group"><label>Status</label><select name="status" required><option value="pending">Pending</option><option value="loaned">Loaned</option><option value="paid">Paid</option></select></div>
                    <div id="tambahError" style="color:#e74c3c;margin-bottom:8px;"></div>
                    <button type="submit" class="btn" style="width:100%;margin-top:10px;">Simpan</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Edit Pinjaman -->
    <div id="editModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button class="custom-modal-close" onclick="closeEditModal()">&times;</button>
            <div id="editContent">Loading...</div>
        </div>
    </div>
     <!-- Modal Customer Selection -->
    <div id="customerSelectionModal" class="custom-modal" style="display:none;">
        <div class="custom-modal-content">
            <button class="custom-modal-close" onclick="closeCustomerSelectionModal()">&times;</button>
            <h3 class="modal-title custom-modal-drag">Pilih Customer</h3>
            <div class="form-group">
                <input type="text" id="customerSearchInput" placeholder="Cari customer..." onkeyup="searchCustomers()">
            </div>
            <div id="customerResults" style="max-height:300px;overflow-y:auto;">
                <!-- Customer list will be loaded here -->
            </div>
            <div id="customerSelectionError" style="color:#e74c3c; margin-top: 10px;"></div>
        </div>
    </div>
    <script>
     let currentFormType = ''; // 'tambah' or 'edit'

    function showDetailModal(id) {
        document.getElementById('detailModal').style.display = 'flex';
        document.getElementById('modalContent').innerHTML = 'Loading...';
        fetch('get_pinjaman_detail.php?id='+id)
            .then(r=>r.text())
            .then(html=>{
                document.getElementById('modalContent').innerHTML = html;
            });
    }
    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
    }
    function openTambahModal() {
         currentFormType = 'tambah';
        document.getElementById('tambahModal').style.display = 'flex';
        setTimeout(function(){
            // document.querySelector('#formTambahPinjaman select[name=customer_id]').focus(); // No longer a select
        }, 200);
    }
    function closeTambahModal() {
        document.getElementById('tambahModal').style.display = 'none';
        document.getElementById('formTambahPinjaman').reset();
        document.getElementById('tambahError').innerText = '';
        document.getElementById('formTambahPinjaman').querySelector('button[type=submit]').disabled = false;
        document.getElementById('formTambahPinjaman').querySelector('button[type=submit]').innerHTML = 'Simpan';
         // Clear customer fields
        document.getElementById('tambah-customer-name').value = '';
        document.getElementById('tambah-customer-id').value = '';
    }
    document.getElementById('formTambahPinjaman')?.addEventListener('submit', function(e) {
        e.preventDefault();
        var form = e.target;
        var btn = form.querySelector('button[type=submit]');
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid #fff;border-right-color:transparent;border-radius:50%;vertical-align:middle;animation:spin 1s linear infinite;margin-right:8px;"></span> Menyimpan...';
        var data = new FormData(form);
        fetch('aksi_tambah_pinjaman.php', {method:'POST',body:data})
            .then(r=>r.json())
            .then(res=>{
                if(res.success) {
                    closeTambahModal();
                    location.reload();
                }
                else {
                    document.getElementById('tambahError').innerText = res.error||'Gagal menambah data.';
                    btn.disabled = false;
                    btn.innerHTML = 'Simpan';
                }
            });
    });
    function openEditModal(id) {
         currentFormType = 'edit';
        document.getElementById('editModal').style.display = 'flex';
        document.getElementById('editContent').innerHTML = 'Loading...';
        fetch('get_pinjaman_edit.php?id='+id)
            .then(r=>r.text())
            .then(html=>{ 
                document.getElementById('editContent').innerHTML = html;
                // Setelah form dimuat, tambahkan event listener untuk submit
                const form = document.querySelector('#editContent form');
                if(form) {
                    form.onsubmit = function(e) {
                        submitEditPinjaman(e, id);
                    };
                }
            });
    }
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
        document.getElementById('editContent').innerHTML = 'Loading...';
    }
    function submitEditPinjaman(e, id) {
        e.preventDefault();
        var form = e.target;
        var data = new FormData(form);
        data.append('id', id);
        
        var btn = form.querySelector('button[type=submit]');
        btn.disabled = true;
        btn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid #fff;border-right-color:transparent;border-radius:50%;vertical-align:middle;animation:spin 1s linear infinite;margin-right:8px;"></span> Menyimpan...';
        
        fetch('aksi_edit_pinjaman.php', {
            method: 'POST',
            body: data
        })
        .then(r=>r.json())
        .then(res=>{
            if(res.success) { 
                location.reload(); 
            } else { 
                document.getElementById('editError').innerText = res.error || 'Gagal mengedit data.';
                btn.disabled = false;
                btn.innerHTML = 'Simpan';
            }
        })
        .catch(error => {
            document.getElementById('editError').innerText = 'Terjadi kesalahan sistem.';
            btn.disabled = false;
            btn.innerHTML = 'Simpan';
        });
    }
     // Customer Selection Modal Functions
    function openCustomerSelectionModal(formType) {
        currentFormType = formType; // 'tambah' or 'edit'
        document.getElementById('customerSelectionModal').style.display = 'flex';
        document.getElementById('customerSearchInput').value = ''; // Clear previous search
        searchCustomers(); // Load all customers initially
    }

    function closeCustomerSelectionModal() {
        document.getElementById('customerSelectionModal').style.display = 'none';
         document.getElementById('customerResults').innerHTML = ''; // Clear results
         document.getElementById('customerSelectionError').innerText = ''; // Clear errors
    }

    function searchCustomers() {
        const query = document.getElementById('customerSearchInput').value;
        const resultsDiv = document.getElementById('customerResults');
         const errorDiv = document.getElementById('customerSelectionError');
        resultsDiv.innerHTML = 'Loading...';
         errorDiv.innerText = '';

        fetch('get_customers.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                resultsDiv.innerHTML = ''; // Clear loading
                if (data.success) {
                    if (data.customers.length > 0) {
                        data.customers.forEach(customer => {
                            const customerDiv = document.createElement('div');
                            customerDiv.style.padding = '10px';
                            customerDiv.style.borderBottom = '1px solid #eee';
                            customerDiv.style.style = 'cursor: pointer;'
                            customerDiv.textContent = customer.name + ' (' + customer.role + ')';
                            customerDiv.onclick = () => selectCustomer(customer.id, customer.name);
                            resultsDiv.appendChild(customerDiv);
                        });
                    } else {
                        resultsDiv.innerHTML = '<div style="padding: 10px;">No customers found.</div>';
                    }
                } else {
                     errorDiv.innerText = 'Error loading customers: ' + (data.error || 'Unknown error');
                     resultsDiv.innerHTML = ''; // Clear loading
                }
            })
            .catch(error => {
                console.error('Error fetching customers:', error);
                errorDiv.innerText = 'Network error fetching customers.';
                 resultsDiv.innerHTML = ''; // Clear loading
            });
    }

    function selectCustomer(id, name) {
        if (currentFormType === 'tambah') {
            document.getElementById('tambah-customer-id').value = id;
            document.getElementById('tambah-customer-name').value = name;
        } else if (currentFormType === 'edit') {
            document.getElementById('edit-customer-id').value = id;
            document.getElementById('edit-customer-name').value = name;
        }
        closeCustomerSelectionModal();
    }

    // DRAGGABLE MODAL
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
        makeModalDraggable('#tambahModal', '.modal-title');
        makeModalDraggable('#editModal', '.modal-title');
        makeModalDraggable('#detailModal', '.modal-title');
        makeModalDraggable('#customerSelectionModal', '.modal-title');
    });
    
    function searchPinjaman() {
        var keyword = document.getElementById('searchInput').value;
        fetch('get_pinjaman_search.php?q='+encodeURIComponent(keyword))
            .then(r=>r.text())
            .then(html=>{
                document.getElementById('pinjamanTable').innerHTML = html;
            });
    }
    document.getElementById('searchInput').addEventListener('keydown', function(e) {
        if(e.key === 'Enter') { searchPinjaman(); }
    });
    function hapusPinjaman(id) {
        fetch('hapus_pinjaman.php?id='+id)
            .then(r=>r.json())
            .then(res=>{
                if(res.success) location.reload();
                else alert(res.error||'Gagal menghapus data.');
            });
    }
    </script>
</body>
</html>
