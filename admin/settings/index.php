<?php

/**
 * Super Administrator Settings
 * Jutsue Mekodjio Bilios Portfolio CMS
 */

require_once __DIR__ . '/../../includes/auth/permissions.php';

requireSuperAdmin();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Settings | Portfolio CMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="card shadow-sm border-0">
        <div class="card-body p-5">

            <h1 class="h3 fw-bold mb-3">
                System Settings
            </h1>

            <p class="text-muted">
                This area is restricted to the Super Administrator.
            </p>

            <div class="alert alert-success">
                Authorization successful.
                You have <strong>super_admin</strong> privileges.
            </div>

            <a
                href="../dashboard/index.php"
                class="btn btn-dark"
            >
                Back to Dashboard
            </a>

        </div>
    </div>

</div>

</body>
</html>