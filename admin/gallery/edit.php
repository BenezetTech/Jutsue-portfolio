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

// Fetch gallery item
$stmt = $pdo->prepare("
    SELECT id, title, image, category, description, display_order
    FROM gallery
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$galleryItem = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$galleryItem) {
    header('Location: index.php?not_found=1');
    exit;
}

$errors = [];

$title = $galleryItem['title'];
$category = $galleryItem['category'] ?? '';
$description = $galleryItem['description'] ?? '';
$display_order = $galleryItem['display_order'];
$currentImage = $galleryItem['image'];

$uploadDirectory = '../../assets/uploads/gallery/';

// Create upload directory if necessary
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
        !filter_var($display_order, FILTER_VALIDATE_INT) &&
        $display_order !== '0'
    ) {
        $errors[] = 'Display order must be a valid whole number.';
    }

    $newUploadedFileName = null;
    $newImagePath = $currentImage;

    // Check whether a replacement image was uploaded
    $hasNewImage =
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

    if ($hasNewImage) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

            $errors[] =
                'There was a problem uploading the replacement image.';

        } else {

            $file = $_FILES['image'];

            // Maximum file size: 5 MB
            $maxFileSize = 5 * 1024 * 1024;

            if ($file['size'] > $maxFileSize) {
                $errors[] = 'Image size must not exceed 5 MB.';
            }

            // Verify actual MIME type
            if (empty($errors)) {

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);

                $allowedMimeTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                if (!isset($allowedMimeTypes[$mimeType])) {
                    $errors[] =
                        'Only JPG, JPEG, PNG, and WEBP images are allowed.';
                }
            }

            // Verify actual image
            if (empty($errors)) {

                $imageInfo = @getimagesize($file['tmp_name']);

                if ($imageInfo === false) {
                    $errors[] =
                        'The uploaded file is not a valid image.';
                }
            }

            // Save replacement image
            if (empty($errors)) {

                try {

                    $randomName = bin2hex(random_bytes(16));

                    $extension = $allowedMimeTypes[$mimeType];

                    $newUploadedFileName =
                        $randomName . '.' . $extension;

                    $destination =
                        $uploadDirectory . $newUploadedFileName;

                    if (
                        !move_uploaded_file(
                            $file['tmp_name'],
                            $destination
                        )
                    ) {
                        $errors[] =
                            'The replacement image could not be saved.';
                    }

                } catch (Exception $e) {

                    $errors[] =
                        'Could not generate a secure image filename.';
                }
            }

            if (empty($errors)) {

                $newImagePath =
                    'assets/uploads/gallery/' .
                    $newUploadedFileName;
            }
        }
    }

    // Update database
    if (empty($errors)) {

        $stmt = $pdo->prepare("
            UPDATE gallery
            SET
                title = ?,
                image = ?,
                category = ?,
                description = ?,
                display_order = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        try {

            $stmt->execute([
                $title,
                $newImagePath,
                $category !== '' ? $category : null,
                $description !== '' ? $description : null,
                (int) $display_order,
                $id
            ]);

            // Remove old image only after successful database update
            if (
                $newUploadedFileName !== null &&
                !empty($currentImage)
            ) {

                $oldImagePath =
                    '../../' . ltrim($currentImage, '/');

                $galleryDirectory =
                    realpath($uploadDirectory);

                $oldFileRealPath =
                    realpath($oldImagePath);

                if (
                    $galleryDirectory !== false &&
                    $oldFileRealPath !== false &&
                    strpos(
                        $oldFileRealPath,
                        $galleryDirectory . DIRECTORY_SEPARATOR
                    ) === 0 &&
                    is_file($oldFileRealPath)
                ) {
                    unlink($oldFileRealPath);
                }
            }

            header('Location: index.php?updated=1');
            exit;

        } catch (PDOException $e) {

            // Remove newly uploaded image if database update fails
            if (
                $newUploadedFileName !== null &&
                is_file(
                    $uploadDirectory . $newUploadedFileName
                )
            ) {
                unlink(
                    $uploadDirectory . $newUploadedFileName
                );
            }

            $errors[] =
                'Gallery item could not be updated. Please try again.';
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

    <title>Edit Gallery Item | Admin Dashboard</title>

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
                Edit Gallery Item
            </h1>

            <p class="text-muted mb-0">
                Update the gallery item or replace its image.
            </p>

        </div>

        <a
            href="index.php"
            class="btn btn-outline-secondary"
        >
            Back to Gallery
        </a>

    </div>


    <!-- Errors -->

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


                <!-- Current Image -->
                <div class="mb-3">

                    <label class="form-label">
                        Current Image
                    </label>

                    <?php
                    $currentImagePath =
                        '../../' . ltrim($currentImage, '/');
                    ?>

                    <?php if (
                        !empty($currentImage) &&
                        is_file($currentImagePath)
                    ): ?>

                        <div class="mb-2">

                            <img
                                src="../../<?= htmlspecialchars(
                                    ltrim($currentImage, '/')
                                ) ?>"
                                alt="<?= htmlspecialchars($title) ?>"
                                class="img-thumbnail"
                                style="
                                    max-width: 300px;
                                    max-height: 220px;
                                    object-fit: cover;
                                "
                            >

                        </div>

                    <?php else: ?>

                        <p class="text-muted">
                            Current image is not available.
                        </p>

                    <?php endif; ?>

                </div>


                <!-- Replacement Image -->
                <div class="mb-3">

                    <label
                        for="image"
                        class="form-label"
                    >
                        Replace Image
                    </label>

                    <input
                        type="file"
                        class="form-control"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <div class="form-text">
                        Leave empty to keep the current image.
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

                </div>


                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Update Gallery Item
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