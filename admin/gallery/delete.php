<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

// Get gallery ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php?invalid_id=1');
    exit;
}

// Get gallery item
$stmt = $pdo->prepare("
    SELECT
        id,
        image
    FROM gallery
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$gallery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gallery) {
    header('Location: index.php?not_found=1');
    exit;
}

// Delete database record
$stmt = $pdo->prepare("
    DELETE FROM gallery
    WHERE id = ?
");

$success = $stmt->execute([$id]);

if ($success) {

    // Delete associated image from uploads folder
    if (
        !empty($gallery['image']) &&
        strpos($gallery['image'], 'assets/uploads/gallery/') === 0
    ) {

        $imagePath = '../../' . $gallery['image'];

        if (
            file_exists($imagePath) &&
            is_file($imagePath)
        ) {
            unlink($imagePath);
        }
    }

    header('Location: index.php?deleted=1');
    exit;
}

header('Location: index.php?delete_error=1');
exit;