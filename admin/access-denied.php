<?php

/**
 * Access Denied Page
 * Jutsue Mekodjio Bilios Portfolio CMS
 */

http_response_code(403);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Access Denied | Portfolio CMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container min-vh-100 d-flex align-items-center justify-content-center">

    <div class="card border-0 shadow-sm text-center" style="max-width: 500px;">

        <div class="card-body p-5">

            <div class="display-4 mb-3">
                🔒
            </div>

            <h1 class="h3 fw-bold mb-3">
                Access Denied
            </h1>

            <p class="text-muted mb-4">
                You do not have permission to access this section
                of the Portfolio CMS.
            </p>

            <a
                href="dashboard/index.php"
                class="btn btn-dark"
            >
                Return to Dashboard
            </a>

        </div>

    </div>

</div>

</body>
</html>