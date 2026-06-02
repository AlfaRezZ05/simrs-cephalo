<?php
/**
 * Auth Helper
 * Authentication utilities for checking login state.
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/database.php';

function isLoggedIn(): bool
{
    startSession();
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin(string $redirect = ''): void
{
    if ($redirect === '') $redirect = BASE_URL . '/auth/login.php';
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

function requireGuest(string $redirect = ''): void
{
    if ($redirect === '') $redirect = BASE_URL . '/index.php';
    if (isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

function getCurrentUser(): ?array
{
    startSession();
    return getSessionUser();
}

function getUserInitials(): string
{
    $user = getCurrentUser();
    if (!$user) return '?';

    $parts = explode(' ', trim($user['name']));
    $initials = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $initials .= strtoupper(substr(end($parts), 0, 1));
    }
    return $initials;
}

// ── Role-Based Access Control (RBAC) ──

function getUserRole(): string
{
    $user = getCurrentUser();
    return $user['role'] ?? 'patient';
}

function hasRole(string $role): bool
{
    return getUserRole() === $role;
}

function requireRole(array $allowedRoles): void
{
    requireLogin();
    if (!in_array(getUserRole(), $allowedRoles)) {
        header('Location: ' . BASE_URL . '/auth/forbidden.php');
        exit;
    }
}

function isAdmin(): bool    { return hasRole('admin'); }
function isDokter(): bool   { return hasRole('dokter'); }
function isFarmasi(): bool  { return hasRole('farmasi'); }
function isPatient(): bool  { return hasRole('patient'); }
?>
