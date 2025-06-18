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
    // Check if user is logged in and is an 'anggota' or 'petugas'
    if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] != 'anggota' && $_SESSION['user']['role'] != 'petugas')) {
        throw new Exception('Akses ditolak. Anda tidak memiliki izin.');
    }

    $user_id = $_SESSION['user']['id'];

    // Get customer_id based on user_id
    // For petugas, they might add for any customer, so we need customer_id from POST
    // For anggota, customer_id is derived from their user_id
    $customer_id = null;
    if ($_SESSION['user']['role'] == 'anggota') {
        $customer_sql = "SELECT id FROM customers WHERE user_id = ? AND deleted_at IS NULL LIMIT 1";
        $stmt_customer = $conn->prepare($customer_sql);
        $stmt_customer->bind_param("i", $user_id);
        $stmt_customer->execute();
        $customer_result = $stmt_customer->get_result();

        if ($customer_result->num_rows == 0) {
            throw new Exception('Customer tidak ditemukan untuk user ini.');
        }
        $customer_row = $customer_result->fetch_assoc();
        $customer_id = $customer_row['id'];
    } else if ($_SESSION['user']['role'] == 'petugas') {
        // Petugas will send customer_id via POST
        $customer_id = $_POST['customer_id'] ?? '';
        if (empty($customer_id)) {
            throw new Exception('Customer ID diperlukan untuk petugas.');
        }
    }

    // Get POST data
    $type = $_POST['type'] ?? '';
    $plan = $_POST['plan'] ?? '';
    $subtotal = floatval($_POST['subtotal'] ?? 0);
    $fee = floatval($_POST['fee'] ?? 0);
    $total = $subtotal + $fee;
    $fiscal_date = $_POST['fiscal_date'] ?? '';
    $status = $_POST['status'] ?? 'pending';

    // Validate inputs
    if (empty($type) || $subtotal <= 0 || $total <= 0 || empty($fiscal_date) || empty($status)) {
        throw new Exception('Semua field wajib diisi dan nominal harus lebih dari 0.');
    }

    $allowed_types = ['pokok', 'wajib', 'sukarela'];
    if (!in_array($type, $allowed_types)) {
        throw new Exception('Jenis simpanan tidak valid.');
    }

    // Insert data into deposits table
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
    if (isset($stmt_customer) && $stmt_customer) { // Close if it was prepared
        $stmt_customer->close();
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    $conn->close();
    echo json_encode($response);
}
?> 