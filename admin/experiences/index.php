<?php

/**
 * Experience Management
 * Jutsue Mekodjio Bilios Portfolio CMS
 *
 * Authenticated administrators can view experiences.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/auth.php';

requireLogin();

/*
|--------------------------------------------------------------------------
| Load Experiences
|--------------------------------------------------------------------------
*/

$experiences = [];
$databaseError = '';

try {

    $stmt = $pdo->query("
        SELECT
            id,
            job_title,
            organization,
            location,
            start_date,
            end_date,
            is_current,
            description,
            display_order,
            created_at,
            updated_at
        FROM experiences
        ORDER BY
            display_order ASC,
            start_date DESC,
            id ASC
    ");

    $experiences = $stmt->fetchAll();

} catch (PDOException $e) {

    error_log(
        'Experience management error: ' . $e->getMessage()
    );

    $databaseError =
        'Unable to load professional experience records.';
}

/*
|--------------------------------------------------------------------------
| Current Administrator
|--------------------------------------------------------------------------
*/

$adminName = $_SESSION['admin_name'] ?? 'Administrator';

/*
|--------------------------------------------------------------------------
| Status Messages
|--------------------------------------------------------------------------
*/

$statusMessage = '';
$statusType = 'success';

if (isset($_GET['created'])) {

    $statusMessage =
        'Professional experience created successfully.';

} elseif (isset($_GET['updated'])) {

    $statusMessage =
        'Professional experience updated successfully.';

} elseif (isset($_GET['deleted'])) {

    $statusMessage =
        'Professional experience deleted successfully.';

} elseif (isset($_GET['error'])) {

    $statusType = 'danger';

    switch ($_GET['error']) {

        case 'invalid_id':
            $statusMessage =
                'Invalid experience record selected.';
            break;

        case 'not_found':
            $statusMessage =
                'The selected experience record was not found.';
            break;

        case 'delete_failed':
            $statusMessage =
                'Unable to delete the experience record.';
            break;

        default:
            $statusMessage =
                'An unexpected error occurred.';
            break;
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
        Experience Management | Portfolio CMS
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<!-- ==============================================================
     NAVIGATION
     ============================================================== -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <span class="navbar-brand fw-bold">
            Jutsue Portfolio CMS
        </span>

        <div class="d-flex align-items-center gap-2">

            <span class="text-white small">
                <?= htmlspecialchars(
                    $adminName,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>

            <a
                href="../auth/logout.php"
                class="btn btn-outline-light btn-sm"
            >
                Logout
            </a>

        </div>

    </div>

</nav>

<!-- ==============================================================
     MAIN CONTENT
     ============================================================== -->

<main class="container py-5">

    <!-- Page Header -->

    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4"
    >

        <div>

            <h1 class="h3 fw-bold mb-1">
                Experience Management
            </h1>

            <p class="text-muted mb-0">
                Manage professional experience displayed on the portfolio.
            </p>

        </div>

        <div class="d-flex gap-2 flex-wrap">

            <a
                href="create.php"
                class="btn btn-dark"
            >
                + Add Experience
            </a>

            <a
                href="../dashboard/index.php"
                class="btn btn-outline-dark"
            >
                Dashboard
            </a>

        </div>

    </div>

    <!-- Database Error -->

    <?php if ($databaseError): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars(
                $databaseError,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

        </div>

    <?php endif; ?>

    <!-- Status Message -->

    <?php if ($statusMessage): ?>

        <div
            class="alert alert-<?= htmlspecialchars(
                $statusType,
                ENT_QUOTES,
                'UTF-8'
            ) ?> alert-dismissible fade show"
            role="alert"
        >

            <?= htmlspecialchars(
                $statusMessage,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>

    <?php endif; ?>

    <!-- Experience Records -->

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-dark">

                        <tr>

                            <th>#</th>

                            <th>Position</th>

                            <th>Organization</th>

                            <th>Location</th>

                            <th>Period</th>

                            <th>Status</th>

                            <th class="text-center">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (!empty($experiences)): ?>

                        <?php foreach ($experiences as $experience): ?>

                            <tr>

                                <!-- ID -->

                                <td>
                                    <?= (int) $experience['id'] ?>
                                </td>

                                <!-- Job Title -->

                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $experience['job_title'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </strong>

                                </td>

                                <!-- Organization -->

                                <td>
                                    <?= htmlspecialchars(
                                        $experience['organization'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <!-- Location -->

                                <td>
                                    <?= htmlspecialchars(
                                        $experience['location']
                                            ?: 'Not provided',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <!-- Period -->

                                <td>

                                    <?php

                                    $startDate = !empty(
                                        $experience['start_date']
                                    )
                                        ? date(
                                            'M Y',
                                            strtotime(
                                                $experience['start_date']
                                            )
                                        )
                                        : 'Unknown';

                                    $endDate = !empty(
                                        $experience['end_date']
                                    )
                                        ? date(
                                            'M Y',
                                            strtotime(
                                                $experience['end_date']
                                            )
                                        )
                                        : null;

                                    ?>

                                    <?= htmlspecialchars(
                                        $startDate,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                    –

                                    <?php if (
                                        (int) $experience['is_current'] === 1
                                    ): ?>

                                        Present

                                    <?php elseif ($endDate): ?>

                                        <?= htmlspecialchars(
                                            $endDate,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    <?php else: ?>

                                        Unknown

                                    <?php endif; ?>

                                </td>

                                <!-- Status -->

                                <td>

                                    <?php if (
                                        (int) $experience['is_current'] === 1
                                    ): ?>

                                        <span class="badge bg-success">
                                            Current
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Completed
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- Actions -->

                                <td class="text-center">

                                    <div
                                        class="d-flex justify-content-center gap-2 flex-wrap"
                                    >

                                        <a
                                            href="edit.php?id=<?= (int) $experience['id'] ?>"
                                            class="btn btn-sm btn-outline-dark"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="delete.php"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to permanently delete this experience record? This action cannot be undone.');"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $experience['id'] ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                            >
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <h2 class="h5">
                                        No experience records yet
                                    </h2>

                                    <p class="mb-3">
                                        Add the first professional experience
                                        record to begin building the portfolio.
                                    </p>

                                    <a
                                        href="create.php"
                                        class="btn btn-dark"
                                    >
                                        + Add Experience
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</main>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>