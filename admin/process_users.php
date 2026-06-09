<?php
/**
 * Admin Panel — Process User Adjustments (Update Role & Delete)
 */

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['admin']);
startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$currentUser = getCurrentUser();
$db = getDBConnection();

$action = $_POST['action_type'] ?? '';
$targetUserId = (int)($_POST['user_id'] ?? 0);

// Prevention checks
if (in_array($action, ['update_role', 'delete_user']) && $targetUserId === (int)$currentUser['id']) {
    setFlash('error', 'Tindakan ilegal: Anda tidak dapat mengubah peran atau menghapus akun Anda sendiri.');
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

if ($action === 'update_role') {
    $newRole = $_POST['role'] ?? 'patient';
    
    // Validate role whitelist
    if (!in_array($newRole, ['admin', 'dokter', 'farmasi', 'patient'])) {
        setFlash('error', 'Peran (role) yang dipilih tidak valid.');
        header('Location: ' . BASE_URL . '/admin/users.php');
        exit;
    }

    try {
        $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$newRole, $targetUserId]);
        setFlash('success', 'Peran pengguna berhasil diperbarui menjadi ' . ucfirst($newRole));
    } catch (PDOException $e) {
        setFlash('error', 'Gagal memperbarui peran pengguna: ' . $e->getMessage());
    }
}

elseif ($action === 'delete_user') {
    try {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$targetUserId]);
        setFlash('success', 'Akun pengguna berhasil dihapus secara permanen.');
    } catch (PDOException $e) {
        setFlash('error', 'Gagal menghapus pengguna: ' . $e->getMessage());
    }
}

elseif ($action === 'update_password') {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($newPassword) < 6) {
        setFlash('error', 'Kata sandi baru minimal harus 6 karakter.');
    } elseif ($newPassword !== $confirmPassword) {
        setFlash('error', 'Konfirmasi kata sandi baru tidak cocok.');
    } else {
        try {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $targetUserId]);
            setFlash('success', 'Kata sandi pengguna berhasil diperbarui.');
        } catch (PDOException $e) {
            setFlash('error', 'Gagal memperbarui kata sandi: ' . $e->getMessage());
        }
    }
}

header('Location: ' . BASE_URL . '/admin/users.php');
exit;
