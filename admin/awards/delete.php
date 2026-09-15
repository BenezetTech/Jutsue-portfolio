<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?invalid_id=1');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php?invalid_id=1');
    exit;
}

try {
    // Check if the award exists
    $checkStmt = $pdo->prepare("
        SELECT id
        FROM awards
        WHERE id = ?
        LIMIT 1
    ");

    $checkStmt->execute([$id]);

    if (!$checkStmt->fetch()) {
        header('Location: index.php?not_found=1');
        exit;
    }

    // Delete the award
    $deleteStmt = $pdo->prepare("
        DELETE FROM awards
        WHERE id = ?
    ");

    $deleteStmt->execute([$id]);

    if ($deleteStmt->rowCount() > 0) {
        header('Location: index.php?deleted=1');
        exit;
    }

    header('Location: index.php?delete_failed=1');
    exit;

} catch (PDOException $e) {
    header('Location: index.php?delete_failed=1');
    exit;
}