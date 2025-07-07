-- =====================================================
-- MENAMBAHKAN ROLE PETUGAS DAN KETUA
-- =====================================================

-- Insert role petugas
INSERT INTO users (role, name, email, password) VALUES 
('petugas', 'Petugas Koperasi', 'petugas@sikopin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert role ketua
INSERT INTO users (role, name, email, password) VALUES 
('ketua', 'Ketua Koperasi', 'ketua@sikopin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- =====================================================
-- VERIFIKASI ROLE YANG TELAH DITAMBAHKAN
-- =====================================================
SELECT id, role, name, email, created_at FROM users WHERE role IN ('admin', 'petugas', 'ketua') ORDER BY role; 