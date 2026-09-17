<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?delete_failed=1');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    header('Location: index.php?invalid_id=1');
    exit;
}

// Check that the certification exists
$stmt = $pdo->prepare("
    SELECT id
    FROM certifications
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$certification = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$certification) {
    header('Location: index.php?not_found=1');
    exit;
}

// Delete certification
$stmt = $pdo->prepare("
    DELETE FROM certifications
    WHERE id = ?
");

$success = $stmt->execute([$id]);

if ($success) {
    header('Location: index.php?deleted=1');
    exit;
}

header('Location: index.php?delete_failed=1');
exit;