<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user'])) {
        throw new Exception('Unauthorized access');
    }

    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        throw new Exception('No user ID provided');
    }

    $id = intval($_GET['id']);
    
    // Validate ID
    if ($id <= 0) {
        throw new Exception('Invalid user ID');
    }

    // Prevent deleting own account
    if ($id == $_SESSION['user']['id']) {
        throw new Exception('Cannot delete your own account');
    }

    // Check if user exists and get their details
    $check_sql = "SELECT id, name, email, role FROM users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    
    if (!$check_stmt) {
        throw new Exception('Failed to prepare check statement: ' . $conn->error);
    }
    
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        throw new Exception('User not found');
    }

    // Check if current user has permission to delete
    $current_user_role = $_SESSION['user']['role'];
    $target_user_role = $user['role'];

    // Only admin can delete other admins
    if ($target_user_role === 'admin' && $current_user_role !== 'admin') {
        throw new Exception('Only admin can delete other admin users');
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // --- Delete related data first (following dependencies) ---

        // Delete from loan_instalments (depends on loans and customers)
        $delete_loan_instalments_sql = "DELETE FROM loan_instalments WHERE loan_id IN (SELECT id FROM loans WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?))";
        $delete_loan_instalments_stmt = $conn->prepare($delete_loan_instalments_sql);
        if ($delete_loan_instalments_stmt) {
            $delete_loan_instalments_stmt->bind_param("i", $id);
            $delete_loan_instalments_stmt->execute();
            $delete_loan_instalments_stmt->close();
        }

        // Delete from transactions (depends on customers and histories)
        $delete_transactions_sql = "DELETE FROM transactions WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?)";
        $delete_transactions_stmt = $conn->prepare($delete_transactions_sql);
        if ($delete_transactions_stmt) {
            $delete_transactions_stmt->bind_param("i", $id);
            $delete_transactions_stmt->execute();
            $delete_transactions_stmt->close();
        }

        // Delete from loans (depends on customers)
        $delete_loans_sql = "DELETE FROM loans WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?)";
        $delete_loans_stmt = $conn->prepare($delete_loans_sql);
        if ($delete_loans_stmt) {
            $delete_loans_stmt->bind_param("i", $id);
            $delete_loans_stmt->execute();
            $delete_loans_stmt->close();
        }

        // Delete from deposits (depends on customers)
        $delete_deposits_sql = "DELETE FROM deposits WHERE customer_id IN (SELECT id FROM customers WHERE user_id = ?)";
        $delete_deposits_stmt = $conn->prepare($delete_deposits_sql);
        if ($delete_deposits_stmt) {
            $delete_deposits_stmt->bind_param("i", $id);
            $delete_deposits_stmt->execute();
            $delete_deposits_stmt->close();
        }

        // Delete from customers (depends on users)
        $delete_customers_sql = "DELETE FROM customers WHERE user_id = ?";
        $delete_customers_stmt = $conn->prepare($delete_customers_sql);
        if ($delete_customers_stmt) {
             $delete_customers_stmt->bind_param("i", $id);
             $delete_customers_stmt->execute();
             $delete_customers_stmt->close();
         }

        // Delete from histories (depends on users)
        $delete_histories_sql = "DELETE FROM histories WHERE user_id = ?";
        $delete_histories_stmt = $conn->prepare($delete_histories_sql);
        if ($delete_histories_stmt) {
            $delete_histories_stmt->bind_param("i", $id);
            $delete_histories_stmt->execute();
            $delete_histories_stmt->close();
        }

        // --- End of related data deletion ---

        // Finally delete the user
        $delete_sql = "DELETE FROM users WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        
        if (!$delete_stmt) {
            throw new Exception('Failed to prepare delete statement: ' . $conn->error);
        }
        
        $delete_stmt->bind_param("i", $id);

        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                // Commit transaction
                $conn->commit();
                echo json_encode([
                    'success' => true, 
                    'message' => 'User "' . $user['name'] . '" berhasil dihapus'
                ]);
            } else {
                throw new Exception('No rows affected. User might not exist.');
            }
        } else {
            throw new Exception('Failed to delete user: ' . $delete_stmt->error);
        }

        $delete_stmt->close();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

    $check_stmt->close();

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