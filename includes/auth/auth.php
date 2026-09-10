<?php

/**
 * Authentication Helper
 * Jutsue Mekodjio Bilios Portfolio CMS
 */

/**
 * Start a secure session if one is not already active.
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check whether an administrator is logged in.
 */
function isLoggedIn(): bool
{
    startSecureSession();

    return isset($_SESSION['admin_id']);
}

/**
 * Require the administrator to be logged in.
 * Redirect to the login page if not authenticated.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: /jutsue-portfolio/admin/auth/login.php');
        exit;
    }
}

/**
 * Get the currently logged-in administrator's ID.
 */
function currentAdminId(): ?int
{
    startSecureSession();

    return isset($_SESSION['admin_id'])
        ? (int) $_SESSION['admin_id']
        : null;
}

/**
 * Get the currently logged-in administrator's role.
 */
function currentAdminRole(): ?string
{
    startSecureSession();

    return $_SESSION['admin_role'] ?? null;
}

/**
 * Log the administrator out.
 */
function logoutAdmin(): void
{
    startSecureSession();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}