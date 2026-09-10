<?php

/**
 * Administrator Authentication
 * Jutsue Mekodjio Bilios Portfolio CMS
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/auth.php';

startSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 'Please enter your email address and password.';
    header('Location: login.php');
    exit;
}

try {
    $sql = "
        SELECT id, name, email, password, role, is_active
        FROM admin_users
        WHERE email = :email
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'email' => $email
    ]);

    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password'])) {
        $_SESSION['login_error'] = 'Invalid email address or password.';
        header('Location: login.php');
        exit;
    }

    if ((int) $admin['is_active'] !== 1) {
        $_SESSION['login_error'] = 'This administrator account is inactive.';
        header('Location: login.php');
        exit;
    }

    // Regenerate the session ID after successful login.
    session_regenerate_id(true);

    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'];

    // Record the successful login.
    $update = $pdo->prepare("
        UPDATE admin_users
        SET last_login = NOW()
        WHERE id = :id
    ");

    $update->execute([
        'id' => $admin['id']
    ]);

    header('Location: ../dashboard/index.php');
    exit;

} catch (PDOException $e) {

    error_log('Administrator login error: ' . $e->getMessage());

    $_SESSION['login_error'] = 'A system error occurred. Please try again.';
    header('Location: login.php');
    exit;
}