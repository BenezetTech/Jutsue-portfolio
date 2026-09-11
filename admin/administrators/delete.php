<?php

/**
 * Delete Administrator
 * Jutsue Mekodjio Bilios Portfolio CMS
 *
 * Super Administrator only.
 *
 * IMPORTANT:
 * This operation requires POST.
 * The current administrator cannot delete their own account.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/permissions.php';

requireSuperAdmin();

/*
|--------------------------------------------------------------------------
| Only Allow POST Requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Administrator ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php?error=invalid_id');
    exit;
}

/*
|--------------------------------------------------------------------------
| Prevent Self-Deletion
|--------------------------------------------------------------------------
*/

$currentAdminId = currentAdminId();

if ($id === $currentAdminId) {
    header('Location: index.php?error=self_delete');
    exit;
}

/*
|--------------------------------------------------------------------------
| Make Sure Administrator Exists
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT id, name, email, role
        FROM admin_users
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        'id' => $id
    ]);

    $administrator = $stmt->fetch();

    if (!$administrator) {
        header('Location: index.php?error=not_found');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Administrator
    |--------------------------------------------------------------------------
    */

    $delete = $pdo->prepare("
        DELETE FROM admin_users
        WHERE id = :id
        LIMIT 1
    ");

    $delete->execute([
        'id' => $id
    ]);

    if ($delete->rowCount() === 1) {

        header('Location: index.php?deleted=1');
        exit;
    }

    header('Location: index.php?error=delete_failed');
    exit;

} catch (PDOException $e) {

    error_log(
        'Administrator deletion error: ' . $e->getMessage()
    );

    header('Location: index.php?error=delete_failed');
    exit;
}