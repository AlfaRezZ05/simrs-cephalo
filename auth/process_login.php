<?php
/**
 * Process Login
 */

require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/validator.php';
require_once __DIR__ . '/../config/database.php';

startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$db = getDBConnection();

$validator = new Validator($_POST);
$validator
    ->required('email', 'Email')
    ->email('email', 'Email')
    ->required('password', 'Kata Sandi');

if ($validator->fails()) {
    $firstError = array_values($validator->errors())[0];
    setFlash('error', $firstError);
    setFlash('old_email', $_POST['email'] ?? '');
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$email    = trim($_POST['email']);
$password = $_POST['password'];

try {
    $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        setFlash('error', 'Email atau kata sandi tidak valid.');
        setFlash('old_email', $email);
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }

    setUser($user);
    header('Location: ' . BASE_URL . '/index.php');
    exit;

} catch (PDOException $e) {
    setFlash('error', 'Terjadi kesalahan basis data.');
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}
?>
