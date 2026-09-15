<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

$adminName = $_SESSION['admin_name'] ?? 'Administrator';
$adminRole = $_SESSION['admin_role'] ?? 'admin';

$errors = [];

// Get award ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php?invalid_id=1');
    exit;
}

// Fetch award
try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            title,
            organization,
            year,
            description,
            display_order
        FROM awards
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $award = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die('Unable to load the award record.');

}

if (!$award) {
    header('Location: index.php?not_found=1');
    exit;
}

// Set current values
$title = $award['title'];
$organization = $award['organization'];
$year = $award['year'];
$description = $award['description'];
$displayOrder = $award['display_order'];

// Process update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $displayOrder = (int) ($_POST['display_order'] ?? 0);

    // Validation

    if ($title === '') {
        $errors[] = 'Award title is required.';
    }

    if ($organization === '') {
        $errors[] = 'Organization is required.';
    }

    if ($year !== '' && !preg_match('/^\d{4}$/', $year)) {
        $errors[] = 'Year must be a valid 4-digit year.';
    }

    // Update database

    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare("
                UPDATE awards
                SET
                    title = ?,
                    organization = ?,
                    year = ?,
                    description = ?,
                    display_order = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");

            $stmt->execute([
                $title,
                $organization,
                $year !== '' ? $year : null,
                $description,
                $displayOrder,
                $id
            ]);

            header('Location: index.php?updated=1');
            exit;

        } catch (PDOException $e) {

            $errors[] = 'Unable to update the award record.';

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

    <title>Edit Award | Jutsue Portfolio CMS</title>

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

        .nav-link {
            color: #ffffff;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 7px 12px;
            border-radius: 5px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .container {
            width: 92%;
            max-width: 900px;
            margin: 35px auto;
        }

        .page-header {
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

        .card {
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        .required {
            color: #dc3545;
        }

        input,
        textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d0d5dd;
            border-radius: 6px;
            font-size: 15px;
            font-family: inherit;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #5b8cff;
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        .help-text {
            margin-top: 6px;
            color: #667085;
            font-size: 13px;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .errors {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #9f1239;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .errors ul {
            margin: 0;
            padding-left: 20px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 11px 18px;
            border-radius: 6px;
            text-decoration: none;
            border: none;
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

        .btn-primary:hover {
            background: #111c2e;
        }

        @media (max-width: 700px) {

            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-info {
                flex-wrap: wrap;
            }

            .row {
                grid-template-columns: 1fr;
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

        <span>
            <?= htmlspecialchars($adminName) ?>
        </span>

        <span class="role">
            <?= htmlspecialchars($adminRole) ?>
        </span>

        <a href="../dashboard.php" class="nav-link">
            Dashboard
        </a>

        <a href="../auth/logout.php" class="nav-link">
            Logout
        </a>

    </div>

</nav>

<main class="container">

    <div class="page-header">

        <h1>Edit Award & Recognition</h1>

        <p>
            Update the award or recognition information.
        </p>

    </div>

    <div class="card">

        <?php if (!empty($errors)): ?>

            <div class="errors">

                <ul>

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?= htmlspecialchars($error) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label for="title">

                    Award / Recognition Title

                    <span class="required">*</span>

                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?= htmlspecialchars($title) ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label for="organization">

                    Organization

                    <span class="required">*</span>

                </label>

                <input
                    type="text"
                    id="organization"
                    name="organization"
                    value="<?= htmlspecialchars($organization) ?>"
                    required
                >

            </div>

            <div class="row">

                <div class="form-group">

                    <label for="year">
                        Year
                    </label>

                    <input
                        type="text"
                        id="year"
                        name="year"
                        value="<?= htmlspecialchars($year ?? '') ?>"
                        maxlength="4"
                    >

                </div>

                <div class="form-group">

                    <label for="display_order">
                        Display Order
                    </label>

                    <input
                        type="number"
                        id="display_order"
                        name="display_order"
                        value="<?= htmlspecialchars($displayOrder) ?>"
                        min="0"
                    >

                    <div class="help-text">
                        Lower numbers appear first.
                    </div>

                </div>

            </div>

            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                ><?= htmlspecialchars($description ?? '') ?></textarea>

            </div>

            <div class="buttons">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Update Award
                </button>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</main>

</body>

</html>