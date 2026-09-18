<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

$errors = [];

// Get gallery ID
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php?invalid_id=1');
    exit;
}

// Fetch existing gallery item
$stmt = $pdo->prepare("
    SELECT
        id,
        title,
        image,
        category,
        description,
        display_order
    FROM gallery
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$gallery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gallery) {
    header('Location: index.php?not_found=1');
    exit;
}

// Existing values
$title = $gallery['title'];
$category = $gallery['category'];
$description = $gallery['description'];
$display_order = $gallery['display_order'];
$currentImage = $gallery['image'];

// Upload directory
$uploadDir = '../../assets/uploads/gallery/';
$uploadPath = 'assets/uploads/gallery/';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $display_order = isset($_POST['display_order'])
        ? (int) $_POST['display_order']
        : 0;

    // Validate title
    if ($title === '') {
        $errors[] = 'Gallery title is required.';
    }

    // Validate display order
    if ($display_order < 0) {
        $errors[] = 'Display order cannot be negative.';
    }

    // Check whether a new image was selected
    $newImageSelected = (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    );

    if ($newImageSelected) {

        $file = $_FILES['image'];

        // Check upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {

            $errors[] = 'There was a problem uploading the new image.';

        } else {

            // Maximum file size: 5MB
            $maxFileSize = 5 * 1024 * 1024;

            if ($file['size'] > $maxFileSize) {
                $errors[] = 'Image size must not exceed 5MB.';
            }

            // Check that it is a real uploaded file
            if (!is_uploaded_file($file['tmp_name'])) {
                $errors[] = 'Invalid file upload.';
            }

            // Secure MIME type detection
            if (empty($errors)) {

                $finfo = finfo_open(FILEINFO_MIME_TYPE);

                if ($finfo === false) {

                    $errors[] = 'Unable to verify the image file type.';

                } else {

                    $mimeType = finfo_file(
                        $finfo,
                        $file['tmp_name']
                    );

                    finfo_close($finfo);

                    $allowedTypes = [
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/webp' => 'webp'
                    ];

                    if (!array_key_exists($mimeType, $allowedTypes)) {
                        $errors[] =
                            'Only JPG, JPEG, PNG, and WEBP images are allowed.';
                    }
                }
            }
        }
    }

    // If validation passes
    if (empty($errors)) {

        try {

            /*
             * If a new image was selected,
             * upload it and update the image path.
             */
            if ($newImageSelected) {

                $extension = $allowedTypes[$mimeType];

                // Generate a unique filename
                $newFileName =
                    bin2hex(random_bytes(16))
                    . '.'
                    . $extension;

                $destination =
                    $uploadDir . $newFileName;

                $newDatabaseImagePath =
                    $uploadPath . $newFileName;

                // Make sure upload directory exists
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Move new image
                if (!move_uploaded_file(
                    $file['tmp_name'],
                    $destination
                )) {

                    $errors[] =
                        'Failed to save the new uploaded image.';

                } else {

                    // Update database including new image
                    $stmt = $pdo->prepare("
                        UPDATE gallery
                        SET
                            title = ?,
                            image = ?,
                            category = ?,
                            description = ?,
                            display_order = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $title,
                        $newDatabaseImagePath,
                        $category,
                        $description,
                        $display_order,
                        $id
                    ]);

                    // Delete old image
                    if (
                        !empty($currentImage) &&
                        strpos($currentImage, 'assets/uploads/gallery/') === 0
                    ) {

                        $oldImagePath =
                            '../../' . $currentImage;

                        if (
                            file_exists($oldImagePath) &&
                            is_file($oldImagePath)
                        ) {
                            unlink($oldImagePath);
                        }
                    }

                    header('Location: index.php?updated=1');
                    exit;
                }

            } else {

                /*
                 * No new image selected.
                 * Keep the existing image.
                 */
                $stmt = $pdo->prepare("
                    UPDATE gallery
                    SET
                        title = ?,
                        category = ?,
                        description = ?,
                        display_order = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $title,
                    $category,
                    $description,
                    $display_order,
                    $id
                ]);

                header('Location: index.php?updated=1');
                exit;
            }

        } catch (PDOException $e) {

            // Remove newly uploaded file if database update fails
            if (
                isset($destination) &&
                file_exists($destination)
            ) {
                unlink($destination);
            }

            $errors[] =
                'Failed to update the gallery item.';
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

    <title>Edit Gallery Item | Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container py-5">

    <!-- Page Header -->
    <div
        class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h1 class="mb-1">
                Edit Gallery Item
            </h1>

            <p class="text-muted mb-0">
                Update the selected gallery item.
            </p>

        </div>

        <a
            href="index.php"
            class="btn btn-secondary"
        >
            ← Back to Gallery
        </a>

    </div>


    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

            <h5 class="alert-heading">
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
                        <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($title) ?>"
                        required
                    >

                </div>


                <!-- Current Image -->
                <div class="mb-3">

                    <label class="form-label">
                        Current Image
                    </label>

                    <div>

                        <img
                            src="../../<?= htmlspecialchars($currentImage) ?>"
                            alt="<?= htmlspecialchars($title) ?>"
                            class="img-thumbnail"
                            style="
                                max-width: 300px;
                                max-height: 220px;
                                object-fit: cover;
                            "
                        >

                    </div>

                </div>


                <!-- New Image -->
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
                        Leave this empty to keep the current image.
                        Accepted formats: JPG, JPEG, PNG, WEBP.
                        Maximum size: 5MB.
                    </div>

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
                        placeholder="e.g. Projects, Events, Leadership"
                    >

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
                        placeholder="Briefly describe this gallery image..."
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