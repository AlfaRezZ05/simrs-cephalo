<?php
/**
 * Session Manager
 * Centralized session handling with secure defaults.
 */

// ── Secure Signed Cookie Session Storage (Fixes Vercel Serverless Stateless Session Loop) ──
function setSecureCookie(string $name, array $data): void {
    $json = json_encode($data);
    $secret = 'simrs-cephalo-signature-key-123456';
    $signature = hash_hmac('sha256', $json, $secret);
    $cookieValue = base64_encode($json . '|' . $signature);
    
    $secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
              (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
              
    setcookie($name, $cookieValue, time() + 86400 * 30, '/', '', $secure, true);
}

function getSecureCookie(string $name): ?array {
    if (!isset($_COOKIE[$name])) return null;
    $raw = base64_decode($_COOKIE[$name], true);
    if (!$raw || strpos($raw, '|') === false) return null;
    list($json, $signature) = explode('|', $raw, 2);
    $secret = 'simrs-cephalo-signature-key-123456';
    $expectedSignature = hash_hmac('sha256', $json, $secret);
    if (hash_equals($expectedSignature, $signature)) {
        return json_decode($json, true);
    }
    return null;
}

function saveSessionToCookie(): void {
    if (isset($_SESSION) && is_array($_SESSION)) {
        setSecureCookie('SIMRS_SESS', $_SESSION);
    }
}

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Prevent warning in Vercel read-only environment by setting save path to /tmp
        if (is_writable('/tmp')) {
            session_save_path('/tmp');
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_only_cookies', '1');

        @session_start();

        // Restore session from our tamper-proof secure cookie
        if (isset($_COOKIE['SIMRS_SESS'])) {
            $data = getSecureCookie('SIMRS_SESS');
            if ($data && is_array($data)) {
                $_SESSION = array_merge($_SESSION, $data);
            }
        }

        // Register auto-save on request shutdown
        register_shutdown_function('saveSessionToCookie');
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

    // Save changes to cookie immediately
    saveSessionToCookie();

    @session_regenerate_id(true);
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

    // Clear the secure cookie as well
    setcookie('SIMRS_SESS', '', time() - 42000, '/');

    @session_destroy();
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
