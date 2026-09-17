<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

$adminName = $_SESSION['admin_name'] ?? 'Administrator';
$adminRole = $_SESSION['admin_role'] ?? 'admin';

$title = '';
$organization = '';
$year = '';
$description = '';
$certificateFile = '';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $certificateFile = trim($_POST['certificate_file'] ?? '');

    // Validate title
    if ($title === '') {
        $errors[] = 'Certification title is required.';
    }

    // Validate organization
    if ($organization === '') {
        $errors[] = 'Issuing organization is required.';
    }

    // Validate year
    if ($year === '') {
        $errors[] = 'Year is required.';
    } elseif (!preg_match('/^\d{4}$/', $year)) {
        $errors[] = 'Year must contain exactly 4 digits.';
    }

    // Insert certification if there are no errors
    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO certifications
                (
                    title,
                    organization,
                    year,
                    description,
                    certificate_file
                )
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $title,
                $organization,
                $year,
                $description !== '' ? $description : null,
                $certificateFile !== '' ? $certificateFile : null
            ]);

            header('Location: index.php?created=1');
            exit;

        } catch (PDOException $e) {

            $errors[] = 'Unable to create the certification. Please try again.';
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

    <title>Add Certification | Jutsue Portfolio Admin</title>

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
            max-width: 900px;
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

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .form-label {
            font-weight: 600;
        }

        .required {
            color: #dc3545;
        }

        .btn-save {
            background: #19d3c5;
            color: #0b1220;
            border: none;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
        }

        .btn-save:hover {
            background: #14b8aa;
            color: #0b1220;
        }

        .help-text {
            font-size: 13px;
            color: #667085;
            margin-top: 5px;
        }

    </style>

</head>

<body>

<div class="page-wrapper">

    <div class="page-header">

        <h1>Add Certification</h1>

        <p>
            Add a professional certification or certificate to the portfolio.
        </p>

    </div>

    <div class="mb-3">

        <strong>Logged in as:</strong>
        <?= htmlspecialchars($adminName) ?>

        &nbsp; | &nbsp;

        <strong>Role:</strong>
        <?= htmlspecialchars($adminRole) ?>

    </div>

    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

            <strong>Please correct the following:</strong>

            <ul class="mb-0 mt-2">

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= htmlspecialchars($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>

    <div class="card">

        <div class="card-body p-4">

            <form method="POST">

                <div class="mb-3">

                    <label
                        for="title"
                        class="form-label"
                    >
                        Certification Title
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($title) ?>"
                        placeholder="e.g. Professional Diploma in Digital Marketing"
                    >

                </div>

                <div class="mb-3">

                    <label
                        for="organization"
                        class="form-label"
                    >
                        Issuing Organization
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="organization"
                        name="organization"
                        value="<?= htmlspecialchars($organization) ?>"
                        placeholder="e.g. FINISTECH"
                    >

                </div>

                <div class="mb-3">

                    <label
                        for="year"
                        class="form-label"
                    >
                        Year
                        <span class="required">*</span>
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="year"
                        name="year"
                        value="<?= htmlspecialchars($year) ?>"
                        min="1900"
                        max="2100"
                        placeholder="e.g. 2025"
                    >

                    <div class="help-text">
                        Enter the year the certification was obtained.
                    </div>

                </div>

                <div class="mb-3">

                    <label
                        for="description"
                        class="form-label"
                    >
                        Description
                    </label>

                    <textarea
                        class="form-control"
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="Briefly describe the certification..."
                    ><?= htmlspecialchars($description) ?></textarea>

                </div>

                <div class="mb-4">

                    <label
                        for="certificate_file"
                        class="form-label"
                    >
                        Certificate File
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="certificate_file"
                        name="certificate_file"
                        value="<?= htmlspecialchars($certificateFile) ?>"
                        placeholder="e.g. assets/uploads/certificates/certificate.pdf"
                    >

                    <div class="help-text">
                        For now, enter the relative path to the certificate file.
                        We will build secure certificate uploads later.
                    </div>

                </div>

                <div class="d-flex gap-2">

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn-save"
                    >
                        Save Certification
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</body>

</html>