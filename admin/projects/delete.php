<?php

// --------------------------------------------------
// ERROR REPORTING
// --------------------------------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);


// --------------------------------------------------
// DATABASE & AUTHENTICATION
// --------------------------------------------------

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/auth.php';

requireLogin();


// --------------------------------------------------
// GET PROJECT ID
// --------------------------------------------------

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;


// --------------------------------------------------
// VALIDATE PROJECT ID
// --------------------------------------------------

if ($id <= 0) {

    header('Location: index.php?error=invalid_id');
    exit;
}


// --------------------------------------------------
// FIND PROJECT
// --------------------------------------------------

try {

    $stmt = $pdo->prepare("
        SELECT id, image
        FROM projects
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {

        header('Location: index.php?error=not_found');
        exit;
    }

} catch (PDOException $e) {

    header('Location: index.php?error=database_error');
    exit;
}


// --------------------------------------------------
// DELETE PROJECT
// --------------------------------------------------

try {

    $stmt = $pdo->prepare("
        DELETE FROM projects
        WHERE id = ?
    ");

    $stmt->execute([$id]);


    // --------------------------------------------------
    // DELETE ASSOCIATED IMAGE
    // --------------------------------------------------

    if (!empty($project['image'])) {

        $image_path =
            __DIR__ .
            '/../../' .
            $project['image'];

        if (file_exists($image_path)) {

            unlink($image_path);
        }
    }


    // --------------------------------------------------
    // REDIRECT AFTER SUCCESS
    // --------------------------------------------------

    header('Location: index.php?deleted=1');
    exit;

} catch (PDOException $e) {

    header('Location: index.php?error=delete_error');
    exit;
}