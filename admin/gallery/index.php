<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../includes/auth/auth.php';

requireLogin();

// Fetch all gallery items
$stmt = $pdo->query("
    SELECT
        id,
        title,
        image,
        category,
        description,
        display_order,
        created_at,
        updated_at
    FROM gallery
    ORDER BY category ASC, display_order ASC, title ASC
");

$galleryItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Gallery Management | Admin Dashboard</title>

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
                Gallery Management
            </h1>

            <p class="text-muted mb-0">
                Manage professional photos and visual content displayed on the portfolio.
            </p>

        </div>

        <div class="d-flex gap-2">

            <a
                href="../index.php"
                class="btn btn-outline-secondary"
            >
                Admin Dashboard
            </a>

            <a
                href="create.php"
                class="btn btn-primary"
            >
                + Add Gallery Item
            </a>

        </div>

    </div>


    <!-- Success Messages -->

    <?php if (isset($_GET['created'])): ?>

        <div class="alert alert-success alert-dismissible fade show">

            Gallery item created successfully.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <?php if (isset($_GET['updated'])): ?>

        <div class="alert alert-success alert-dismissible fade show">

            Gallery item updated successfully.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <?php if (isset($_GET['deleted'])): ?>

        <div class="alert alert-success alert-dismissible fade show">

            Gallery item deleted successfully.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <!-- Error Messages -->

    <?php if (isset($_GET['invalid_id'])): ?>

        <div class="alert alert-warning alert-dismissible fade show">

            Invalid gallery item ID.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <?php if (isset($_GET['not_found'])): ?>

        <div class="alert alert-warning alert-dismissible fade show">

            Gallery item not found.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <?php if (isset($_GET['delete_error'])): ?>

        <div class="alert alert-danger alert-dismissible fade show">

            Gallery item could not be deleted. Please try again.

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <!-- Gallery Content -->

    <div class="card shadow-sm">

        <div class="card-body p-0">

            <?php if (empty($galleryItems)): ?>

                <div class="p-5 text-center">

                    <h5 class="mb-2">
                        No Gallery Items Found
                    </h5>

                    <p class="text-muted mb-3">
                        You have not added any gallery images yet.
                    </p>

                    <a
                        href="create.php"
                        class="btn btn-primary"
                    >
                        Add Your First Gallery Item
                    </a>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-dark">

                            <tr>

                                <th scope="col">
                                    #
                                </th>

                                <th scope="col">
                                    Image
                                </th>

                                <th scope="col">
                                    Title
                                </th>

                                <th scope="col">
                                    Category
                                </th>

                                <th scope="col">
                                    Description
                                </th>

                                <th scope="col">
                                    Order
                                </th>

                                <th
                                    scope="col"
                                    class="text-end"
                                >
                                    Actions
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($galleryItems as $item): ?>

                            <tr>

                                <!-- ID -->
                                <td>
                                    <?= (int) $item['id'] ?>
                                </td>


                                <!-- Image -->
                                <td>

                                    <?php
                                    $imagePath = '../../' . ltrim(
                                        $item['image'],
                                        '/'
                                    );
                                    ?>

                                    <?php if (
                                        !empty($item['image']) &&
                                        is_file($imagePath)
                                    ): ?>

                                        <img
                                            src="../../<?= htmlspecialchars(
                                                ltrim($item['image'], '/')
                                            ) ?>"
                                            alt="<?= htmlspecialchars(
                                                $item['title']
                                            ) ?>"
                                            width="80"
                                            height="60"
                                            class="rounded border"
                                            style="object-fit: cover;"
                                        >

                                    <?php else: ?>

                                        <div
                                            class="bg-light border rounded d-flex align-items-center justify-content-center"
                                            style="width: 80px; height: 60px;"
                                        >
                                            <small class="text-muted">
                                                No image
                                            </small>
                                        </div>

                                    <?php endif; ?>

                                </td>


                                <!-- Title -->
                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $item['title']
                                        ) ?>
                                    </strong>

                                </td>


                                <!-- Category -->
                                <td>

                                    <?php if (!empty($item['category'])): ?>

                                        <span class="badge text-bg-secondary">
                                            <?= htmlspecialchars(
                                                $item['category']
                                            ) ?>
                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Not specified
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Description -->
                                <td>

                                    <?php if (
                                        !empty($item['description'])
                                    ): ?>

                                        <?php
                                        $description = trim(
                                            $item['description']
                                        );

                                        if (mb_strlen($description) > 80) {
                                            $description =
                                                mb_substr(
                                                    $description,
                                                    0,
                                                    80
                                                ) . '...';
                                        }
                                        ?>

                                        <?= htmlspecialchars(
                                            $description
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No description
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Display Order -->
                                <td>
                                    <?= (int) $item['display_order'] ?>
                                </td>


                                <!-- Actions -->
                                <td class="text-end">

                                    <a
                                        href="edit.php?id=<?= (int) $item['id'] ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Edit
                                    </a>


                                    <!-- DELETE MUST USE POST -->
                                    <form
                                        method="POST"
                                        action="delete.php"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this gallery item and its image? This action cannot be undone.');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $item['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>