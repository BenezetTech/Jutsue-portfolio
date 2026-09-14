```php
<?php

/**
 * Profile Management
 * Jutsue Mekodjio Bilios Portfolio CMS
 *
 * Authenticated administrators can view the profile.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/auth.php';

requireLogin();

/*
|--------------------------------------------------------------------------
| Load Profile
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->query("
        SELECT
            id,
            full_name,
            professional_title,
            headline,
            bio,
            location,
            phone_primary,
            phone_secondary,
            email_primary,
            email_secondary,
            linkedin_url,
            website_url,
            profile_photo,
            cv_file,
            updated_at
        FROM profile
        ORDER BY id ASC
        LIMIT 1
    ");

    $profile = $stmt->fetch();

    /*
    |--------------------------------------------------------------------------
    | Default Empty Profile
    |--------------------------------------------------------------------------
    */

    if (!$profile) {

        $profile = [
            'id' => null,
            'full_name' => '',
            'professional_title' => '',
            'headline' => '',
            'bio' => '',
            'location' => '',
            'phone_primary' => '',
            'phone_secondary' => '',
            'email_primary' => '',
            'email_secondary' => '',
            'linkedin_url' => '',
            'website_url' => '',
            'profile_photo' => '',
            'cv_file' => '',
            'updated_at' => null
        ];
    }

} catch (PDOException $e) {

    error_log(
        'Profile management error: ' . $e->getMessage()
    );

    exit('Unable to load profile information.');
}

/*
|--------------------------------------------------------------------------
| Current Administrator
|--------------------------------------------------------------------------
*/

$adminName = $_SESSION['admin_name'] ?? 'Administrator';
$adminRole = $_SESSION['admin_role'] ?? 'admin';

/*
|--------------------------------------------------------------------------
| Success Message
|--------------------------------------------------------------------------
*/

$successMessage = '';

if (isset($_GET['updated'])) {

    $successMessage = 'Professional profile updated successfully.';
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
        Profile Management | Portfolio CMS
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<!-- ==============================================================
     NAVIGATION
     ============================================================== -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <span class="navbar-brand fw-bold">
            Jutsue Portfolio CMS
        </span>

        <div class="d-flex align-items-center gap-2">

            <span class="text-white small">
                <?= htmlspecialchars(
                    $adminName,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </span>

            <a
                href="../auth/logout.php"
                class="btn btn-outline-light btn-sm"
            >
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- ==============================================================
     MAIN CONTENT
     ============================================================== -->

<main class="container py-5">

    <!-- Success Message -->

    <?php if ($successMessage): ?>

        <div
            class="alert alert-success alert-dismissible fade show"
            role="alert"
        >

            <strong>Success!</strong>

            <?= htmlspecialchars(
                $successMessage,
                ENT_QUOTES,
                'UTF-8'
            ) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>

    <?php endif; ?>


    <!-- Page Header -->

    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4"
    >

        <div>

            <h1 class="h3 fw-bold mb-1">
                Profile Management
            </h1>

            <p class="text-muted mb-0">
                Manage the professional profile displayed on the portfolio.
            </p>

        </div>

        <a
            href="edit.php"
            class="btn btn-dark"
        >
            Edit Profile
        </a>

    </div>


    <!-- ==========================================================
         PROFILE CARD
         ========================================================== -->

    <div class="card border-0 shadow-sm">

        <div class="card-body p-4 p-md-5">

            <div class="row g-4">


                <!-- ==================================================
                     PROFILE PHOTO
                     ================================================== -->

                <div class="col-md-4 col-lg-3 text-center">

                    <?php if (!empty($profile['profile_photo'])): ?>

                        <img
                            src="../../assets/uploads/<?= htmlspecialchars(
                                $profile['profile_photo'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            alt="Profile Photo"
                            class="img-fluid rounded-circle shadow-sm"
                            style="
                                width: 180px;
                                height: 180px;
                                object-fit: cover;
                            "
                        >

                    <?php else: ?>

                        <div
                            class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center mx-auto"
                            style="
                                width: 180px;
                                height: 180px;
                            "
                        >

                            <span class="display-6">

                                <?= htmlspecialchars(
                                    strtoupper(
                                        substr(
                                            $profile['full_name'] ?: 'J',
                                            0,
                                            1
                                        )
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </span>

                        </div>

                    <?php endif; ?>

                </div>


                <!-- ==================================================
                     BASIC INFORMATION
                     ================================================== -->

                <div class="col-md-8 col-lg-9">

                    <h2 class="h3 fw-bold mb-1">

                        <?= htmlspecialchars(
                            $profile['full_name']
                                ?: 'Profile not yet configured',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </h2>

                    <?php if ($profile['professional_title']): ?>

                        <p class="text-primary fw-semibold mb-2">

                            <?= htmlspecialchars(
                                $profile['professional_title'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>

                    <?php endif; ?>


                    <?php if ($profile['headline']): ?>

                        <p class="text-muted mb-4">

                            <?= htmlspecialchars(
                                $profile['headline'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </p>

                    <?php endif; ?>


                    <!-- Contact Summary -->

                    <div class="row g-3">

                        <!-- Location -->

                        <div class="col-md-6">

                            <div class="border rounded p-3 bg-light">

                                <small class="text-muted d-block mb-1">
                                    Location
                                </small>

                                <strong>

                                    <?= htmlspecialchars(
                                        $profile['location']
                                            ?: 'Not provided',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                            </div>

                        </div>


                        <!-- Primary Email -->

                        <div class="col-md-6">

                            <div class="border rounded p-3 bg-light">

                                <small class="text-muted d-block mb-1">
                                    Primary Email
                                </small>

                                <strong>

                                    <?= htmlspecialchars(
                                        $profile['email_primary']
                                            ?: 'Not provided',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                            </div>

                        </div>


                        <!-- Primary Phone -->

                        <div class="col-md-6">

                            <div class="border rounded p-3 bg-light">

                                <small class="text-muted d-block mb-1">
                                    Primary Phone
                                </small>

                                <strong>

                                    <?= htmlspecialchars(
                                        $profile['phone_primary']
                                            ?: 'Not provided',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </strong>

                            </div>

                        </div>


                        <!-- LinkedIn -->

                        <div class="col-md-6">

                            <div class="border rounded p-3 bg-light">

                                <small class="text-muted d-block mb-1">
                                    LinkedIn
                                </small>

                                <?php if ($profile['linkedin_url']): ?>

                                    <a
                                        href="<?= htmlspecialchars(
                                            $profile['linkedin_url'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        View LinkedIn Profile
                                    </a>

                                <?php else: ?>

                                    <strong>
                                        Not provided
                                    </strong>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <hr class="my-5">


            <!-- ======================================================
                 PROFESSIONAL BIOGRAPHY
                 ====================================================== -->

            <div class="mb-4">

                <h2 class="h5 fw-bold">
                    Professional Biography
                </h2>

                <?php if ($profile['bio']): ?>

                    <div class="text-muted">

                        <?= nl2br(
                            htmlspecialchars(
                                $profile['bio'],
                                ENT_QUOTES,
                                'UTF-8'
                            )
                        ) ?>

                    </div>

                <?php else: ?>

                    <p class="text-muted mb-0">
                        <em>
                            No biography has been added yet.
                        </em>
                    </p>

                <?php endif; ?>

            </div>


            <!-- ======================================================
                 SECONDARY CONTACT & WEBSITE
                 ====================================================== -->

            <div class="row g-4">


                <!-- Secondary Contact -->

                <div class="col-md-6">

                    <h2 class="h5 fw-bold">
                        Secondary Contact
                    </h2>

                    <p class="mb-2">

                        <strong>Phone:</strong>

                        <?= htmlspecialchars(
                            $profile['phone_secondary']
                                ?: 'Not provided',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </p>

                    <p class="mb-0">

                        <strong>Email:</strong>

                        <?= htmlspecialchars(
                            $profile['email_secondary']
                                ?: 'Not provided',
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </p>

                </div>


                <!-- Website & CV -->

                <div class="col-md-6">

                    <h2 class="h5 fw-bold">
                        Website & CV
                    </h2>

                    <p class="mb-2">

                        <strong>Website:</strong>

                        <?php if ($profile['website_url']): ?>

                            <a
                                href="<?= htmlspecialchars(
                                    $profile['website_url'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Visit Website
                            </a>

                        <?php else: ?>

                            <span class="text-muted">
                                Not provided
                            </span>

                        <?php endif; ?>

                    </p>


                    <p class="mb-0">

                        <strong>CV:</strong>

                        <?php if ($profile['cv_file']): ?>

                            <a
                                href="../../assets/uploads/<?= htmlspecialchars(
                                    $profile['cv_file'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                View CV
                            </a>

                        <?php else: ?>

                            <span class="text-muted">
                                Not uploaded
                            </span>

                        <?php endif; ?>

                    </p>

                </div>

            </div>


            <!-- ======================================================
                 LAST UPDATED
                 ====================================================== -->

            <?php if ($profile['updated_at']): ?>

                <hr class="my-4">

                <p class="text-muted small mb-0">

                    Last updated:

                    <?= htmlspecialchars(
                        $profile['updated_at'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </p>

            <?php endif; ?>

        </div>

    </div>

</main>


<!-- ==============================================================
     BOOTSTRAP JAVASCRIPT
     ============================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>
```
