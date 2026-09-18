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
$display_order = 0;

// Upload directory
$uploadDir = '../../assets/uploads/gallery/';
$uploadPath = 'assets/uploads/gallery/';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $display_order = isset($_POST['display_order']) ? (int) $_POST['display_order'] : 0;

    // Validate title
    if ($title === '') {
        $errors[] = 'Gallery title is required.';
    }

    // Validate image
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please select an image.';
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'There was a problem uploading the image.';
    } else {

        $file = $_FILES['image'];

        // Maximum file size: 5MB
        $maxFileSize = 5 * 1024 * 1024;

        if ($file['size'] > $maxFileSize) {
            $errors[] = 'Image size must not exceed 5MB.';
        }

        // Check that the uploaded file is really an uploaded file
        if (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid file upload.';
        }

        // Detect MIME type securely
        if (empty($errors)) {

            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo === false) {
                $errors[] = 'Unable to verify the image file type.';
            } else {

                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                if (!array_key_exists($mimeType, $allowedTypes)) {
                    $errors[] = 'Only JPG, JPEG, PNG, and WEBP images are allowed.';
                }
            }
        }
    }

    // Validate display order
    if ($display_order < 0) {
        $errors[] = 'Display order cannot be negative.';
    }

    // If there are no errors, upload and save
    if (empty($errors)) {

        $extension = $allowedTypes[$mimeType];

        // Create a unique filename
        $newFileName = bin2hex(random_bytes(16)) . '.' . $extension;

        $destination = $uploadDir . $newFileName;
        $databaseImagePath = $uploadPath . $newFileName;

        // Make sure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {

            $errors[] = 'Failed to save the uploaded image.';

        } else {

            try {

                $stmt = $pdo->prepare("
                    INSERT INTO gallery (
                        title,
                        image,
                        category,
                        description,
                        display_order
                    )
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $title,
                    $databaseImagePath,
                    $category,
                    $description,
                    $display_order
                ]);

                header('Location: index.php?created=1');
                exit;

            } catch (PDOException $e) {

                // Remove uploaded file if database insertion fails
                if (file_exists($destination)) {
                    unlink($destination);
                }

                $errors[] = 'Failed to save gallery item to the database.';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Gallery Item | Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="mb-1">Add Gallery Item</h1>
            <p class="text-muted mb-0">
                Add a new image to the professional gallery.
            </p>
        </div>

        <a href="index.php" class="btn btn-secondary">
            ← Back to Gallery
        </a>

    </div>


    <?php if (!empty($errors)): ?>

        <div class="alert alert-danger">

            <h5 class="alert-heading">Please correct the following:</h5>

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

            <form method="POST" enctype="multipart/form-data">

                <!-- Title -->
                <div class="mb-3">

                    <label for="title" class="form-label">
                        Gallery Title <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($title) ?>"
                        placeholder="e.g. Digital Health Project"
                        required
                    >

                </div>


                <!-- Image -->
                <div class="mb-3">

                    <label for="image" class="form-label">
                        Image <span class="text-danger">*</span>
                    </label>

                    <input
                        type="file"
                        class="form-control"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                    <div class="form-text">
                        Accepted formats: JPG, JPEG, PNG, WEBP.
                        Maximum size: 5MB.
                    </div>

                </div>


                <!-- Category -->
                <div class="mb-3">

                    <label for="category" class="form-label">
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

                    <label for="description" class="form-label">
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

                    <label for="display_order" class="form-label">
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
                        Upload & Save Gallery Item
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