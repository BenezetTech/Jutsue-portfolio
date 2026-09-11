<?php

/**
 * Authorization Helper
 * Jutsue Mekodjio Bilios Portfolio CMS
 */

require_once __DIR__ . '/auth.php';

/**
 * Check whether the current administrator has a specific role.
 */
function hasRole(string $requiredRole): bool
{
    return currentAdminRole() === $requiredRole;
}

/**
 * Check whether the current administrator is a super administrator.
 */
function isSuperAdmin(): bool
{
    return hasRole('super_admin');
}

/**
 * Require super administrator privileges.
 */
function requireSuperAdmin(): void
{
    requireLogin();

  if (!isSuperAdmin()) {
    header('Location: /jutsue-portfolio/admin/access-denied.php');
    exit;
}
}