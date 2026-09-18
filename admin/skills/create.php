<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $category = trim($_POST['category'] ?? '');
    $skill_name = trim($_POST['skill_name'] ?? '');
    $proficiency = trim($_POST['proficiency'] ?? '');
    $display_order = trim($_POST['display_order'] ?? '0');

    // Validation
    if ($category === '') {
        $errors[] = 'Skill category is required.';
    }

    if ($skill_name === '') {
        $errors[] = 'Skill name is required.';
    }

    if ($proficiency !== '') {

        if (
            !ctype_digit($proficiency) ||
            (int) $proficiency < 0 ||
            (int) $proficiency > 100
        ) {
            $errors[] = 'Proficiency must be a number between 0 and 100.';
        }

    }

    if (
        $display_order === '' ||
        !ctype_digit($display_order)
    ) {
        $errors[] = 'Display order must be a whole number.';
    }

    // Insert skill if there are no errors
    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO skills (
                category,
                skill_name,
                proficiency,
                display_order
            )
            VALUES (?, ?, ?, ?)
        ");

        $success = $stmt->execute([
            $category,
            $skill_name,
            $proficiency !== '' ? (int) $proficiency : null,
            (int) $display_order
        ]);

        if ($success) {

            header('Location: index.php?created=1');
            exit;

        } else {

            $errors[] = 'Skill could not be created. Please try again.';

        }
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

    <title>Add Skill | Admin Dashboard</title>

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

            <h1 class="h3 mb-1">
                Add Skill
            </h1>

            <p class="text-muted mb-0">
                Add a technical or professional skill to the portfolio.
            </p>

        </div>

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            Back to Skills
        </a>

    </div>


    <!-- Validation Errors -->

    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

            <strong>
                Please correct the following:
            </strong>

            <ul class="mb-0 mt-2">

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= htmlspecialchars($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <!-- Form -->

    <div class="card shadow-sm">

        <div class="card-body p-4">

            <form method="POST">

                <!-- Category -->

                <div class="mb-3">

                    <label
                        for="category"
                        class="form-label"
                    >
                        Skill Category
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="category"
                        name="category"
                        value="<?= htmlspecialchars(
                            $_POST['category'] ?? ''
                        ) ?>"
                        placeholder="e.g. Programming Languages"
                    >

                    <div class="form-text">
                        Example: Programming Languages, Frameworks,
                        Databases, Networking, Tools.
                    </div>

                </div>


                <!-- Skill Name -->

                <div class="mb-3">

                    <label
                        for="skill_name"
                        class="form-label"
                    >
                        Skill Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="skill_name"
                        name="skill_name"
                        value="<?= htmlspecialchars(
                            $_POST['skill_name'] ?? ''
                        ) ?>"
                        placeholder="e.g. PHP"
                    >

                </div>


                <!-- Proficiency -->

                <div class="mb-3">

                    <label
                        for="proficiency"
                        class="form-label"
                    >
                        Proficiency
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="proficiency"
                        name="proficiency"
                        value="<?= htmlspecialchars(
                            $_POST['proficiency'] ?? ''
                        ) ?>"
                        min="0"
                        max="100"
                        placeholder="e.g. 85"
                    >

                    <div class="form-text">
                        Enter a percentage between 0 and 100.
                    </div>

                </div>


                <!-- Display Order -->

                <div class="mb-4">

                    <label
                        for="display_order"
                        class="form-label"
                    >
                        Display Order
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="display_order"
                        name="display_order"
                        value="<?= htmlspecialchars(
                            $_POST['display_order'] ?? '0'
                        ) ?>"
                        min="0"
                    >

                    <div class="form-text">
                        Lower numbers appear first.
                    </div>

                </div>


                <!-- Buttons -->

                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Skill
                    </button>

                    <a
                        href="index.php"
                        class="btn btn-outline-secondary"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>

</html>