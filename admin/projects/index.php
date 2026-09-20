<?php

// Show PHP errors during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once __DIR__ . '/../../config/database.php';

// Authentication
require_once __DIR__ . '/../../includes/auth/auth.php';

// Make sure admin is logged in
requireLogin();


// --------------------------------------------------
// FETCH ALL PROJECTS
// --------------------------------------------------

$projects = [];

try {

    $stmt = $pdo->query("
        SELECT
            id,
            title,
            slug,
            category,
            short_description,
            full_description,
            technologies,
            image,
            project_url,
            year,
            featured,
            display_order,
            created_at,
            updated_at
        FROM projects
        ORDER BY display_order ASC, year DESC, title ASC
    ");

    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        '<div style="
            padding: 30px;
            font-family: Arial, sans-serif;
            color: #b00020;
            background: #fff5f5;
            border: 1px solid #f1b0b7;
            margin: 30px;
            border-radius: 8px;
        ">
            <h2>Projects Database Error</h2>
            <p>
                <strong>Error:</strong>
                ' . htmlspecialchars($e->getMessage()) . '
            </p>
            <p>
                Please check your <strong>projects</strong> table and database connection.
            </p>
        </div>'
    );
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

    <title>Projects Management</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">


<div class="container-fluid py-4">

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="fw-bold mb-1">
                Projects Management
            </h1>

            <p class="text-muted mb-0">
                Manage professional projects displayed on the portfolio.
            </p>

        </div>

        <a
            href="create.php"
            class="btn btn-primary"
        >
            + Add Project
        </a>

    </div>


    <!-- SUCCESS / ERROR MESSAGES -->

    <?php if (isset($_GET['created'])): ?>

        <div class="alert alert-success">
            Project created successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET['updated'])): ?>

        <div class="alert alert-success">
            Project updated successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET['deleted'])): ?>

        <div class="alert alert-success">
            Project deleted successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET['error'])): ?>

        <div class="alert alert-danger">

            <?php

            $error = $_GET['error'];

            switch ($error) {

                case 'not_found':
                    echo 'The requested project was not found.';
                    break;

                case 'delete_error':
                    echo 'Unable to delete the project.';
                    break;

                case 'invalid':
                    echo 'Invalid request.';
                    break;

                default:
                    echo 'An unexpected error occurred.';
                    break;
            }

            ?>

        </div>

    <?php endif; ?>


    <!-- PROJECTS CARD -->

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <?php if (empty($projects)): ?>

                <!-- EMPTY STATE -->

                <div class="text-center py-5">

                    <h3 class="fw-bold">
                        No Projects Found
                    </h3>

                    <p class="text-muted">
                        You have not added any professional projects yet.
                    </p>

                    <a
                        href="create.php"
                        class="btn btn-primary"
                    >
                        Add Your First Project
                    </a>

                </div>

            <?php else: ?>

                <!-- PROJECTS TABLE -->

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-dark">

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    Image
                                </th>

                                <th>
                                    Project
                                </th>

                                <th>
                                    Category
                                </th>

                                <th>
                                    Technologies
                                </th>

                                <th>
                                    Year
                                </th>

                                <th>
                                    Featured
                                </th>

                                <th>
                                    Order
                                </th>

                                <th>
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($projects as $project): ?>

                            <tr>

                                <!-- ID -->

                                <td>
                                    <?= htmlspecialchars($project['id']) ?>
                                </td>


                                <!-- IMAGE -->

                                <td>

                                    <?php if (!empty($project['image'])): ?>

                                        <img
                                            src="../../<?= htmlspecialchars($project['image']) ?>"
                                            alt="<?= htmlspecialchars($project['title']) ?>"
                                            width="70"
                                            height="50"
                                            style="
                                                object-fit: cover;
                                                border-radius: 6px;
                                            "
                                        >

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No image
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- PROJECT -->

                                <td>

                                    <strong>
                                        <?= htmlspecialchars($project['title']) ?>
                                    </strong>

                                    <?php if (!empty($project['short_description'])): ?>

                                        <div class="small text-muted mt-1">

                                            <?= htmlspecialchars(
                                                $project['short_description']
                                            ) ?>

                                        </div>

                                    <?php endif; ?>

                                </td>


                                <!-- CATEGORY -->

                                <td>

                                    <?php if (!empty($project['category'])): ?>

                                        <span class="badge bg-secondary">

                                            <?= htmlspecialchars(
                                                $project['category']
                                            ) ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- TECHNOLOGIES -->

                                <td>

                                    <?php if (!empty($project['technologies'])): ?>

                                        <?= htmlspecialchars(
                                            $project['technologies']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- YEAR -->

                                <td>

                                    <?php if (!empty($project['year'])): ?>

                                        <?= htmlspecialchars(
                                            $project['year']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- FEATURED -->

                                <td>

                                    <?php if ((int)$project['featured'] === 1): ?>

                                        <span class="badge bg-success">
                                            Yes
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            No
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- DISPLAY ORDER -->

                                <td>

                                    <?= htmlspecialchars(
                                        $project['display_order']
                                    ) ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="d-flex gap-2">

                                        <a
                                            href="edit.php?id=<?= urlencode($project['id']) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Edit
                                        </a>


                                        <a
                                            href="delete.php?id=<?= urlencode($project['id']) ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this project?');"
                                        >
                                            Delete
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>


<!-- Bootstrap JavaScript -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>