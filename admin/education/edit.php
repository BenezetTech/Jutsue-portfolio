<?php
require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

$adminName = $_SESSION['admin_name'] ?? 'Administrator';
$adminRole = $_SESSION['admin_role'] ?? 'admin';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php?error=invalid');
    exit;
}

// Get the education record
$stmt = $pdo->prepare("SELECT * FROM education WHERE id = ?");
$stmt->execute([$id]);
$education = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$education) {
    header('Location: index.php?error=not_found');
    exit;
}

// Form variables
$qualification = $education['qualification'];
$institution = $education['institution'];
$location = $education['location'];
$start_year = $education['start_year'];
$end_year = $education['end_year'];
$description = $education['description'];
$display_order = $education['display_order'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $qualification = trim($_POST['qualification'] ?? '');
    $institution = trim($_POST['institution'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $start_year = trim($_POST['start_year'] ?? '');
    $end_year = trim($_POST['end_year'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $display_order = (int) ($_POST['display_order'] ?? 0);

    // Validation
    if ($qualification === '') {
        $errors[] = 'Qualification is required.';
    }

    if ($institution === '') {
        $errors[] = 'Institution is required.';
    }

    if ($start_year !== '' && !preg_match('/^\d{4}$/', $start_year)) {
        $errors[] = 'Start year must be a valid 4-digit year.';
    }

    if ($end_year !== '' && !preg_match('/^\d{4}$/', $end_year)) {
        $errors[] = 'End year must be a valid 4-digit year.';
    }

    if (
        $start_year !== '' &&
        $end_year !== '' &&
        (int) $start_year > (int) $end_year
    ) {
        $errors[] = 'Start year cannot be greater than end year.';
    }

    // Update record
    if (empty($errors)) {

        try {
            $stmt = $pdo->prepare("
                UPDATE education
                SET
                    qualification = ?,
                    institution = ?,
                    location = ?,
                    start_year = ?,
                    end_year = ?,
                    description = ?,
                    display_order = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $qualification,
                $institution,
                $location,
                $start_year !== '' ? $start_year : null,
                $end_year !== '' ? $end_year : null,
                $description,
                $display_order,
                $id
            ]);

            header('Location: index.php?updated=1');
            exit;

        } catch (PDOException $e) {
            $errors[] = 'Unable to update the education record.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Education | Admin</title>

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

        .header {
            background: #0b1220;
            color: white;
            padding: 18px 30px;
        }

        .header h1 {
            margin: 0 0 5px;
            font-size: 24px;
        }

        .header p {
            margin: 0;
            color: #cbd5e1;
            font-size: 14px;
        }

        .container {
            max-width: 900px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.08);
        }

        .card h2 {
            margin-top: 0;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d0d5dd;
            border-radius: 6px;
            font-size: 15px;
        }

        textarea {
            min-height: 130px;
            resize: vertical;
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
        }

        .btn-primary {
            background: #0b1220;
            color: white;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #172033;
        }

        @media (max-width: 700px) {
            .row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>Edit Education</h1>
    <p>
        Logged in as <?php echo htmlspecialchars($adminName); ?>
        (<?php echo htmlspecialchars($adminRole); ?>)
    </p>
</div>

<div class="container">

    <div class="card">

        <h2>Edit Educational Qualification</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label for="qualification">Qualification *</label>

                <input
                    type="text"
                    id="qualification"
                    name="qualification"
                    value="<?php echo htmlspecialchars($qualification); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="institution">Institution *</label>

                <input
                    type="text"
                    id="institution"
                    name="institution"
                    value="<?php echo htmlspecialchars($institution); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="location">Location</label>

                <input
                    type="text"
                    id="location"
                    name="location"
                    value="<?php echo htmlspecialchars($location ?? ''); ?>"
                    placeholder="e.g. Yaoundé, Cameroon"
                >
            </div>

            <div class="row">

                <div class="form-group">
                    <label for="start_year">Start Year</label>

                    <input
                        type="text"
                        id="start_year"
                        name="start_year"
                        value="<?php echo htmlspecialchars($start_year ?? ''); ?>"
                        placeholder="e.g. 2023"
                        maxlength="4"
                    >
                </div>

                <div class="form-group">
                    <label for="end_year">End Year</label>

                    <input
                        type="text"
                        id="end_year"
                        name="end_year"
                        value="<?php echo htmlspecialchars($end_year ?? ''); ?>"
                        placeholder="e.g. 2025"
                        maxlength="4"
                    >
                </div>

            </div>

            <div class="form-group">
                <label for="description">Description</label>

                <textarea
                    id="description"
                    name="description"
                    placeholder="Brief description of the programme..."
                ><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="display_order">Display Order</label>

                <input
                    type="number"
                    id="display_order"
                    name="display_order"
                    value="<?php echo htmlspecialchars($display_order); ?>"
                    min="0"
                >
            </div>

            <div class="buttons">

                <button type="submit" class="btn btn-primary">
                    Update Education
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>