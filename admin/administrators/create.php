<?php

/**
 * Create Administrator
 * Jutsue Mekodjio Bilios Portfolio CMS
 *
 * Super Administrator only.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/permissions.php';

requireSuperAdmin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'admin';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please complete all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'The passwords do not match.';
    } elseif (!in_array($role, ['admin', 'super_admin'], true)) {
        $error = 'Invalid administrator role selected.';
    } else {

        try {

            $check = $pdo->prepare("
                SELECT id
                FROM admin_users
                WHERE email = :email
                LIMIT 1
            ");

            $check->execute([
                'email' => $email
            ]);

            if ($check->fetch()) {

                $error = 'An administrator with this email already exists.';

            } else {

                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $stmt = $pdo->prepare("
                    INSERT INTO admin_users
                        (name, email, password, role, is_active)
                    VALUES
                        (:name, :email, :password, :role, 1)
                ");

                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashedPassword,
                    'role' => $role
                ]);

                header('Location: index.php?created=1');
                exit;
            }

        } catch (PDOException $e) {

            error_log(
                'Administrator creation error: ' . $e->getMessage()
            );

            $error = 'Unable to create the administrator account.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Add Administrator | Portfolio CMS
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <span class="navbar-brand fw-bold">
            Jutsue Portfolio CMS
        </span>

        <a
            href="index.php"
            class="btn btn-outline-light btn-sm"
        >
            Back
        </a>

    </div>

</nav>

<main class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4 p-md-5">

                    <h1 class="h3 fw-bold mb-1">
                        Add Administrator
                    </h1>

                    <p class="text-muted mb-4">
                        Create a new authorized CMS administrator.
                    </p>

                    <?php if ($error): ?>

                        <div class="alert alert-danger">
                            <?= htmlspecialchars(
                                $error,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </div>

                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">

                            <label
                                for="name"
                                class="form-label"
                            >
                                Full Name
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="name"
                                name="name"
                                value="<?= htmlspecialchars(
                                    $_POST['name'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >

                        </div>

                        <div class="mb-3">

                            <label
                                for="email"
                                class="form-label"
                            >
                                Email Address
                            </label>

                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars(
                                    $_POST['email'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >

                        </div>

                        <div class="mb-3">

                            <label
                                for="password"
                                class="form-label"
                            >
                                Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                minlength="8"
                                required
                            >

                        </div>

                        <div class="mb-3">

                            <label
                                for="confirm_password"
                                class="form-label"
                            >
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="confirm_password"
                                name="confirm_password"
                                minlength="8"
                                required
                            >

                        </div>

                        <div class="mb-4">

                            <label
                                for="role"
                                class="form-label"
                            >
                                Administrator Role
                            </label>

                            <select
                                class="form-select"
                                id="role"
                                name="role"
                                required
                            >

                                <option value="admin">
                                    Admin
                                </option>

                                <option value="super_admin">
                                    Super Admin
                                </option>

                            </select>

                        </div>

                        <div class="d-flex gap-2">

                            <a
                                href="index.php"
                                class="btn btn-outline-secondary"
                            >
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="btn btn-dark"
                            >
                                Create Administrator
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</main>

</body>

</html>