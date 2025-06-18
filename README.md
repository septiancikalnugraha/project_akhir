# SIKOPIN - Sistem Informasi Koperasi

Aplikasi web modern untuk manajemen simpanan dan pinjaman koperasi berbasis PHP dan MySQL, dengan UI/UX profesional dan fitur validasi lengkap.

---

## Fitur Utama
- **Manajemen Simpanan & Pinjaman**: Tambah, edit, hapus (hard delete), dan lihat data simpanan/pinjaman anggota. Subtotal, fee, dan total dihitung otomatis.
- **Dashboard Statistik**: Grafik (Chart.js) dan tabel laporan simpanan & pinjaman per bulan (annual report). Data valid berdasarkan field `fiscal_date`.
- **Manajemen Anggota & User**: CRUD anggota dan user, pengaturan role (petugas, anggota, ketua). Penghapusan anggota juga menghapus user terkait (sinkronisasi data).
- **Login Multi-Role**: Hak akses berbeda untuk petugas, ketua, dan anggota. Hanya petugas/ketua yang bisa menambah/edit data simpanan & pinjaman.
- **Validasi Otomatis & Feedback Spesifik**: Field total otomatis dihitung, validasi backend, dan feedback error yang jelas (misal: email sudah terdaftar, peran tidak sesuai, dsb).
- **Notifikasi Interaktif**: Alert sukses/gagal saat menambah/edit/hapus data.
- **UI/UX Modern**: Tampilan responsif, tema oranye/kuning, card login/register profesional, pagination info akurat ("Menampilkan X dari X data").
- **Sinkronisasi Data**: Penghapusan anggota juga menghapus user terkait (dan sebaliknya). Data lama dapat disinkronkan dengan script khusus.
- **Validasi Customer**: Hanya anggota (user role `anggota` & belum dihapus) yang muncul di pilihan customer saat tambah pinjaman/simpanan.
- **Aturan Hapus Anggota**: Anggota hanya bisa dihapus jika tidak memiliki simpanan atau pinjaman aktif.

## Struktur Folder
- `simpanan.php` : Halaman utama data simpanan (petugas/ketua)
- `pinjaman.php` : Halaman utama data pinjaman (petugas/ketua)
- `anggota.php`  : Manajemen data anggota
- `user.php`     : Manajemen user & role
- `dashboard.php`: Dashboard statistik & grafik
- `tambah_simpanan.php`, `add_simpanan.php`, `aksi_tambah_simpanan.php` : Proses tambah simpanan
- `aksi_tambah_pinjaman.php` : Proses tambah pinjaman
- `hapus_anggota.php`, `hapus_user.php`, `hapus_simpanan.php`, `hapus_pinjaman.php` : Hard delete anggota/user/simpanan/pinjaman
- `db.php`       : Koneksi database
- `project_akhir.sql` : Struktur database
- `style.css`    : Custom style utama
- `resources/views/layouts/` : Template blade (jika menggunakan Laravel)

## Struktur Database (Ringkasan)
- **users**: Data user login (role, name, email, password, deleted_at)
- **customers**: Data anggota koperasi (user_id, name, email, phone, address, deleted_at)
- **deposits**: Data simpanan (customer_id, type, plan, subtotal, fee, total, fiscal_date, status, deleted_at)
- **loans**: Data pinjaman (customer_id, instalment, subtotal, fee, total, fiscal_date, status, deleted_at)
- **Relasi**: Setiap anggota (customers) terhubung ke user (users) melalui user_id. Simpanan dan pinjaman terhubung ke anggota melalui customer_id.

## Cara Instalasi
1. **Clone/download** project ke folder XAMPP/htdocs.
2. Import file `project_akhir.sql` ke database MySQL Anda.
3. Edit `db.php` jika perlu menyesuaikan user/password database.
4. Jalankan XAMPP (Apache & MySQL), lalu akses `http://localhost/nama_folder_project` di browser.

## Panduan Penggunaan
### Untuk Petugas/Ketua:
- Login sebagai petugas/ketua.
- Bisa menambah, mengedit, dan menghapus data simpanan & pinjaman untuk anggota.
- Field **total** pada simpanan/pinjaman otomatis dihitung dari subtotal + fee (readonly).
- Dapat mengelola data anggota dan user.
- **Tidak bisa menghapus anggota yang masih punya simpanan/pinjaman.**

### Untuk Anggota:
- Login sebagai anggota.
- Hanya bisa melihat data simpanan & pinjaman milik sendiri.
- Tidak bisa menambah data simpanan/pinjaman.

## Penjelasan Teknis & Best Practice
- **Hard Delete**: Data yang dihapus benar-benar hilang dari database (bukan soft delete lagi).
- **Sinkronisasi Data**: Penghapusan anggota dari anggota.php juga menghapus user terkait di user.php (dan sebaliknya). Untuk data lama, gunakan script sinkronisasi user_id di customers.
- **Auto-Calculate**: Form tambah/edit simpanan & pinjaman otomatis menghitung total dari subtotal + fee (JS & backend).
- **Notifikasi**: Setiap aksi tambah/edit/hapus menampilkan alert sukses/gagal.
- **Validasi Backend**: Backend selalu menghitung ulang total dari subtotal + fee, tidak menerima input total mentah dari user.
- **Annual Report**: Grafik hanya muncul jika data di database valid (fiscal_date tidak kosong/0000-00-00).
- **Role Akses**: Hanya petugas/ketua yang bisa menambah/edit/hapus simpanan & pinjaman. Anggota hanya bisa melihat data milik sendiri.
- **Validasi Customer**: Pada form tambah pinjaman/simpanan, hanya customer yang user-nya role `anggota` dan belum dihapus yang bisa dipilih. Jika user/customer sudah dihapus, tidak akan muncul di daftar.
- **Aturan Hapus Anggota**: Anggota hanya bisa dihapus jika tidak punya simpanan/pinjaman aktif. Jika masih ada, akan muncul pesan error dan penghapusan dibatalkan.
- **Dashboard Customer**: Jumlah customer di dashboard dihitung dari user dengan role `anggota` dan belum dihapus (bukan dari tabel customers saja).
- **Validasi Email & Feedback Error**: Email dicek case-insensitive, tidak bisa dobel, dan feedback error login/register sangat spesifik.
- **UI/UX Modern**: Login & register card, sidebar, tabel, dan modal didesain responsif dan profesional.

## Tips Pengembangan & Customisasi
- **Custom Style**: Edit `style.css` atau bagian `<style>` di setiap file untuk mengubah warna, font, atau layout.
- **Tambah Fitur**: Ikuti pola CRUD yang sudah ada untuk menambah modul baru.
- **Validasi**: Selalu tambahkan validasi backend dan feedback error yang jelas.
- **Responsive**: Gunakan media query di CSS jika ingin tampilan lebih mobile-friendly.

## Screenshots (Opsional)
- ![Login UI](screenshots/login.png)
- ![Dashboard](screenshots/dashboard.png)
- ![Tabel Simpanan](screenshots/simpanan.png)

> Tambahkan screenshot di folder `screenshots/` jika ingin dokumentasi lebih visual.

## Troubleshooting & FAQ
- **Grafik tidak muncul di dashboard?**
  - Pastikan data simpanan/pinjaman sudah ada dan field fiscal_date terisi benar (bukan 0000-00-00).
- **Tidak bisa tambah simpanan/pinjaman?**
  - Pastikan login sebagai petugas/ketua. Anggota tidak bisa menambah data.
- **Data tidak terhapus?**
  - Sekarang penghapusan hard delete, data benar-benar hilang dari database.
- **Data lama tidak sinkron?**
  - Jalankan script sinkronisasi user_id di tabel customers agar data anggota dan user terhubung.
- **Tidak bisa hapus anggota?**
  - Pastikan anggota tidak punya simpanan atau pinjaman aktif.
- **Customer tidak muncul di pilihan tambah pinjaman/simpanan?**
  - Pastikan user terkait belum dihapus dan role-nya `anggota`.
- **Error lain?**
  - Cek console browser, error log PHP, dan pastikan koneksi database benar.

## Kontribusi & Lisensi
- Silakan modifikasi kode untuk kebutuhan tugas akhir, latihan, atau pengembangan lebih lanjut.
- Tidak ada lisensi khusus, gunakan dengan bijak.

## Kontak
- Untuk pertanyaan, silakan hubungi pengembang atau dosen pembimbing Anda.

---

**Aplikasi ini dikembangkan untuk kebutuhan tugas akhir/latihan sistem koperasi.** 