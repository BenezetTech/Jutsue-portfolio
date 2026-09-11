```php
<?php

/**
 * Edit Administrator
 * Jutsue Mekodjio Bilios Portfolio CMS
 *
 * Super Administrator only.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/permissions.php';

requireSuperAdmin();

/*
|--------------------------------------------------------------------------
| Get Administrator ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$error = '';

$administrator = null;

/*
|--------------------------------------------------------------------------
| Load Administrator
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            name,
            email,
            role,
            is_active
        FROM admin_users
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        'id' => $id
    ]);

    $administrator = $stmt->fetch();

    if (!$administrator) {
        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {

    error_log(
        'Administrator lookup error: ' . $e->getMessage()
    );

    exit('Unable to load administrator.');
}

/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | Collect Form Data
    |--------------------------------------------------------------------------
    */

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'admin';
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $currentAdminId = currentAdminId();

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $id === $currentAdminId &&
        ($role !== 'super_admin' || $isActive !== 1)
    ) {

        $error =
            'You cannot remove Super Admin privileges or deactivate your own account.';

    } elseif ($name === '' || $email === '') {

        $error = 'Name and email are required.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } elseif (!in_array($role, ['admin', 'super_admin'], true)) {

        $error = 'Invalid administrator role selected.';

    } elseif ($password !== '' && strlen($password) < 8) {

        $error = 'New password must be at least 8 characters long.';

    } elseif (
        $password !== '' &&
        $password !== $confirmPassword
    ) {

        $error = 'The passwords do not match.';

    } else {

        /*
        |--------------------------------------------------------------------------
        | Update Administrator
        |--------------------------------------------------------------------------
        */

        try {

            /*
            |--------------------------------------------------------------------------
            | Check Duplicate Email
            |--------------------------------------------------------------------------
            */

            $check = $pdo->prepare("
                SELECT id
                FROM admin_users
                WHERE email = :email
                AND id != :id
                LIMIT 1
            ");

            $check->execute([
                'email' => $email,
                'id' => $id
            ]);

            if ($check->fetch()) {

                $error =
                    'Another administrator already uses this email address.';

            } else {

                /*
                |--------------------------------------------------------------------------
                | Update With New Password
                |--------------------------------------------------------------------------
                */

                if ($password !== '') {

                    $hashedPassword = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $update = $pdo->prepare("
                        UPDATE admin_users
                        SET
                            name = :name,
                            email = :email,
                            password = :password,
                            role = :role,
                            is_active = :is_active,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id = :id
                    ");

                    $update->execute([
                        'name' => $name,
                        'email' => $email,
                        'password' => $hashedPassword,
                        'role' => $role,
                        'is_active' => $isActive,
                        'id' => $id
                    ]);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Update Without Changing Password
                    |--------------------------------------------------------------------------
                    */

                    $update = $pdo->prepare("
                        UPDATE admin_users
                        SET
                            name = :name,
                            email = :email,
                            role = :role,
                            is_active = :is_active,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id = :id
                    ");

                    $update->execute([
                        'name' => $name,
                        'email' => $email,
                        'role' => $role,
                        'is_active' => $isActive,
                        'id' => $id
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Keep Current Session Data In Sync
                |--------------------------------------------------------------------------
                */

                if ($id === $currentAdminId) {

                    startSecureSession();

                    $_SESSION['admin_name'] = $name;
                    $_SESSION['admin_email'] = $email;
                    $_SESSION['admin_role'] = $role;
                }

                /*
                |--------------------------------------------------------------------------
                | Redirect After Successful Update
                |--------------------------------------------------------------------------
                */

                header('Location: index.php?updated=1');
                exit;
            }

        } catch (PDOException $e) {

            error_log(
                'Administrator update error: ' . $e->getMessage()
            );

            $error =
                'Unable to update the administrator.';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Keep Form Values After Validation Error
    |--------------------------------------------------------------------------
    */

    $administrator['name'] = $name;
    $administrator['email'] = $email;
    $administrator['role'] = $role;
    $administrator['is_active'] = $isActive;
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
        Edit Administrator | Portfolio CMS
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
                        Edit Administrator
                    </h1>

                    <p class="text-muted mb-4">
                        Update administrator account information and permissions.
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

                        <!-- Full Name -->

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
                                    $administrator['name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >

                        </div>

                        <!-- Email -->

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
                                    $administrator['email'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                required
                            >

                        </div>

                        <!-- Role -->

                        <div class="mb-3">

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

                                <option
                                    value="admin"
                                    <?= $administrator['role'] === 'admin'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Admin
                                </option>

                                <option
                                    value="super_admin"
                                    <?= $administrator['role'] === 'super_admin'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Super Admin
                                </option>

                            </select>

                        </div>

                        <!-- Account Status -->

                        <div class="form-check mb-4">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="is_active"
                                name="is_active"
                                <?= (int) $administrator['is_active'] === 1
                                    ? 'checked'
                                    : '' ?>
                            >

                            <label
                                class="form-check-label"
                                for="is_active"
                            >
                                Account is active
                            </label>

                        </div>

                        <hr class="my-4">

                        <!-- Password -->

                        <h2 class="h5 fw-bold">
                            Change Password
                        </h2>

                        <p class="text-muted small">
                            Leave these fields empty to keep the current password.
                        </p>

                        <div class="mb-3">

                            <label
                                for="password"
                                class="form-label"
                            >
                                New Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                minlength="8"
                                autocomplete="new-password"
                            >

                        </div>

                        <!-- Confirm Password -->

                        <div class="mb-4">

                            <label
                                for="confirm_password"
                                class="form-label"
                            >
                                Confirm New Password
                            </label>

                            <input
                                type="password"
                                class="form-control"
                                id="confirm_password"
                                name="confirm_password"
                                minlength="8"
                                autocomplete="new-password"
                            >

                        </div>

                        <!-- Actions -->

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
                                Save Changes
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
```
