```php
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
// GET PROJECT ID
// --------------------------------------------------

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php?error=invalid');
    exit;
}


// --------------------------------------------------
// FETCH PROJECT
// --------------------------------------------------

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
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
        FROM projects
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        header('Location: index.php?error=not_found');
        exit;
    }

} catch (PDOException $e) {

    die(
        '<div style="
            padding:30px;
            font-family:Arial,sans-serif;
            color:#b00020;
        ">
            <h2>Database Error</h2>
            <p>' .
            htmlspecialchars($e->getMessage()) .
            '</p>
        </div>'
    );
}


// --------------------------------------------------
// FORM VARIABLES
// --------------------------------------------------

$title = $project['title'];
$slug = $project['slug'];
$category = $project['category'];
$short_description = $project['short_description'];
$full_description = $project['full_description'];
$technologies = $project['technologies'];
$current_image = $project['image'];
$project_url = $project['project_url'];
$year = $project['year'];
$featured = (int) $project['featured'];
$display_order = $project['display_order'];

$errors = [];


// --------------------------------------------------
// HANDLE FORM SUBMISSION
// --------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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

    // Year validation
    if (
        $year !== '' &&
        !preg_match('/^\d{4}$/', $year)
    ) {
        $errors[] = 'Year must be a valid four-digit year.';
    }

    // Display order validation
    if (
        $display_order !== '' &&
        !is_numeric($display_order)
    ) {
        $errors[] = 'Display order must be a number.';
    }


    // --------------------------------------------------
    // CHECK DUPLICATE SLUG
    // --------------------------------------------------

    if (empty($errors)) {

        try {

            $check = $pdo->prepare("
                SELECT id
                FROM projects
                WHERE slug = ?
                AND id != ?
                LIMIT 1
            ");

            $check->execute([
                $slug,
                $id
            ]);

            if ($check->fetch()) {
                $errors[] =
                    'Another project already uses this slug.';
            }

        } catch (PDOException $e) {

            $errors[] =
                'Unable to check the project slug: ' .
                $e->getMessage();
        }
    }


    // --------------------------------------------------
    // IMAGE UPLOAD
    // --------------------------------------------------

    $new_image = null;
    $image_changed = false;

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if (
            $_FILES['image']['error'] !==
            UPLOAD_ERR_OK
        ) {

            $errors[] =
                'There was an error uploading the image.';

        } else {

            $file = $_FILES['image'];

            // Maximum size: 5MB
            $max_size = 5 * 1024 * 1024;

            if ($file['size'] > $max_size) {

                $errors[] =
                    'Image size must not exceed 5MB.';

            } else {

                // Allowed image types
                $allowed_types = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                // Detect actual MIME type
                $finfo = new finfo(FILEINFO_MIME_TYPE);

                $mime_type = $finfo->file(
                    $file['tmp_name']
                );

                if (!isset($allowed_types[$mime_type])) {

                    $errors[] =
                        'Only JPG, PNG and WEBP images are allowed.';

                } else {

                    // Upload directory
                    $upload_directory =
                        __DIR__ .
                        '/../../assets/uploads/projects/';

                    // Create directory if needed
                    if (!is_dir($upload_directory)) {

                        if (!mkdir(
                            $upload_directory,
                            0755,
                            true
                        )) {

                            $errors[] =
                                'Unable to create the project upload directory.';
                        }
                    }

                    if (empty($errors)) {

                        $extension =
                            $allowed_types[$mime_type];

                        // Generate unique filename
                        $filename =
                            uniqid(
                                'project_',
                                true
                            ) .
                            '.' .
                            $extension;

                        $destination =
                            $upload_directory .
                            $filename;

                        // Save uploaded file
                        if (
                            move_uploaded_file(
                                $file['tmp_name'],
                                $destination
                            )
                        ) {

                            $new_image =
                                'assets/uploads/projects/' .
                                $filename;

                            $image_changed = true;

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
    // UPDATE PROJECT
    // --------------------------------------------------

    if (empty($errors)) {

        try {

            // ------------------------------------------
            // UPDATE WITH NEW IMAGE
            // ------------------------------------------

            if ($image_changed) {

                $stmt = $pdo->prepare("
                    UPDATE projects
                    SET
                        title = :title,
                        slug = :slug,
                        category = :category,
                        short_description = :short_description,
                        full_description = :full_description,
                        technologies = :technologies,
                        image = :image,
                        project_url = :project_url,
                        year = :year,
                        featured = :featured,
                        display_order = :display_order
                    WHERE id = :id
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
                        $new_image,

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
                            ? (int) $display_order
                            : 0,

                    ':id' =>
                        $id
                ]);


                // Delete old image
                if (!empty($current_image)) {

                    $old_image =
                        __DIR__ .
                        '/../../' .
                        $current_image;

                    if (
                        file_exists($old_image) &&
                        is_file($old_image)
                    ) {

                        unlink($old_image);
                    }
                }


            // ------------------------------------------
            // UPDATE WITHOUT CHANGING IMAGE
            // ------------------------------------------

            } else {

                $stmt = $pdo->prepare("
                    UPDATE projects
                    SET
                        title = :title,
                        slug = :slug,
                        category = :category,
                        short_description = :short_description,
                        full_description = :full_description,
                        technologies = :technologies,
                        project_url = :project_url,
                        year = :year,
                        featured = :featured,
                        display_order = :display_order
                    WHERE id = :id
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
                            ? (int) $display_order
                            : 0,

                    ':id' =>
                        $id
                ]);
            }


            // ------------------------------------------
            // SUCCESS
            // ------------------------------------------

            header(
                'Location: index.php?updated=1'
            );

            exit;


        } catch (PDOException $e) {

            // Remove newly uploaded image
            // if database update fails

            if (
                $image_changed &&
                !empty($new_image)
            ) {

                $uploaded_file =
                    __DIR__ .
                    '/../../' .
                    $new_image;

                if (file_exists($uploaded_file)) {
                    unlink($uploaded_file);
                }
            }

            $errors[] =
                'Unable to update project: ' .
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

    <title>Edit Project</title>

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
                Edit Project
            </h1>

            <p class="text-muted mb-0">
                Update this professional project.
            </p>

        </div>

        <a
            href="index.php"
            class="btn btn-outline-secondary"
        >
            ← Back to Projects
        </a>

    </div>


    <!-- ERROR MESSAGES -->

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


    <!-- EDIT FORM -->

    <div class="card shadow-sm border-0">

        <div class="card-body p-4">

            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <!-- PROJECT TITLE -->

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
                        required
                    ><?= htmlspecialchars($technologies) ?></textarea>

                    <div class="form-text">
                        Separate technologies with commas.
                    </div>

                </div>


                <!-- CURRENT IMAGE -->

                <?php if (!empty($current_image)): ?>

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Current Image
                        </label>

                        <div>

                            <img
                                src="../../<?= htmlspecialchars($current_image) ?>"
                                alt="<?= htmlspecialchars($title) ?>"
                                style="
                                    width:180px;
                                    height:120px;
                                    object-fit:cover;
                                    border-radius:8px;
                                "
                            >

                        </div>

                    </div>

                <?php endif; ?>


                <!-- REPLACE IMAGE -->

                <div class="mb-3">

                    <label
                        for="image"
                        class="form-label fw-semibold"
                    >
                        Replace Image
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    >

                    <div class="form-text">
                        Leave empty to keep the current image.
                        JPG, PNG or WEBP. Maximum 5MB.
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
                        Enter the live URL of the project.
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
                        value="1"
                        class="form-check-input"
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
                        Update Project
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
```
