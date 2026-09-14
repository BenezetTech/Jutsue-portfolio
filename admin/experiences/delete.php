<?php

/**
 * Delete Professional Experience
 * Jutsue Mekodjio Bilios Portfolio CMS
 *
 * Authenticated administrators only.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/auth.php';

requireLogin();

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
| Get Experience ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php?error=invalid_id');
    exit;
}

/*
|--------------------------------------------------------------------------
| Make Sure Experience Exists
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT id
        FROM experiences
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        'id' => $id
    ]);

    $experience = $stmt->fetch();

    if (!$experience) {
        header('Location: index.php?error=not_found');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Experience
    |--------------------------------------------------------------------------
    */

    $delete = $pdo->prepare("
        DELETE FROM experiences
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
        'Experience deletion error: ' .
        $e->getMessage()
    );

    header('Location: index.php?error=delete_failed');
    exit;
}