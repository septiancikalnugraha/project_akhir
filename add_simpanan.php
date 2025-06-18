<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();
session_start();
require 'db.php';

ob_clean();
header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => ''
];

try {
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'anggota' && $_SESSION['user']['role'] != 'petugas')) {
        throw new Exception('Akses ditolak. Anda tidak memiliki izin.');
    }

    $user_id = $_SESSION['user']['id'];
    $customer_id = null;
    if ($_SESSION['user']['role'] == 'anggota') {
        // Cek user dan customer sekaligus
        $sql = "SELECT u.id as user_id, c.id as customer_id FROM users u LEFT JOIN customers c ON u.id = c.user_id AND c.deleted_at IS NULL WHERE u.id = ? AND u.deleted_at IS NULL LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception('User atau anggota tidak ditemukan atau sudah dihapus.');
        }
        $row = $result->fetch_assoc();
        if (!$row['user_id'] || !$row['customer_id']) {
            throw new Exception('User atau anggota tidak ditemukan atau sudah dihapus.');
        }
        $customer_id = $row['customer_id'];
        $stmt->close();
    } else if ($_SESSION['user']['role'] == 'petugas') {
        $customer_id = $_POST['customer_id'] ?? '';
        if (empty($customer_id)) {
            throw new Exception('Customer ID diperlukan untuk petugas.');
        }
        // Cek customer dan user sekaligus
        $sql = "SELECT c.id as customer_id, u.id as user_id FROM customers c LEFT JOIN users u ON c.user_id = u.id AND u.deleted_at IS NULL WHERE c.id = ? AND c.deleted_at IS NULL LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            throw new Exception('User atau anggota tidak ditemukan atau sudah dihapus.');
        }
        $row = $result->fetch_assoc();
        if (!$row['user_id'] || !$row['customer_id']) {
            throw new Exception('User atau anggota tidak ditemukan atau sudah dihapus.');
        }
        $stmt->close();
    }

    $type = $_POST['type'] ?? '';
    $plan = $_POST['plan'] ?? '';
    $subtotal = floatval($_POST['subtotal'] ?? 0);
    $fee = floatval($_POST['fee'] ?? 0);
    $total = $subtotal + $fee;
    $fiscal_date = $_POST['fiscal_date'] ?? '';
    $status = $_POST['status'] ?? 'pending';

    if (empty($type) || $subtotal <= 0 || $total <= 0 || empty($fiscal_date) || empty($status)) {
        throw new Exception('Semua field wajib diisi dan nominal harus lebih dari 0.');
    }

    $allowed_types = ['pokok', 'wajib', 'sukarela'];
    if (!in_array($type, $allowed_types)) {
        throw new Exception('Jenis simpanan tidak valid.');
    }

    $insert_sql = "INSERT INTO deposits (customer_id, type, plan, subtotal, fee, total, fiscal_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt_insert = $conn->prepare($insert_sql);
    if ($stmt_insert === false) {
        throw new Exception('Gagal menyiapkan statement: ' . $conn->error);
    }
    $stmt_insert->bind_param("issddsss", $customer_id, $type, $plan, $subtotal, $fee, $total, $fiscal_date, $status);

    if ($stmt_insert->execute()) {
        $response['success'] = true;
        $response['message'] = 'Simpanan berhasil ditambahkan dan menunggu verifikasi.';
    } else {
        throw new Exception('Gagal menambahkan simpanan: ' . $stmt_insert->error);
    }
    $stmt_insert->close();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    $conn->close();
    echo json_encode($response);
}
?> 