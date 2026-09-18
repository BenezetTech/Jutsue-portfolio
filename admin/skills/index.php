<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

// Fetch all skills
$stmt = $pdo->query("
    SELECT id, category, skill_name, proficiency, display_order
    FROM skills
    ORDER BY category ASC, display_order ASC, skill_name ASC
");

$skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Skills Management | Admin Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Skills Management</h1>

            <p class="text-muted mb-0">
                Manage technical, professional, and other skills displayed
                on the portfolio.
            </p>
        </div>

        <a href="create.php" class="btn btn-primary">
            Add Skill
        </a>

    </div>


    <!-- Success Messages -->

    <?php if (isset($_GET['created'])): ?>

        <div class="alert alert-success">
            Skill created successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET['updated'])): ?>

        <div class="alert alert-success">
            Skill updated successfully.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET['deleted'])): ?>

        <div class="alert alert-success">
            Skill deleted successfully.
        </div>

    <?php endif; ?>


    <!-- Error Messages -->

    <?php if (isset($_GET['invalid_id'])): ?>

        <div class="alert alert-danger">
            Invalid skill ID.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET['not_found'])): ?>

        <div class="alert alert-danger">
            Skill not found.
        </div>

    <?php endif; ?>


    <?php if (isset($_GET['delete_failed'])): ?>

        <div class="alert alert-danger">
            Skill could not be deleted. Please try again.
        </div>

    <?php endif; ?>


    <!-- Skills List -->

    <?php if (empty($skills)): ?>

        <div class="card shadow-sm">

            <div class="card-body text-center py-5">

                <h2 class="h5 mb-2">
                    No Skills Found
                </h2>

                <p class="text-muted mb-4">
                    You have not added any skills yet.
                </p>

                <a href="create.php" class="btn btn-primary">
                    Add Your First Skill
                </a>

            </div>

        </div>

    <?php else: ?>

        <div class="card shadow-sm">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-dark">

                            <tr>

                                <th scope="col">
                                    #
                                </th>

                                <th scope="col">
                                    Category
                                </th>

                                <th scope="col">
                                    Skill
                                </th>

                                <th scope="col">
                                    Proficiency
                                </th>

                                <th scope="col">
                                    Order
                                </th>

                                <th scope="col" class="text-end">
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($skills as $skill): ?>

                            <tr>

                                <td>
                                    <?= (int) $skill['id'] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($skill['category']) ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= htmlspecialchars($skill['skill_name']) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php if (!empty($skill['proficiency'])): ?>

                                        <?= htmlspecialchars($skill['proficiency']) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Not specified
                                        </span>

                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= (int) $skill['display_order'] ?>
                                </td>

                                <td class="text-end">

                                    <a
                                        href="edit.php?id=<?= (int) $skill['id'] ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="delete.php"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this skill?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $skill['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    <?php endif; ?>

</div>

</body>
</html>