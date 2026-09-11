```php
<?php

/**
 * Administrator Management
 * Jutsue Mekodjio Bilios Portfolio CMS
 *
 * Super Administrator only.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/permissions.php';

requireSuperAdmin();

/*
|--------------------------------------------------------------------------
| Load Administrators
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->query("
        SELECT
            id,
            name,
            email,
            role,
            is_active,
            last_login,
            created_at
        FROM admin_users
        ORDER BY id ASC
    ");

    $administrators = $stmt->fetchAll();

} catch (PDOException $e) {

    error_log(
        'Administrator management error: ' . $e->getMessage()
    );

    $administrators = [];

    $databaseError = 'Unable to load administrator accounts.';
}

/*
|--------------------------------------------------------------------------
| Status Messages
|--------------------------------------------------------------------------
*/

$statusMessage = '';
$statusType = 'success';

if (isset($_GET['created'])) {

    $statusMessage = 'Administrator account created successfully.';

} elseif (isset($_GET['updated'])) {

    $statusMessage = 'Administrator account updated successfully.';

} elseif (isset($_GET['deleted'])) {

    $statusMessage = 'Administrator account deleted successfully.';

} elseif (isset($_GET['status_updated'])) {

    if ((int) $_GET['status_updated'] === 1) {

        $statusMessage = 'Administrator account activated successfully.';

    } else {

        $statusMessage = 'Administrator account deactivated successfully.';
    }

} elseif (isset($_GET['error'])) {

    $statusType = 'danger';

    switch ($_GET['error']) {

        case 'self_status':
            $statusMessage =
                'You cannot deactivate your own administrator account.';
            break;

        case 'status_update':
            $statusMessage =
                'Unable to update the administrator status.';
            break;

        case 'self_delete':
            $statusMessage =
                'You cannot delete your own administrator account.';
            break;

        case 'delete_failed':
            $statusMessage =
                'Unable to delete the administrator account.';
            break;

        case 'invalid_id':
            $statusMessage =
                'Invalid administrator account selected.';
            break;

        case 'not_found':
            $statusMessage =
                'Administrator account not found.';
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
        Administrator Management | Portfolio CMS
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
            href="../dashboard/index.php"
            class="btn btn-outline-light btn-sm"
        >
            Back to Dashboard
        </a>

    </div>

</nav>

<main class="container py-5">

    <!-- Page Header -->

    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4"
    >

        <div>

            <h1 class="h3 fw-bold mb-1">
                Administrator Management
            </h1>

            <p class="text-muted mb-0">
                Manage authorized administrators and their roles.
            </p>

        </div>

        <a
            href="create.php"
            class="btn btn-dark"
        >
            + Add Administrator
        </a>

    </div>

    <!-- Database Error -->

    <?php if (isset($databaseError)): ?>

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
            class="alert alert-<?= $statusType ?> alert-dismissible fade show"
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

    <!-- Administrator Table -->

    <div class="card shadow-sm border-0">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-dark">

                        <tr>

                            <th>#</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Role</th>

                            <th>Status</th>

                            <th>Last Login</th>

                            <th class="text-center">
                                Actions
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (!empty($administrators)): ?>

                        <?php foreach ($administrators as $administrator): ?>

                            <?php

                            /*
                            |--------------------------------------------------------------------------
                            | Determine Current Logged-In Administrator
                            |--------------------------------------------------------------------------
                            */

                            $isCurrentAdmin =
                                (int) $administrator['id'] === currentAdminId();

                            ?>

                            <tr>

                                <!-- ID -->

                                <td>
                                    <?= (int) $administrator['id'] ?>
                                </td>

                                <!-- Name -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $administrator['name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </strong>

                                </td>

                                <!-- Email -->

                                <td>

                                    <?= htmlspecialchars(
                                        $administrator['email'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>

                                <!-- Role -->

                                <td>

                                    <?php if ($administrator['role'] === 'super_admin'): ?>

                                        <span class="badge bg-danger">
                                            Super Admin
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-primary">
                                            Admin
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- Status -->

                                <td>

                                    <?php if ((int) $administrator['is_active'] === 1): ?>

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- Last Login -->

                                <td>

                                    <?php if ($administrator['last_login']): ?>

                                        <?= htmlspecialchars(
                                            $administrator['last_login'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Never
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <!-- Actions -->

                                <td class="text-center">

                                    <div
                                        class="d-flex justify-content-center gap-2 flex-wrap"
                                    >

                                        <!-- Edit -->

                                        <a
                                            href="edit.php?id=<?= (int) $administrator['id'] ?>"
                                            class="btn btn-sm btn-outline-dark"
                                        >
                                            Edit
                                        </a>

                                        <?php if (!$isCurrentAdmin): ?>

                                            <!-- Activate / Deactivate -->

                                            <?php if ((int) $administrator['is_active'] === 1): ?>

                                                <a
                                                    href="toggle-status.php?id=<?= (int) $administrator['id'] ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to deactivate this administrator?');"
                                                >
                                                    Deactivate
                                                </a>

                                            <?php else: ?>

                                                <a
                                                    href="toggle-status.php?id=<?= (int) $administrator['id'] ?>"
                                                    class="btn btn-sm btn-outline-success"
                                                    onclick="return confirm('Are you sure you want to activate this administrator?');"
                                                >
                                                    Activate
                                                </a>

                                            <?php endif; ?>

                                            <!-- Delete -->

                                            <form
                                                action="delete.php"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to permanently delete this administrator? This action cannot be undone.');"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int) $administrator['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                >
                                                    Delete
                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <!-- Current Administrator -->

                                            <span
                                                class="badge bg-light text-dark border d-flex align-items-center"
                                            >
                                                Current Account
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center text-muted py-4"
                            >
                                No administrator accounts found.
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
```
