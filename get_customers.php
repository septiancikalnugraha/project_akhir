<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in and has necessary role (e.g., petugas, admin, or ketua)
    if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['petugas', 'admin', 'ketua'])) {
        throw new Exception('Unauthorized access');
    }

    $search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

    // Build the SQL query
    $sql = "SELECT c.id, c.name, u.role FROM customers c LEFT JOIN users u ON c.user_id = u.id WHERE c.deleted_at IS NULL AND u.deleted_at IS NULL";
    
    $params = [];
    $types = '';

    if (!empty($search_query)) {
        $sql .= " AND (c.name LIKE ? OR u.role LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $types .= 'ss';
    }

    $sql .= " ORDER BY c.name ASC";

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    
    if ($search_query && !$stmt) {
         throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']),
            'role' => htmlspecialchars($row['role'])
        ];
    }

    echo json_encode([
        'success' => true,
        'customers' => $customers
    ]);

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 