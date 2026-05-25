<?php
/**
 * Session Manager
 * Centralized session handling with secure defaults.
 */

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_only_cookies', '1');

        session_start();
    }
}

function setUser(array $user): void
{
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'] ?? 'patient';
    $_SESSION['logged_in']  = true;
    $_SESSION['login_time'] = time();

    // Automatically authorize for Cephalo AI as well!
    $_SESSION['modul_11_authorized'] = true;
    $_SESSION['modul_11_role'] = 'Dokter Ortodonti';

    session_regenerate_id(true);
}

function getSessionUser(): ?array
{
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return null;
    }

    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['user_role'] ?? 'patient',
    ];
}

function destroySession(): void
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();
}

function setFlash(string $key, string $value): void
{
    $_SESSION['flash'][$key] = $value;
}

function getFlash(string $key): ?string
{
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}
?>
