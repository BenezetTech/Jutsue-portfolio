<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php?invalid_id=1');
    exit;
}

// Fetch certification
$stmt = $pdo->prepare("
    SELECT id, title, organization, year, description, certificate_file
    FROM certifications
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);
$certification = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$certification) {
    header('Location: index.php?not_found=1');
    exit;
}

$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $certificate_file = trim($_POST['certificate_file'] ?? '');

    // Validation
    if ($title === '') {
        $errors[] = 'Certification title is required.';
    }

    if ($organization === '') {
        $errors[] = 'Issuing organization is required.';
    }

    if ($year === '') {
        $errors[] = 'Year is required.';
    } elseif (!preg_match('/^\d{4}$/', $year)) {
        $errors[] = 'Year must contain exactly 4 digits.';
    }

    // If there are no errors, update the certification
    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE certifications
            SET
                title = ?,
                organization = ?,
                year = ?,
                description = ?,
                certificate_file = ?
            WHERE id = ?
        ");

        $success = $stmt->execute([
            $title,
            $organization,
            $year,
            $description !== '' ? $description : null,
            $certificate_file !== '' ? $certificate_file : null,
            $id
        ]);

        if ($success) {
            header('Location: index.php?updated=1');
            exit;
        } else {
            $errors[] = 'Certification could not be updated. Please try again.';
        }
    }

} else {

    // Load existing values into the form
    $title = $certification['title'];
    $organization = $certification['organization'];
    $year = $certification['year'];
    $description = $certification['description'];
    $certificate_file = $certification['certificate_file'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Certification | Admin Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Edit Certification</h1>
            <p class="text-muted mb-0">
                Update this certification or professional certificate.
            </p>
        </div>

        <a href="index.php" class="btn btn-secondary">
            Back to Certifications
        </a>

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


    <div class="card shadow-sm">

        <div class="card-body p-4">

            <form method="POST">

                <!-- Certification Title -->
                <div class="mb-3">

                    <label for="title" class="form-label">
                        Certification Title
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($title ?? '') ?>"
                    >

                </div>


                <!-- Issuing Organization -->
                <div class="mb-3">

                    <label for="organization" class="form-label">
                        Issuing Organization
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="organization"
                        name="organization"
                        value="<?= htmlspecialchars($organization ?? '') ?>"
                    >

                </div>


                <!-- Year -->
                <div class="mb-3">

                    <label for="year" class="form-label">
                        Year
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="year"
                        name="year"
                        value="<?= htmlspecialchars($year ?? '') ?>"
                        placeholder="e.g. 2025"
                    >

                    <div class="form-text">
                        Enter the year using exactly four digits.
                    </div>

                </div>


                <!-- Description -->
                <div class="mb-3">

                    <label for="description" class="form-label">
                        Description
                    </label>

                    <textarea
                        class="form-control"
                        id="description"
                        name="description"
                        rows="5"
                    ><?= htmlspecialchars($description ?? '') ?></textarea>

                </div>


                <!-- Certificate File -->
                <div class="mb-4">

                    <label for="certificate_file" class="form-label">
                        Certificate File
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="certificate_file"
                        name="certificate_file"
                        value="<?= htmlspecialchars($certificate_file ?? '') ?>"
                        placeholder="e.g. assets/uploads/certificate.pdf"
                    >

                    <div class="form-text">
                        For now, enter the relative file path if a certificate
                        file exists. Secure file upload functionality will be
                        added later.
                    </div>

                </div>


                <!-- Buttons -->
                <div class="d-flex gap-2">

                    <button type="submit" class="btn btn-primary">
                        Update Certification
                    </button>

                    <a href="index.php" class="btn btn-outline-secondary">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>