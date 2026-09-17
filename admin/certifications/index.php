<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

$adminName = $_SESSION['admin_name'] ?? 'Administrator';
$adminRole = $_SESSION['admin_role'] ?? 'admin';

// Status messages
$statusMessage = '';
$statusType = '';

// Created
if (isset($_GET['created']) && $_GET['created'] == '1') {
    $statusMessage = 'Certification added successfully.';
    $statusType = 'success';
}

// Updated
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $statusMessage = 'Certification updated successfully.';
    $statusType = 'success';
}

// Deleted
if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    $statusMessage = 'Certification deleted successfully.';
    $statusType = 'success';
}

// Invalid ID
if (isset($_GET['invalid_id']) && $_GET['invalid_id'] == '1') {
    $statusMessage = 'Invalid certification ID.';
    $statusType = 'danger';
}

// Not found
if (isset($_GET['not_found']) && $_GET['not_found'] == '1') {
    $statusMessage = 'Certification not found.';
    $statusType = 'danger';
}

// Delete failed
if (isset($_GET['delete_failed']) && $_GET['delete_failed'] == '1') {
    $statusMessage = 'Unable to delete the certification.';
    $statusType = 'danger';
}

try {
    $stmt = $pdo->query("
        SELECT
            id,
            title,
            organization,
            year,
            description,
            certificate_file
        FROM certifications
        ORDER BY year DESC, id DESC
    ");

    $certifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $certifications = [];
    $statusMessage = 'Unable to load certifications.';
    $statusType = 'danger';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Certifications | Jutsue Portfolio Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f5f7fa;
            color: #172033;
            font-family: Arial, sans-serif;
        }

        .page-wrapper {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            background: #0b1220;
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .page-header h1 {
            margin-bottom: 8px;
        }

        .page-header p {
            margin: 0;
            color: #cbd5e1;
        }

        .top-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn-add {
            background: #19d3c5;
            color: #0b1220;
            border: none;
            font-weight: 600;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
        }

        .btn-add:hover {
            background: #14b8aa;
            color: #0b1220;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .table th {
            background: #111c2e;
            color: white;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .btn-edit {
            background: #5b8cff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-edit:hover {
            background: #4675e8;
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-delete:hover {
            background: #bb2d3b;
        }

        .description {
            max-width: 350px;
        }

        .file-badge {
            display: inline-block;
            background: #e8f7f5;
            color: #087f75;
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 12px;
            text-decoration: none;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #667085;
        }

        .empty-state h3 {
            color: #172033;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .top-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-add {
                text-align: center;
            }

            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
</head>

<body>

<div class="page-wrapper">

    <div class="page-header">
        <h1>Certifications</h1>
        <p>
            Manage professional certifications and certificates displayed on the portfolio.
        </p>
    </div>

    <div class="mb-3">
        <strong>Logged in as:</strong>
        <?= htmlspecialchars($adminName) ?>
        &nbsp; | &nbsp;
        <strong>Role:</strong>
        <?= htmlspecialchars($adminRole) ?>
    </div>

    <?php if ($statusMessage): ?>

        <div class="alert alert-<?= htmlspecialchars($statusType) ?>">
            <?= htmlspecialchars($statusMessage) ?>
        </div>

    <?php endif; ?>

    <div class="top-actions">
        <a href="../index.php" class="btn btn-secondary">
            ← Dashboard
        </a>

        <a href="create.php" class="btn-add">
            + Add Certification
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <?php if (empty($certifications)): ?>

                <div class="empty-state">
                    <h3>No certifications yet</h3>
                    <p>
                        Add the first certification or professional certificate
                        to begin building the certifications section.
                    </p>

                    <a href="create.php" class="btn btn-primary">
                        Add Certification
                    </a>
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover mb-0">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Organization</th>
                                <th>Year</th>
                                <th>Description</th>
                                <th>Certificate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($certifications as $certification): ?>

                            <tr>

                                <td>
                                    <?= (int) $certification['id'] ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($certification['title']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($certification['organization']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($certification['year']) ?>
                                </td>

                                <td class="description">
                                    <?php if (!empty($certification['description'])): ?>

                                        <?= nl2br(htmlspecialchars($certification['description'])) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No description
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td>

                                    <?php if (!empty($certification['certificate_file'])): ?>

                                        <a
                                            href="../../<?= htmlspecialchars($certification['certificate_file']) ?>"
                                            target="_blank"
                                            class="file-badge"
                                        >
                                            View Certificate
                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No file
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <div class="d-flex gap-2">

                                        <a
                                            href="edit.php?id=<?= (int) $certification['id'] ?>"
                                            class="btn-edit"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="delete.php"
                                            onsubmit="return confirm('Are you sure you want to delete this certification?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $certification['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn-delete"
                                            >
                                                Delete
                                            </button>
                                        </form>

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

</body>
</html>