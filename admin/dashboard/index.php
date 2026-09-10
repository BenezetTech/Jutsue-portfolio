<?php

/**
 * Administrator Dashboard
 * Jutsue Mekodjio Bilios Portfolio CMS
 */

require_once __DIR__ . '/../../includes/auth/auth.php';

requireLogin();

$adminName = $_SESSION['admin_name'] ?? 'Administrator';
$adminRole = $_SESSION['admin_role'] ?? 'admin';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Jutsue Mekodjio Bilios Portfolio CMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">
            Jutsue Portfolio CMS
        </span>

        <a
            href="../auth/logout.php"
            class="btn btn-outline-light btn-sm"
        >
            Logout
        </a>
    </div>
</nav>

<main class="container py-5">

    <div class="mb-4">
        <h1 class="fw-bold">
            Welcome, <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>!
        </h1>

        <p class="text-muted">
            You are logged in successfully.
        </p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <h2 class="h4">Administrator Information</h2>

            <hr>

            <p>
                <strong>Name:</strong>
                <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p>
                <strong>Role:</strong>
                <?= htmlspecialchars($adminRole, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p class="mb-0 text-success">
                Authentication successful.
            </p>

        </div>
    </div>

</main>

</body>
</html>