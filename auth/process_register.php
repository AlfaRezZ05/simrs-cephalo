<?php
/**
 * Process Register
 */

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/validator.php';
require_once __DIR__ . '/../config/database.php';

startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/auth/register.php');
    exit;
}

$db = getDBConnection();

$validator = new Validator($_POST, $db);
$validator
    ->required('name', 'Nama Lengkap')
    ->maxLength('name', 100, 'Nama Lengkap')
    ->required('email', 'Email')
    ->email('email', 'Email')
    ->unique('email', 'users', 'email', 'Email')
    ->required('password', 'Kata Sandi')
    ->minLength('password', 6, 'Kata Sandi')
    ->required('password_confirm', 'Konfirmasi Kata Sandi')
    ->match('password', 'password_confirm', 'Konfirmasi Kata Sandi');

if ($validator->fails()) {
    $_SESSION['validation_errors'] = $validator->errors();
    $_SESSION['old_input'] = [
        'name'  => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'role'  => $_POST['role'] ?? '',
    ];
    header('Location: ' . BASE_URL . '/auth/register.php');
    exit;
}

$name     = trim($_POST['name']);
$email    = trim($_POST['email']);
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role     = in_array($_POST['role'] ?? '', ['patient', 'dokter', 'farmasi']) ? $_POST['role'] : 'patient';

try {
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)");
    $stmt->execute([
        'name'     => $name,
        'email'    => $email,
        'password' => $password,
        'role'     => $role,
    ]);

    setFlash('success', 'Akun pakar berhasil dibuat! Silakan masuk.');
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;

} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    setFlash('error', 'Terjadi kesalahan saat menyimpan data. Coba lagi.');
    $_SESSION['old_input'] = [
        'name'  => $_POST['name'] ?? '',
        'email' => $_POST['email'] ?? '',
        'role'  => $_POST['role'] ?? '',
    ];
    header('Location: ' . BASE_URL . '/auth/register.php');
    exit;
}
?>
