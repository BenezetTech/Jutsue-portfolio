<?php

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

$pageTitle = 'Add Education';

$errors = [];

$qualification = '';
$institution = '';
$location = '';
$startYear = '';
$endYear = '';
$description = '';
$displayOrder = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $qualification = trim($_POST['qualification'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $startYear = trim($_POST['start_year'] ?? '');
    $endYear = trim($_POST['end_year'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $displayOrder = (int) ($_POST['display_order'] ?? 0);

    // Validate required fields
    if ($qualification === '') {
        $errors[] = 'Qualification is required.';
    }

    if ($institution === '') {
        $errors[] = 'Institution is required.';
    }

    // Validate start year
    if ($startYear !== '' && !preg_match('/^\d{4}$/', $startYear)) {
        $errors[] = 'Start year must be a valid four-digit year.';
    }

    // Validate end year
    if ($endYear !== '' && !preg_match('/^\d{4}$/', $endYear)) {
        $errors[] = 'End year must be a valid four-digit year.';
    }

    // Make sure start year is not greater than end year
    if (
        $startYear !== '' &&
        $endYear !== '' &&
        (int) $startYear > (int) $endYear
    ) {
        $errors[] = 'Start year cannot be later than end year.';
    }

    // Save education record
    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO education (
                    qualification,
                    institution,
                    location,
                    start_year,
                    end_year,
                    description,
                    display_order
                )
                VALUES (
                    :qualification,
                    :institution,
                    :location,
                    :start_year,
                    :end_year,
                    :description,
                    :display_order
                )
            ");

            $stmt->execute([
                ':qualification' => $qualification,
                ':institution' => $institution,
                ':location' => $location !== '' ? $location : null,
                ':start_year' => $startYear !== '' ? $startYear : null,
                ':end_year' => $endYear !== '' ? $endYear : null,
                ':description' => $description !== '' ? $description : null,
                ':display_order' => $displayOrder
            ]);

            header('Location: index.php?created=1');
            exit;

        } catch (PDOException $e) {

            $errors[] = 'Unable to save the education record. Please try again.';
        }
    }
}

$currentAdminName = $_SESSION['admin_name'] ?? 'Administrator';
$currentAdminRole = $_SESSION['admin_role'] ?? 'admin';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($pageTitle) ?> | Jutsue Portfolio CMS
    </title>

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
            border: 1px solid rgba(255,255,255,0.4);
            padding: 7px 12px;
            border-radius: 5px;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
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
            font-size: 14px;
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
            display: block;
            margin-top: 5px;
            color: #667085;
            font-size: 12px;
        }

        .year-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: #f8d7da;
            color: #842029;
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            border: none;
            padding: 11px 18px;
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

        .btn-primary:hover {
            opacity: 0.9;
        }

        .btn-secondary:hover {
            background: #dfe4eb;
        }

        @media (max-width: 768px) {

            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-info {
                flex-wrap: wrap;
            }

            .container {
                width: 95%;
            }

            .card {
                padding: 20px;
            }

            .year-row {
                grid-template-columns: 1fr;
                gap: 0;
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
            <?= htmlspecialchars($currentAdminName) ?>
        </span>

        <span class="role">
            <?= htmlspecialchars($currentAdminRole) ?>
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

        <h1>Add Education</h1>

        <p>
            Add an academic qualification or educational background record.
        </p>

    </div>

    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

            <ul>

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= htmlspecialchars($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>

    <div class="card">

        <form method="POST" action="">

            <div class="form-group">

                <label for="qualification">
                    Qualification <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="qualification"
                    name="qualification"
                    value="<?= htmlspecialchars($qualification) ?>"
                    placeholder="e.g. MSc Health Care Data Analytics"
                    required
                >

            </div>

            <div class="form-group">

                <label for="institution">
                    Institution <span class="required">*</span>
                </label>

                <input
                    type="text"
                    id="institution"
                    name="institution"
                    value="<?= htmlspecialchars($institution) ?>"
                    placeholder="e.g. FINISTECH"
                    required
                >

            </div>

            <div class="form-group">

                <label for="location">
                    Location
                </label>

                <input
                    type="text"
                    id="location"
                    name="location"
                    value="<?= htmlspecialchars($location) ?>"
                    placeholder="e.g. Yaoundé, Cameroon"
                >

            </div>

            <div class="year-row">

                <div class="form-group">

                    <label for="start_year">
                        Start Year
                    </label>

                    <input
                        type="text"
                        id="start_year"
                        name="start_year"
                        value="<?= htmlspecialchars($startYear) ?>"
                        placeholder="e.g. 2023"
                        maxlength="4"
                    >

                </div>

                <div class="form-group">

                    <label for="end_year">
                        End Year
                    </label>

                    <input
                        type="text"
                        id="end_year"
                        name="end_year"
                        value="<?= htmlspecialchars($endYear) ?>"
                        placeholder="e.g. 2025"
                        maxlength="4"
                    >

                </div>

            </div>

            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    placeholder="Briefly describe the qualification, specialization, project, or other relevant information."
                ><?= htmlspecialchars($description) ?></textarea>

            </div>

            <div class="form-group">

                <label for="display_order">
                    Display Order
                </label>

                <input
                    type="number"
                    id="display_order"
                    name="display_order"
                    value="<?= htmlspecialchars((string) $displayOrder) ?>"
                    min="0"
                >

                <span class="help-text">
                    Lower numbers appear first.
                </span>

            </div>

            <div class="form-actions">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Education
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