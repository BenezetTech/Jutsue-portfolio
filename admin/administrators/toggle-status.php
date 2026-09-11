<?php

/**
 * Toggle Administrator Status
 * Jutsue Mekodjio Bilios Portfolio CMS
 *
 * Super Administrator only.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/permissions.php';

requireSuperAdmin();

/*
|--------------------------------------------------------------------------
| Get Administrator ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Current Administrator
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            name,
            email,
            role,
            is_active
        FROM admin_users
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        'id' => $id
    ]);

    $administrator = $stmt->fetch();

    if (!$administrator) {
        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {

    error_log(
        'Administrator status lookup error: ' . $e->getMessage()
    );

    exit('Unable to load administrator.');
}

/*
|--------------------------------------------------------------------------
| Prevent Self-Deactivation
|--------------------------------------------------------------------------
*/

$currentAdminId = currentAdminId();

if ($id === $currentAdminId) {

    header('Location: index.php?error=self_status');
    exit;
}

/*
|--------------------------------------------------------------------------
| Toggle Account Status
|--------------------------------------------------------------------------
*/

$newStatus = (int) $administrator['is_active'] === 1 ? 0 : 1;

try {

    $update = $pdo->prepare("
        UPDATE admin_users
        SET
            is_active = :is_active,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = :id
    ");

    $update->execute([
        'is_active' => $newStatus,
        'id' => $id
    ]);

    header(
        'Location: index.php?status_updated=' . $newStatus
    );

    exit;

} catch (PDOException $e) {

    error_log(
        'Administrator status update error: ' . $e->getMessage()
    );

    header('Location: index.php?error=status_update');
    exit;
}