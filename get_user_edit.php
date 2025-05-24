<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'db.php';

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
    if ($id <= 0) {
        throw new Exception('Invalid user ID');
    }

    // Get user data
    $sql = "SELECT id, name, email, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        throw new Exception('User not found');
    }

    // Generate edit form HTML
    ?>
    <div class="modal-title">Edit User</div>
    <form onsubmit="return submitEditUser(event, <?php echo $user['id']; ?>)">
        <div class="form-group">
            <label for="edit-name">Nama:</label>
            <input type="text" id="edit-name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="edit-email">Email:</label>
            <input type="email" id="edit-email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="edit-password">Password: (kosongkan jika tidak ingin mengubah)</label>
            <input type="password" id="edit-password" name="password">
        </div>
        <div class="form-group">
            <label for="edit-role">Role:</label>
            <select id="edit-role" name="role" required>
                <option value="petugas" <?php echo $user['role'] === 'petugas' ? 'selected' : ''; ?>>Petugas</option>
                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="anggota" <?php echo $user['role'] === 'anggota' ? 'selected' : ''; ?>>Anggota</option>
            </select>
        </div>
        <button type="submit" class="btn">Simpan Perubahan</button>
        <div id="editError" class="error-message"></div>
    </form>
    <?php

    $stmt->close();

} catch (Exception $e) {
    echo '<div class="error-message">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?> 