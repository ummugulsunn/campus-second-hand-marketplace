<?php
declare(strict_types=1);

/**
 * Centralized helper functions for session handling and input sanitization.
 */

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool
{
    startSession();
    return isset($_SESSION['user_id']);
}

function requireLogin(): void
{
    startSession();
    if (!isLoggedIn()) {
        header('Location: ' . base_url('/pages/login.php'));
        exit;
    }
}

function loginUser(int $userId, string $userName, int $roleId, string $roleName): void
{
    startSession();
    $_SESSION['user_id']        = $userId;
    $_SESSION['user_name']      = $userName;
    $_SESSION['user_role_id']   = $roleId;
    $_SESSION['user_role_name'] = $roleName;
}

function logoutUser(): void
{
    startSession();
    session_unset();
    session_destroy();
}

function getCurrentUserName(): ?string
{
    startSession();
    return isset($_SESSION['user_name']) ? (string) $_SESSION['user_name'] : null;
}

function getCurrentUserRoleName(): ?string
{
    startSession();
    return isset($_SESSION['user_role_name']) ? (string) $_SESSION['user_role_name'] : null;
}

function hasRole(string $roleName): bool
{
    startSession();
    $storedRole = $_SESSION['user_role_name'] ?? null;
    return $storedRole !== null && strcasecmp($storedRole, $roleName) === 0;
}

/**
 * Basic XSS protection for output and simple input cleaning.
 */
function cleanInput(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

