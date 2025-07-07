-- =====================================================
-- TABEL USERS (Pengguna Sistem)
-- =====================================================
CREATE TABLE users (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    role VARCHAR(50) NOT NULL,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password TEXT NOT NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABEL CUSTOMERS (Anggota Koperasi)
-- =====================================================
CREATE TABLE customers (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(10),
    code VARCHAR(50) UNIQUE NOT NULL,
    balance DOUBLE(15,2) DEFAULT 0.00,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(50),
    phone VARCHAR(15),
    address TEXT,
    birthdate DATE,
    birthplace VARCHAR(30),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- TABEL DEPOSITS (Simpanan)
-- =====================================================
CREATE TABLE deposits (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    customer_id INT(11) NOT NULL,
    customer_balance_before DOUBLE(15,2) DEFAULT 0.00,
    customer_balance_after DOUBLE(15,2) DEFAULT 0.00,
    status VARCHAR(50) DEFAULT 'pending',
    type VARCHAR(50) NOT NULL,
    plan VARCHAR(50),
    subtotal DOUBLE(15,2) DEFAULT 0.00,
    fee DOUBLE(15,2) DEFAULT 0.00,
    total DOUBLE(15,2) DEFAULT 0.00,
    notes TEXT,
    fiscal_date TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL LOANS (Pinjaman)
-- =====================================================
CREATE TABLE loans (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    customer_id INT(11) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    instalment DOUBLE(15,2) DEFAULT 0.00,
    subtotal DOUBLE(15,2) DEFAULT 0.00,
    fee DOUBLE(15,2) DEFAULT 0.00,
    total DOUBLE(15,2) DEFAULT 0.00,
    notes TEXT,
    fiscal_date TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL LOAN_INSTALMENTS (Cicilan Pinjaman)
-- =====================================================
CREATE TABLE loan_instalments (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    customer_id INT(11) NOT NULL,
    loan_id INT(11) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    instalment DOUBLE(15,2) DEFAULT 0.00,
    subtotal DOUBLE(15,2) DEFAULT 0.00,
    fee DOUBLE(15,2) DEFAULT 0.00,
    total DOUBLE(15,2) DEFAULT 0.00,
    notes TEXT,
    fiscal_date TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (loan_id) REFERENCES loans(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL TRANSACTIONS (Transaksi)
-- =====================================================
CREATE TABLE transactions (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    customer_id INT(10) NOT NULL,
    model_type VARCHAR(50),
    model_id INT(11),
    type VARCHAR(50) NOT NULL,
    total DOUBLE(15,2) DEFAULT 0.00,
    fee DOUBLE(15,2) DEFAULT 0.00,
    status VARCHAR(255) DEFAULT 'pending',
    instalment DOUBLE(15,2) DEFAULT 0.00,
    note TEXT,
    fiscal_date TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    finished_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL INVOICES (Invoice)
-- =====================================================
CREATE TABLE invoices (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    transaction_id INT(10),
    customer_id INT(10) NOT NULL,
    total DOUBLE(15,2) DEFAULT 0.00,
    fee DOUBLE(15,2) DEFAULT 0.00,
    status VARCHAR(255) DEFAULT 'pending',
    notes TEXT,
    approved_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- =====================================================
-- TABEL PAYMENT_METHODS (Metode Pembayaran)
-- =====================================================
CREATE TABLE payment_methods (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    expire_in_minutes DOUBLE(15,2) DEFAULT 60.00,
    amount_min DOUBLE(15,2) DEFAULT 0.00,
    amount_max DOUBLE(15,2) DEFAULT 999999999.99,
    fee DOUBLE(15,2) DEFAULT 0.00,
    note TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABEL PAYMENTS (Pembayaran)
-- =====================================================
CREATE TABLE payments (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    invoice_id INT(10),
    customer_id INT(10) NOT NULL,
    total DOUBLE(15,2) DEFAULT 0.00,
    fee DOUBLE(15,2) DEFAULT 0.00,
    status VARCHAR(50) DEFAULT 'pending',
    method_id INT(10),
    method_code VARCHAR(50),
    method_status VARCHAR(50),
    method_request TEXT,
    method_response TEXT,
    method_notifications LONGTEXT,
    note TEXT,
    fiscal_date TIMESTAMP NULL,
    expired_at TIMESTAMP NULL,
    settlement_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (method_id) REFERENCES payment_methods(id) ON DELETE SET NULL
);

-- =====================================================
-- TABEL HISTORIES (Riwayat)
-- =====================================================
CREATE TABLE histories (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    model_type VARCHAR(50),
    model_id INT(10),
    user_id INT(10),
    username VARCHAR(50),
    status VARCHAR(50),
    message TEXT,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- TABEL APPROVALS (Persetujuan)
-- =====================================================
CREATE TABLE approvals (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    approvable_type VARCHAR(50),
    approvable_id INT(10),
    approved_at TIMESTAMP NULL,
    rejected_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- INSERT DATA AWAL
-- =====================================================

-- Insert admin user
INSERT INTO users (role, name, email, password) VALUES 
('admin', 'Administrator', 'admin@sikopin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert role petugas
INSERT INTO users (role, name, email, password) VALUES 
('petugas', 'Petugas Koperasi', 'petugas@sikopin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert role ketua
INSERT INTO users (role, name, email, password) VALUES 
('ketua', 'Ketua Koperasi', 'ketua@sikopin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample payment methods
INSERT INTO payment_methods (name, expire_in_minutes, amount_min, amount_max, fee, note, is_active) VALUES 
('Tunai', 0, 0, 999999999.99, 0, 'Pembayaran tunai', 1),
('Transfer Bank', 60, 10000, 999999999.99, 0, 'Transfer bank', 1),
('E-Wallet', 30, 1000, 1000000, 1000, 'E-wallet payment', 1);

-- =====================================================
-- INDEKS UNTUK PERFORMANCE
-- =====================================================

-- Indeks untuk tabel customers
CREATE INDEX idx_customers_user_id ON customers(user_id);
CREATE INDEX idx_customers_code ON customers(code);
CREATE INDEX idx_customers_email ON customers(email);

-- Indeks untuk tabel deposits
CREATE INDEX idx_deposits_customer_id ON deposits(customer_id);
CREATE INDEX idx_deposits_status ON deposits(status);
CREATE INDEX idx_deposits_fiscal_date ON deposits(fiscal_date);

-- Indeks untuk tabel loans
CREATE INDEX idx_loans_customer_id ON loans(customer_id);
CREATE INDEX idx_loans_status ON loans(status);
CREATE INDEX idx_loans_fiscal_date ON loans(fiscal_date);

-- Indeks untuk tabel transactions
CREATE INDEX idx_transactions_customer_id ON transactions(customer_id);
CREATE INDEX idx_transactions_code ON transactions(code);
CREATE INDEX idx_transactions_status ON transactions(status);

-- Indeks untuk tabel payments
CREATE INDEX idx_payments_customer_id ON payments(customer_id);
CREATE INDEX idx_payments_code ON payments(code);
CREATE INDEX idx_payments_status ON payments(status); 