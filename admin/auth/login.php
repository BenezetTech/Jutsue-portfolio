<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/auth.php';

startSecureSession();

if (isLoggedIn()) {
    header('Location: ../dashboard/index.php');
    exit;
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Jutsue Mekodjio Bilios</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center justify-content-center">

    <div class="card shadow-sm border-0" style="max-width: 430px; width: 100%;">
        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">
                <h1 class="h3 fw-bold">Administrator Login</h1>
                <p class="text-muted mb-0">
                    Jutsue Mekodjio Bilios Portfolio CMS
                </p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form action="authenticate.php" method="POST">

                <div class="mb-3">
                    <label for="email" class="form-label">
                        Email Address
                    </label>

                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                        autocomplete="email"
                    >
                </div>

              <div class="mb-4">
    <label for="password" class="form-label">
        Password
    </label>

    <div class="input-group">
        <input
            type="password"
            class="form-control"
            id="password"
            name="password"
            placeholder="Enter your password"
            required
            autocomplete="current-password"
        >

        <button
            type="button"
            class="btn btn-outline-secondary"
            id="togglePassword"
            aria-label="Show password"
            title="Show password"
        >
            👁
        </button>
    </div>
</div>

                <button type="submit" class="btn btn-dark w-100">
                    Sign In
                </button>

            </form>

        </div>
    </div>

</div>


<script>
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', function () {
        const isPasswordHidden = passwordInput.type === 'password';

        passwordInput.type = isPasswordHidden ? 'text' : 'password';

        this.textContent = isPasswordHidden ? '🙈' : '👁';
        this.setAttribute(
            'aria-label',
            isPasswordHidden ? 'Hide password' : 'Show password'
        );
        this.setAttribute(
            'title',
            isPasswordHidden ? 'Hide password' : 'Show password'
        );
    });
</script>

</body>
</html>