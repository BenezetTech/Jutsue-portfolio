<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?delete_error=1');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    header('Location: index.php?invalid_id=1');
    exit;
}

// Fetch gallery item before deleting it
$stmt = $pdo->prepare("
    SELECT id, image
    FROM gallery
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$galleryItem = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$galleryItem) {
    header('Location: index.php?not_found=1');
    exit;
}

$imagePath = $galleryItem['image'];

// Delete database record
$stmt = $pdo->prepare("
    DELETE FROM gallery
    WHERE id = ?
");

try {

    $stmt->execute([$id]);

    /*
     * Delete the associated image only after
     * the database record has been successfully deleted.
     */
    if (!empty($imagePath)) {

        $uploadDirectory =
            realpath('../../assets/uploads/gallery/');

        $filePath =
            realpath(
                '../../' . ltrim($imagePath, '/')
            );

        /*
         * Security check:
         * Only allow deletion of files that are physically
         * inside the gallery upload directory.
         */
        if (
            $uploadDirectory !== false &&
            $filePath !== false &&
            strpos(
                $filePath,
                $uploadDirectory . DIRECTORY_SEPARATOR
            ) === 0 &&
            is_file($filePath)
        ) {
            unlink($filePath);
        }
    }

    header('Location: index.php?deleted=1');
    exit;

} catch (PDOException $e) {

    header('Location: index.php?delete_error=1');
    exit;
}