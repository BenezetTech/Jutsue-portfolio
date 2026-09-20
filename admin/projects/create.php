<?php

// --------------------------------------------------
// ERROR REPORTING
// --------------------------------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);


// --------------------------------------------------
// DATABASE & AUTHENTICATION
// --------------------------------------------------

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/auth.php';

requireLogin();


// --------------------------------------------------
// FORM VARIABLES
// --------------------------------------------------

$title = '';
$slug = '';
$category = '';
$short_description = '';
$full_description = '';
$technologies = '';
$image = '';
$project_url = '';
$year = '';
$featured = 0;
$display_order = 0;

$errors = [];


// --------------------------------------------------
// HANDLE FORM SUBMISSION
// --------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get submitted values
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $full_description = trim($_POST['full_description'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $project_url = trim($_POST['project_url'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $display_order = trim($_POST['display_order'] ?? '0');


    // --------------------------------------------------
    // VALIDATION
    // --------------------------------------------------

    if ($title === '') {
        $errors[] = 'Project title is required.';
    }

    if ($slug === '') {
        $errors[] = 'Project slug is required.';
    }

    if ($short_description === '') {
        $errors[] = 'Short description is required.';
    }

    if ($full_description === '') {
        $errors[] = 'Full description is required.';
    }

    if ($technologies === '') {
        $errors[] = 'Technologies are required.';
    }

    // Project URL is REQUIRED
    if ($project_url === '') {
        $errors[] = 'Project URL is required.';
    } elseif (!filter_var($project_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'Please enter a valid Project URL.';
    }

    if ($year !== '' && !preg_match('/^\d{4}$/', $year)) {
        $errors[] = 'Year must be a valid four-digit year.';
    }

    if ($display_order !== '' && !is_numeric($display_order)) {
        $errors[] = 'Display order must be a number.';
    }


    // --------------------------------------------------
    // IMAGE UPLOAD
    // --------------------------------------------------

    $uploaded_image = '';

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

            $errors[] = 'There was an error uploading the image.';

        } else {

            $file = $_FILES['image'];

            // Maximum file size: 5MB
            $max_size = 5 * 1024 * 1024;

            if ($file['size'] > $max_size) {

                $errors[] = 'Image size must not exceed 5MB.';

            } else {

                // Allowed MIME types
                $allowed_types = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                // Check actual MIME type
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime_type = $finfo->file($file['tmp_name']);

                if (!isset($allowed_types[$mime_type])) {

                    $errors[] =
                        'Only JPG, PNG and WEBP images are allowed.';

                } else {

                    // Upload directory
                    $upload_directory =
                        __DIR__ . '/../../assets/uploads/projects/';

                    // Create directory if it doesn't exist
                    if (!is_dir($upload_directory)) {

                        if (!mkdir($upload_directory, 0755, true)) {

                            $errors[] =
                                'Unable to create the project upload directory.';
                        }
                    }

                    if (empty($errors)) {

                        // Generate unique filename
                        $extension = $allowed_types[$mime_type];

                        $filename =
                            uniqid('project_', true) .
                            '.' .
                            $extension;

                        $destination =
                            $upload_directory . $filename;

                        // Move uploaded file
                        if (
                            move_uploaded_file(
                                $file['tmp_name'],
                                $destination
                            )
                        ) {

                            // Store relative path in database
                            $uploaded_image =
                                'assets/uploads/projects/' . $filename;

                        } else {

                            $errors[] =
                                'Unable to save the uploaded image.';
                        }
                    }
                }
            }
        }
    }


    // --------------------------------------------------
    // CHECK FOR DUPLICATE SLUG
    // --------------------------------------------------

    if (empty($errors)) {

        try {

            $check = $pdo->prepare("
                SELECT id
                FROM projects
                WHERE slug = ?
                LIMIT 1
            ");

            $check->execute([$slug]);

            if ($check->fetch()) {

                $errors[] =
                    'A project with this slug already exists.';
            }

        } catch (PDOException $e) {

            $errors[] =
                'Unable to check the project slug: ' .
                $e->getMessage();
        }
    }


    // --------------------------------------------------
    // INSERT PROJECT
    // --------------------------------------------------

    if (empty($errors)) {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO projects (
                    title,
                    slug,
                    category,
                    short_description,
                    full_description,
                    technologies,
                    image,
                    project_url,
                    year,
                    featured,
                    display_order
                )
                VALUES (
                    :title,
                    :slug,
                    :category,
                    :short_description,
                    :full_description,
                    :technologies,
                    :image,
                    :project_url,
                    :year,
                    :featured,
                    :display_order
                )
            ");

            $stmt->execute([

                ':title' =>
                    $title,

                ':slug' =>
                    $slug,

                ':category' =>
                    $category,

                ':short_description' =>
                    $short_description,

                ':full_description' =>
                    $full_description,

                ':technologies' =>
                    $technologies,

                ':image' =>
                    $uploaded_image ?: null,

                ':project_url' =>
                    $project_url,

                ':year' =>
                    $year !== ''
                        ? $year
                        : null,

                ':featured' =>
                    $featured,

                ':display_order' =>
                    $display_order !== ''
                        ? (int)$display_order
                        : 0
            ]);


            // Redirect after successful creation
            header(
                'Location: index.php?created=1'
            );

            exit;

        } catch (PDOException $e) {

            // Remove uploaded image if database insertion fails
            if (!empty($uploaded_image)) {

                $uploaded_file =
                    __DIR__ .
                    '/../../' .
                    $uploaded_image;

                if (file_exists($uploaded_file)) {

                    unlink($uploaded_file);
                }
            }

            $errors[] =
                'Unable to create project: ' .
                $e->getMessage();
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

    <title>Add Project</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">

<div class="container py-5">

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="fw-bold mb-1">
                Add Project
            </h1>

            <p class="text-muted mb-0">
                Add a professional project to the portfolio.
            </p>

        </div>

        <a
            href="index.php"
            class="btn btn-outline-secondary"
        >
            ← Back to Projects
        </a>

    </div>


    <!-- VALIDATION ERRORS -->

    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

            <h5 class="fw-bold">
                Please correct the following:
            </h5>

            <ul class="mb-0">

                <?php foreach ($errors as $error): ?>

                    <li>
                        <?= htmlspecialchars($error) ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

    <?php endif; ?>


    <!-- PROJECT FORM -->

    <div class="card shadow-sm border-0">

        <div class="card-body p-4">

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <!-- TITLE -->

                <div class="mb-3">

                    <label
                        for="title"
                        class="form-label fw-semibold"
                    >
                        Project Title *
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        class="form-control"
                        value="<?= htmlspecialchars($title) ?>"
                        placeholder="e.g. LDCollect"
                        required
                    >

                </div>


                <!-- SLUG -->

                <div class="mb-3">

                    <label
                        for="slug"
                        class="form-label fw-semibold"
                    >
                        Slug *
                    </label>

                    <input
                        type="text"
                        id="slug"
                        name="slug"
                        class="form-control"
                        value="<?= htmlspecialchars($slug) ?>"
                        placeholder="e.g. ldcollect"
                        required
                    >

                    <div class="form-text">
                        Use lowercase letters, numbers and hyphens.
                    </div>

                </div>


                <!-- CATEGORY -->

                <div class="mb-3">

                    <label
                        for="category"
                        class="form-label fw-semibold"
                    >
                        Category
                    </label>

                    <input
                        type="text"
                        id="category"
                        name="category"
                        class="form-control"
                        value="<?= htmlspecialchars($category) ?>"
                        placeholder="e.g. Digital Health"
                    >

                </div>


                <!-- SHORT DESCRIPTION -->

                <div class="mb-3">

                    <label
                        for="short_description"
                        class="form-label fw-semibold"
                    >
                        Short Description *
                    </label>

                    <textarea
                        id="short_description"
                        name="short_description"
                        class="form-control"
                        rows="3"
                        placeholder="Briefly describe the project..."
                        required
                    ><?= htmlspecialchars($short_description) ?></textarea>

                </div>


                <!-- FULL DESCRIPTION -->

                <div class="mb-3">

                    <label
                        for="full_description"
                        class="form-label fw-semibold"
                    >
                        Full Description *
                    </label>

                    <textarea
                        id="full_description"
                        name="full_description"
                        class="form-control"
                        rows="7"
                        placeholder="Provide the complete project description..."
                        required
                    ><?= htmlspecialchars($full_description) ?></textarea>

                </div>


                <!-- TECHNOLOGIES -->

                <div class="mb-3">

                    <label
                        for="technologies"
                        class="form-label fw-semibold"
                    >
                        Technologies *
                    </label>

                    <textarea
                        id="technologies"
                        name="technologies"
                        class="form-control"
                        rows="3"
                        placeholder="PHP, MySQL, JavaScript, React, Flutter..."
                        required
                    ><?= htmlspecialchars($technologies) ?></textarea>

                    <div class="form-text">
                        Separate technologies with commas.
                    </div>

                </div>


                <!-- IMAGE -->

                <div class="mb-3">

                    <label
                        for="image"
                        class="form-label fw-semibold"
                    >
                        Project Image
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    >

                    <div class="form-text">
                        JPG, PNG or WEBP. Maximum size: 5MB.
                    </div>

                </div>


                <!-- PROJECT URL -->

                <div class="mb-3">

                    <label
                        for="project_url"
                        class="form-label fw-semibold"
                    >
                        Project URL *
                    </label>

                    <input
                        type="url"
                        id="project_url"
                        name="project_url"
                        class="form-control"
                        value="<?= htmlspecialchars($project_url) ?>"
                        placeholder="https://example.com"
                        required
                    >

                    <div class="form-text">
                        Enter the live website, GitHub repository, demo, or other project link.
                    </div>

                </div>


                <!-- YEAR -->

                <div class="mb-3">

                    <label
                        for="year"
                        class="form-label fw-semibold"
                    >
                        Year
                    </label>

                    <input
                        type="number"
                        id="year"
                        name="year"
                        class="form-control"
                        value="<?= htmlspecialchars($year) ?>"
                        placeholder="2026"
                        min="1900"
                        max="2100"
                    >

                </div>


                <!-- FEATURED -->

                <div class="form-check mb-3">

                    <input
                        type="checkbox"
                        id="featured"
                        name="featured"
                        class="form-check-input"
                        value="1"
                        <?= $featured ? 'checked' : '' ?>
                    >

                    <label
                        for="featured"
                        class="form-check-label fw-semibold"
                    >
                        Feature this project on the portfolio
                    </label>

                </div>


                <!-- DISPLAY ORDER -->

                <div class="mb-4">

                    <label
                        for="display_order"
                        class="form-label fw-semibold"
                    >
                        Display Order
                    </label>

                    <input
                        type="number"
                        id="display_order"
                        name="display_order"
                        class="form-control"
                        value="<?= htmlspecialchars($display_order) ?>"
                        min="0"
                    >

                    <div class="form-text">
                        Lower numbers appear first.
                    </div>

                </div>


                <!-- BUTTONS -->

                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Project
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

    </div>

</div>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>