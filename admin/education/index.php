<?php
require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

$pageTitle = 'Education Management';
$statusMessage = '';
$statusType = '';

// Handle status messages from create/edit/delete actions
if (isset($_GET['created'])) {
    $statusMessage = 'Education record created successfully.';
    $statusType = 'success';
} elseif (isset($_GET['updated'])) {
    $statusMessage = 'Education record updated successfully.';
    $statusType = 'success';
} elseif (isset($_GET['deleted'])) {
    $statusMessage = 'Education record deleted successfully.';
    $statusType = 'success';
} elseif (isset($_GET['invalid_id'])) {
    $statusMessage = 'Invalid education record ID.';
    $statusType = 'danger';
} elseif (isset($_GET['not_found'])) {
    $statusMessage = 'Education record not found.';
    $statusType = 'danger';
} elseif (isset($_GET['delete_failed'])) {
    $statusMessage = 'Unable to delete the education record.';
    $statusType = 'danger';
}

// Fetch education records
$educationRecords = [];
$databaseError = '';

try {
    $stmt = $pdo->query("
        SELECT
            id,
            qualification,
            institution,
            location,
            start_year,
            end_year,
            description,
            display_order
        FROM education
        ORDER BY display_order ASC, end_year DESC, id DESC
    ");

    $educationRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $databaseError = 'Unable to load education records from the database.';
}

// Current administrator information
$currentAdminName = $_SESSION['admin_name'] ?? 'Administrator';
$currentAdminRole = $_SESSION['admin_role'] ?? 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Jutsue Portfolio CMS</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            color: #172033;
        }

        .navbar {
            background: #0b1220;
            color: #ffffff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .navbar-brand {
            font-size: 20px;
            font-weight: bold;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 14px;
        }

        .role {
            background: #19d3c5;
            color: #0b1220;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
        }

        .logout {
            color: #ffffff;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.4);
            padding: 7px 12px;
            border-radius: 5px;
        }

        .logout:hover {
            background: rgba(255,255,255,0.1);
        }

        .container {
            width: 92%;
            max-width: 1200px;
            margin: 35px auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .page-header h1 {
            margin: 0 0 6px;
            font-size: 30px;
        }

        .page-header p {
            margin: 0;
            color: #667085;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-primary {
            background: #0b1220;
            color: #ffffff;
        }

        .btn-secondary {
            background: #e9edf3;
            color: #172033;
        }

        .btn-edit {
            background: #5b8cff;
            color: #ffffff;
        }

        .btn-delete {
            background: #dc3545;
            color: #ffffff;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .alert-danger {
            background: #f8d7da;
            color: #842029;
        }

        .card {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e9edf3;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            font-size: 13px;
            text-transform: uppercase;
            color: #667085;
        }

        td {
            font-size: 14px;
        }

        .qualification {
            font-weight: bold;
            color: #0b1220;
        }

        .institution {
            font-weight: 600;
        }

        .period {
            white-space: nowrap;
        }

        .description {
            max-width: 300px;
            color: #667085;
            line-height: 1.5;
        }

        .action-buttons {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            padding: 7px 11px;
            font-size: 12px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #667085;
        }

        .empty-state h2 {
            color: #172033;
            margin-bottom: 8px;
        }

        .database-error {
            background: #fff3cd;
            color: #664d03;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-info {
                flex-wrap: wrap;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .container {
                width: 95%;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="navbar-brand">
        Jutsue Portfolio CMS
    </div>

    <div class="admin-info">
        <span><?= htmlspecialchars($currentAdminName) ?></span>

        <span class="role">
            <?= htmlspecialchars($currentAdminRole) ?>
        </span>

        <a href="../dashboard.php" class="logout">Dashboard</a>

        <a href="../auth/logout.php" class="logout">Logout</a>
    </div>
</nav>

<main class="container">

    <div class="page-header">
        <div>
            <h1>Education Management</h1>
            <p>Manage academic qualifications and educational background.</p>
        </div>

        <div class="actions">
            <a href="create.php" class="btn btn-primary">
                + Add Education
            </a>

            <a href="../dashboard.php" class="btn btn-secondary">
                Dashboard
            </a>
        </div>
    </div>

    <?php if ($statusMessage): ?>
        <div class="alert alert-<?= htmlspecialchars($statusType) ?>">
            <?= htmlspecialchars($statusMessage) ?>
        </div>
    <?php endif; ?>

    <?php if ($databaseError): ?>
        <div class="database-error">
            <?= htmlspecialchars($databaseError) ?>
        </div>
    <?php endif; ?>

    <div class="card">

        <?php if (!$databaseError && empty($educationRecords)): ?>

            <div class="empty-state">
                <h2>No education records yet</h2>
                <p>
                    Add the first educational qualification to begin building
                    the education section of the portfolio.
                </p>

                <a href="create.php" class="btn btn-primary">
                    Add First Education Record
                </a>
            </div>

        <?php elseif (!$databaseError): ?>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Qualification</th>
                            <th>Institution</th>
                            <th>Location</th>
                            <th>Period</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($educationRecords as $index => $education): ?>

                        <tr>
                            <td>
                                <?= $index + 1 ?>
                            </td>

                            <td>
                                <div class="qualification">
                                    <?= htmlspecialchars($education['qualification']) ?>
                                </div>
                            </td>

                            <td>
                                <div class="institution">
                                    <?= htmlspecialchars($education['institution']) ?>
                                </div>
                            </td>

                            <td>
                                <?= htmlspecialchars($education['location'] ?? '') ?>
                            </td>

                            <td class="period">

                                <?php
                                $startYear = $education['start_year'];
                                $endYear = $education['end_year'];

                                if ($startYear && $endYear) {
                                    echo htmlspecialchars($startYear . ' – ' . $endYear);
                                } elseif ($startYear) {
                                    echo htmlspecialchars($startYear . ' – Present');
                                } elseif ($endYear) {
                                    echo htmlspecialchars($endYear);
                                } else {
                                    echo '—';
                                }
                                ?>

                            </td>

                            <td>
                                <div class="description">
                                    <?php
                                    $description = trim($education['description'] ?? '');

                                    if ($description !== '') {
                                        echo nl2br(
                                            htmlspecialchars(
                                                mb_strimwidth(
                                                    $description,
                                                    0,
                                                    180,
                                                    '...'
                                                )
                                            )
                                        );
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </div>
                            </td>

                            <td>
                                <div class="action-buttons">

                                    <a
                                        href="edit.php?id=<?= (int) $education['id'] ?>"
                                        class="btn btn-edit"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="delete.php"
                                        onsubmit="return confirm('Are you sure you want to delete this education record?');"
                                        style="display:inline;"
                                    >
                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $education['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-delete"
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

</main>

</body>
</html>