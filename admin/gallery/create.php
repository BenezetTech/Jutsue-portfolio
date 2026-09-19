<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

$errors = [];

$title = '';
$category = '';
$description = '';
$display_order = '0';

$uploadDirectory = '../../assets/uploads/gallery/';

// Create upload directory if it does not exist
if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $display_order = trim($_POST['display_order'] ?? '0');

    // Validate title
    if ($title === '') {
        $errors[] = 'Gallery title is required.';
    }

    // Validate display order
    if ($display_order === '') {
        $display_order = '0';
    }

    if (
        !filter_var(
            $display_order,
            FILTER_VALIDATE_INT
        ) &&
        $display_order !== '0'
    ) {
        $errors[] = 'Display order must be a valid whole number.';
    }

    // Validate image
    if (
        !isset($_FILES['image']) ||
        $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE
    ) {
        $errors[] = 'Gallery image is required.';
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'There was a problem uploading the image.';
    }

    $uploadedFileName = null;

    if (empty($errors)) {

        $file = $_FILES['image'];

        // Maximum file size: 5 MB
        $maxFileSize = 5 * 1024 * 1024;

        if ($file['size'] > $maxFileSize) {
            $errors[] = 'Image size must not exceed 5 MB.';
        }

        // Verify actual MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedMimeTypes[$mimeType])) {
            $errors[] = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
        }

        // Verify image dimensions
        if (empty($errors)) {

            $imageInfo = @getimagesize($file['tmp_name']);

            if ($imageInfo === false) {
                $errors[] = 'The uploaded file is not a valid image.';
            }
        }

        // Generate secure unique filename
        if (empty($errors)) {

            try {
                $randomName = bin2hex(random_bytes(16));
            } catch (Exception $e) {
                $errors[] = 'Could not generate a secure image filename.';
            }

            if (empty($errors)) {

                $extension = $allowedMimeTypes[$mimeType];

                $uploadedFileName =
                    $randomName . '.' . $extension;

                $destination =
                    $uploadDirectory . $uploadedFileName;

                if (
                    !move_uploaded_file(
                        $file['tmp_name'],
                        $destination
                    )
                ) {
                    $errors[] =
                        'The image could not be saved. Please try again.';
                }
            }
        }
    }

    // Save database record
    if (empty($errors)) {

        $imagePath =
            'assets/uploads/gallery/' . $uploadedFileName;

        $stmt = $pdo->prepare("
            INSERT INTO gallery
            (
                title,
                image,
                category,
                description,
                display_order
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        try {

            $stmt->execute([
                $title,
                $imagePath,
                $category !== '' ? $category : null,
                $description !== '' ? $description : null,
                (int) $display_order
            ]);

            header('Location: index.php?created=1');
            exit;

        } catch (PDOException $e) {

            // Remove uploaded file if database insertion fails
            if (
                $uploadedFileName !== null &&
                file_exists($uploadDirectory . $uploadedFileName)
            ) {
                unlink($uploadDirectory . $uploadedFileName);
            }

            $errors[] =
                'Gallery item could not be created. Please try again.';
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

    <title>Add Gallery Item | Admin Dashboard</title>

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
                Add Gallery Item
            </h1>

            <p class="text-muted mb-0">
                Add a professional photo or visual item to the portfolio.
            </p>

        </div>

        <a
            href="index.php"
            class="btn btn-outline-secondary"
        >
            Back to Gallery
        </a>

    </div>


    <!-- Validation Errors -->

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


    <!-- Form -->

    <div class="card shadow-sm">

        <div class="card-body p-4">

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <!-- Title -->
                <div class="mb-3">

                    <label
                        for="title"
                        class="form-label"
                    >
                        Gallery Title
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($title) ?>"
                    >

                </div>


                <!-- Category -->
                <div class="mb-3">

                    <label
                        for="category"
                        class="form-label"
                    >
                        Category
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="category"
                        name="category"
                        value="<?= htmlspecialchars($category) ?>"
                        placeholder="e.g. Conferences, Training, Leadership"
                    >

                </div>


                <!-- Image -->
                <div class="mb-3">

                    <label
                        for="image"
                        class="form-label"
                    >
                        Image
                    </label>

                    <input
                        type="file"
                        class="form-control"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <div class="form-text">
                        JPG, JPEG, PNG, or WEBP. Maximum size: 5 MB.
                    </div>

                </div>


                <!-- Description -->
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
                    ><?= htmlspecialchars($description) ?></textarea>

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
                        value="<?= htmlspecialchars($display_order) ?>"
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
                        Save Gallery Item
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