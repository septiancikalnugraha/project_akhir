-- Tabel Users
CREATE TABLE users (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    role VARCHAR(50),
    name VARCHAR(50),
    email VARCHAR(50),
    email_verified_at TIMESTAMP NULL,
    password TEXT,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Approvals
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

-- Tabel Customers
CREATE TABLE customers (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(10),
    code VARCHAR(50),
    balance INT(11),
    name VARCHAR(50),
    email VARCHAR(50),
    phone INT(11),
    address TEXT(255),
    birthdate DATE,
    birthplace VARCHAR(30),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel Deposits
CREATE TABLE deposits (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    customer_id INT(11),
    customer_balance_before DOUBLE(15,2),
    customer_balance_after DOUBLE(15,2),
    status VARCHAR(50),
    type VARCHAR(50),
    plan VARCHAR(50),
    subtotal DOUBLE(15,2),
    fee DOUBLE(15,2),
    total DOUBLE(15,2),
    notes TEXT(255),
    fiscal_date TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Tabel Histories
CREATE TABLE histories (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    model_type VARCHAR(50),
    model_id INT(10),
    user_id INT(10),
    username VARCHAR(50),
    status VARCHAR(50),
    message TEXT(255),
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel Invoices
CREATE TABLE invoices (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50),
    transaction_id INT(10),
    customer_id INT(10),
    total DOUBLE(15,2),
    fee DOUBLE(15,2),
    status VARCHAR(255),
    notes TEXT(255),
    approved_at TIMESTAMP NULL,
    paid_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Tabel Loans
CREATE TABLE loans (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    customer_id INT(11),
    status VARCHAR(50),
    instalment DOUBLE(15,2),
    subtotal DOUBLE(15,2),
    fee DOUBLE(15,2),
    total DOUBLE(15,2),
    notes TEXT(255),
    fiscal_date TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Tabel Loan_Instalments
CREATE TABLE loan_instalments (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    customer_id INT(11),
    loan_id INT(11),
    status VARCHAR(50),
    instalment DOUBLE(15,2),
    subtotal DOUBLE(15,2),
    fee DOUBLE(15,2),
    total DOUBLE(15,2),
    notes TEXT(255),
    fiscal_date TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (loan_id) REFERENCES loans(id)
);

-- Tabel Payments
CREATE TABLE payments (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50),
    invoice_id INT(10),
    customer_id INT(10),
    total DOUBLE(15,2),
    fee DOUBLE(15,2),
    status VARCHAR(50),
    method_id INT(10),
    method_code VARCHAR(50),
    method_status VARCHAR(50),
    method_request TEXT(50),
    method_response TEXT(50),
    method_notifications LONGTEXT,
    note TEXT(255),
    fiscal_date TIMESTAMP NULL,
    expired_at TIMESTAMP NULL,
    settlement_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (method_id) REFERENCES payment_methods(id)
);

-- Tabel Payment_methods
CREATE TABLE payment_methods (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50),
    expire_in_minutes DOUBLE(15,2),
    amount_min DOUBLE(15,2),
    amount_max DOUBLE(15,2),
    fee DOUBLE(15,2),
    note TEXT(255),
    is_active BOOLEAN,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Transactions
CREATE TABLE transactions (
    id INT(20) PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50),
    customer_id INT(10),
    model_type VARCHAR(50),
    model_id INT(11),
    type VARCHAR(50),
    total DOUBLE(15,2),
    fee DOUBLE(15,2),
    status VARCHAR(255),
    instalment DOUBLE(15,2),
    note TEXT(255),
    fiscal_date TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    finished_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (model_id) REFERENCES histories(id)
);