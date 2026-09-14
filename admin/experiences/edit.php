<?php

/**
 * Edit Professional Experience
 * Jutsue Mekodjio Bilios Portfolio CMS
 *
 * Authenticated administrators can edit experience records.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/auth.php';

requireLogin();

$error = '';

/*
|--------------------------------------------------------------------------
| Get Experience ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Load Experience
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            job_title,
            organization,
            location,
            start_date,
            end_date,
            is_current,
            description,
            display_order
        FROM experiences
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->execute([
        'id' => $id
    ]);

    $experience = $stmt->fetch();

    if (!$experience) {
        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {

    error_log(
        'Experience lookup error: ' . $e->getMessage()
    );

    exit('Unable to load experience record.');
}

/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jobTitle = trim($_POST['job_title'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $location = trim($_POST['location'] ?? '');

    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';

    $isCurrent = isset($_POST['is_current']) ? 1 : 0;

    $description = trim($_POST['description'] ?? '');

    $displayOrder = filter_input(
        INPUT_POST,
        'display_order',
        FILTER_VALIDATE_INT
    );

    if ($displayOrder === false || $displayOrder === null) {
        $displayOrder = 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($jobTitle === '') {

        $error = 'Job title is required.';

    } elseif ($organization === '') {

        $error = 'Organization is required.';

    } elseif (
        $startDate !== '' &&
        !DateTime::createFromFormat('Y-m-d', $startDate)
    ) {

        $error = 'Please enter a valid start date.';

    } elseif (
        !$isCurrent &&
        $endDate === ''
    ) {

        $error = 'Please provide an end date for a completed experience.';

    } elseif (
        $endDate !== '' &&
        !DateTime::createFromFormat('Y-m-d', $endDate)
    ) {

        $error = 'Please enter a valid end date.';

    } elseif (
        $startDate !== '' &&
        $endDate !== '' &&
        strtotime($endDate) < strtotime($startDate)
    ) {

        $error = 'End date cannot be earlier than the start date.';

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | Update Experience
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                UPDATE experiences
                SET
                    job_title = :job_title,
                    organization = :organization,
                    location = :location,
                    start_date = :start_date,
                    end_date = :end_date,
                    is_current = :is_current,
                    description = :description,
                    display_order = :display_order
                WHERE id = :id
            ");

            $stmt->execute([
                'job_title' => $jobTitle,
                'organization' => $organization,
                'location' => $location ?: null,
                'start_date' => $startDate ?: null,
                'end_date' => $isCurrent
                    ? null
                    : ($endDate ?: null),
                'is_current' => $isCurrent,
                'description' => $description ?: null,
                'display_order' => $displayOrder,
                'id' => $id
            ]);

            header('Location: index.php?updated=1');
            exit;

        } catch (PDOException $e) {

            error_log(
                'Experience update error: ' .
                $e->getMessage()
            );

            $error =
                'Unable to update the professional experience.';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Preserve Form Values
    |--------------------------------------------------------------------------
    */

    $experience['job_title'] =
        $jobTitle;

    $experience['organization'] =
        $organization;

    $experience['location'] =
        $location;

    $experience['start_date'] =
        $startDate;

    $experience['end_date'] =
        $endDate;

    $experience['is_current'] =
        $isCurrent;

    $experience['description'] =
        $description;

    $experience['display_order'] =
        $displayOrder;
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
        Edit Experience | Portfolio CMS
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
            href="index.php"
            class="btn btn-outline-light btn-sm"
        >
            Back
        </a>

    </div>

</nav>

<main class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-9">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4 p-md-5">

                    <h1 class="h3 fw-bold mb-1">
                        Edit Professional Experience
                    </h1>

                    <p class="text-muted mb-4">
                        Update this position in the professional career timeline.
                    </p>

                    <?php if ($error): ?>

                        <div class="alert alert-danger">
                            <?= htmlspecialchars(
                                $error,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </div>

                    <?php endif; ?>

                    <form method="POST">

                        <!-- Position -->

                        <h2 class="h5 fw-bold mb-3">
                            Position Information
                        </h2>

                        <div class="row g-3">

                            <div class="col-md-6">

                                <label
                                    for="job_title"
                                    class="form-label"
                                >
                                    Job Title
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="job_title"
                                    name="job_title"
                                    value="<?= htmlspecialchars(
                                        $experience['job_title'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    required
                                >

                            </div>

                            <div class="col-md-6">

                                <label
                                    for="organization"
                                    class="form-label"
                                >
                                    Organization
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="organization"
                                    name="organization"
                                    value="<?= htmlspecialchars(
                                        $experience['organization'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    required
                                >

                            </div>

                            <div class="col-md-6">

                                <label
                                    for="location"
                                    class="form-label"
                                >
                                    Location
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="location"
                                    name="location"
                                    value="<?= htmlspecialchars(
                                        $experience['location'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                            </div>

                        </div>

                        <hr class="my-4">

                        <!-- Dates -->

                        <h2 class="h5 fw-bold mb-3">
                            Employment Period
                        </h2>

                        <div class="row g-3">

                            <div class="col-md-6">

                                <label
                                    for="start_date"
                                    class="form-label"
                                >
                                    Start Date
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    id="start_date"
                                    name="start_date"
                                    value="<?= htmlspecialchars(
                                        $experience['start_date'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                            </div>

                            <div class="col-md-6">

                                <label
                                    for="end_date"
                                    class="form-label"
                                >
                                    End Date
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    id="end_date"
                                    name="end_date"
                                    value="<?= htmlspecialchars(
                                        $experience['end_date'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                            </div>

                        </div>

                        <div class="form-check mt-3">

                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="is_current"
                                name="is_current"
                                <?= (int) $experience['is_current'] === 1
                                    ? 'checked'
                                    : '' ?>
                            >

                            <label
                                class="form-check-label"
                                for="is_current"
                            >
                                This is a current position
                            </label>

                        </div>

                        <hr class="my-4">

                        <!-- Description -->

                        <h2 class="h5 fw-bold mb-3">
                            Description
                        </h2>

                        <div class="mb-4">

                            <textarea
                                class="form-control"
                                id="description"
                                name="description"
                                rows="8"
                                placeholder="Describe responsibilities, achievements, technologies, and impact..."
                            ><?= htmlspecialchars(
                                $experience['description'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></textarea>

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
                                    (string) $experience['display_order'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                min="0"
                            >

                            <div class="form-text">
                                Lower numbers appear first.
                            </div>

                        </div>

                        <!-- Actions -->

                        <div class="d-flex gap-2">

                            <a
                                href="index.php"
                                class="btn btn-outline-secondary"
                            >
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="btn btn-dark"
                            >
                                Save Changes
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</main>

</body>

</html>