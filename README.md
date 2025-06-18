# SIKOPIN - Sistem Informasi Koperasi

Aplikasi web untuk manajemen simpanan dan pinjaman koperasi berbasis PHP dan MySQL.

## Fitur Utama
- **Manajemen Simpanan**: Tambah, edit, hapus, dan lihat data simpanan anggota.
- **Manajemen Pinjaman**: Tambah, edit, hapus, dan lihat data pinjaman anggota.
- **Dashboard Statistik**: Grafik dan tabel laporan simpanan & pinjaman per bulan.
- **Manajemen Anggota & User**: CRUD anggota dan user, pengaturan role (petugas, anggota, ketua).
- **Login Multi-Role**: Hak akses berbeda untuk petugas dan anggota.
- **Validasi Otomatis**: Total simpanan/pinjaman otomatis dihitung dari subtotal + fee.
- **Notifikasi**: Pemberitahuan sukses/gagal saat menambah data.

## Struktur Folder
- `simpanan.php` : Halaman utama data simpanan (petugas/ketua)
- `pinjaman.php` : Halaman utama data pinjaman (petugas/ketua)
- `anggota.php`  : Manajemen data anggota
- `user.php`     : Manajemen user & role
- `dashboard.php`: Dashboard statistik
- `tambah_simpanan.php`, `add_simpanan.php`, `aksi_tambah_simpanan.php` : Proses tambah simpanan
- `aksi_tambah_pinjaman.php` : Proses tambah pinjaman
- `resources/views/layouts/` : Template blade (jika menggunakan Laravel)
- `db.php`       : Koneksi database
- `project_akhir.sql` : Struktur database

## Cara Instalasi
1. **Clone/download** project ke folder XAMPP/htdocs.
2. Import file `project_akhir.sql` ke database MySQL Anda.
3. Edit `db.php` jika perlu menyesuaikan user/password database.
4. Jalankan XAMPP (Apache & MySQL), lalu akses `http://localhost/nama_folder_project` di browser.

## Petunjuk Penggunaan
### Untuk Petugas/Ketua:
- Login sebagai petugas/ketua.
- Bisa menambah, mengedit, dan menghapus data simpanan & pinjaman untuk anggota.
- Field **total** pada simpanan/pinjaman otomatis dihitung dari subtotal + fee.
- Hanya petugas/ketua yang bisa menambah data simpanan/pinjaman.

### Untuk Anggota:
- Login sebagai anggota.
- Hanya bisa melihat data simpanan & pinjaman milik sendiri.
- Tidak bisa menambah data simpanan/pinjaman.

## Catatan
- Pastikan field tanggal (fiscal_date) selalu diisi dengan benar.
- Jika ada error, cek console browser dan error log PHP.
- Untuk customisasi lebih lanjut, edit file PHP terkait di root project.

---

**Aplikasi ini dikembangkan untuk kebutuhan tugas akhir/latihan sistem koperasi.** 