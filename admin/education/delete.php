<?php
require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

// Get the education record ID from POST
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

// Validate ID
if ($id <= 0) {
    header('Location: index.php?invalid_id=1');
    exit;
}

try {

    // Check that the education record exists
    $stmt = $pdo->prepare("SELECT id FROM education WHERE id = ?");
    $stmt->execute([$id]);

    $education = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$education) {
        header('Location: index.php?not_found=1');
        exit;
    }

    // Delete the education record
    $stmt = $pdo->prepare("DELETE FROM education WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        header('Location: index.php?deleted=1');
        exit;
    }

    // Delete failed
    header('Location: index.php?delete_failed=1');
    exit;

} catch (PDOException $e) {

    // Database error
    header('Location: index.php?delete_failed=1');
    exit;
}