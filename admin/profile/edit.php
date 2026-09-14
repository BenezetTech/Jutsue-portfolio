```php
<?php

/**
 * Edit Professional Profile
 * Jutsue Mekodjio Bilios Portfolio CMS
 *
 * Authenticated administrators can edit the profile.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth/auth.php';

requireLogin();

$error = '';

/*
|--------------------------------------------------------------------------
| Upload Configuration
|--------------------------------------------------------------------------
*/

$uploadDirectory = __DIR__ . '/../../assets/uploads/';

/*
|--------------------------------------------------------------------------
| Load Existing Profile
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
            cv_file
        FROM profile
        ORDER BY id ASC
        LIMIT 1
    ");

    $profile = $stmt->fetch();

    /*
    |--------------------------------------------------------------------------
    | Create Empty Profile Structure If None Exists
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
            'cv_file' => ''
        ];
    }

} catch (PDOException $e) {

    error_log(
        'Profile edit load error: ' . $e->getMessage()
    );

    exit('Unable to load profile information.');
}

/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | Collect Text Fields
    |--------------------------------------------------------------------------
    */

    $fullName = trim($_POST['full_name'] ?? '');

    $professionalTitle = trim(
        $_POST['professional_title'] ?? ''
    );

    $headline = trim(
        $_POST['headline'] ?? ''
    );

    $bio = trim(
        $_POST['bio'] ?? ''
    );

    $location = trim(
        $_POST['location'] ?? ''
    );

    $phonePrimary = trim(
        $_POST['phone_primary'] ?? ''
    );

    $phoneSecondary = trim(
        $_POST['phone_secondary'] ?? ''
    );

    $emailPrimary = trim(
        $_POST['email_primary'] ?? ''
    );

    $emailSecondary = trim(
        $_POST['email_secondary'] ?? ''
    );

    $linkedinUrl = trim(
        $_POST['linkedin_url'] ?? ''
    );

    $websiteUrl = trim(
        $_POST['website_url'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | Keep Existing Files By Default
    |--------------------------------------------------------------------------
    */

    $profilePhoto = $profile['profile_photo'];
    $cvFile = $profile['cv_file'];

    $newProfilePhoto = null;
    $newCvFile = null;

    /*
    |--------------------------------------------------------------------------
    | Basic Text Validation
    |--------------------------------------------------------------------------
    */

    if ($fullName === '') {

        $error = 'Full name is required.';

    } elseif ($professionalTitle === '') {

        $error = 'Professional title is required.';

    } elseif (
        $emailPrimary !== '' &&
        !filter_var($emailPrimary, FILTER_VALIDATE_EMAIL)
    ) {

        $error = 'Please enter a valid primary email address.';

    } elseif (
        $emailSecondary !== '' &&
        !filter_var($emailSecondary, FILTER_VALIDATE_EMAIL)
    ) {

        $error = 'Please enter a valid secondary email address.';

    } elseif (
        $linkedinUrl !== '' &&
        !filter_var($linkedinUrl, FILTER_VALIDATE_URL)
    ) {

        $error = 'Please enter a valid LinkedIn URL.';

    } elseif (
        $websiteUrl !== '' &&
        !filter_var($websiteUrl, FILTER_VALIDATE_URL)
    ) {

        $error = 'Please enter a valid website URL.';

    }

    /*
    |--------------------------------------------------------------------------
    | Make Sure Upload Directory Exists
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        if (!is_dir($uploadDirectory)) {

            if (!mkdir($uploadDirectory, 0755, true)) {

                $error =
                    'Unable to prepare the upload directory.';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Profile Photo
    |--------------------------------------------------------------------------
    */

    if (
        $error === '' &&
        isset($_FILES['profile_photo']) &&
        $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $photo = $_FILES['profile_photo'];

        if ($photo['error'] !== UPLOAD_ERR_OK) {

            $error =
                'There was a problem uploading the profile photo.';

        } elseif ($photo['size'] > 5 * 1024 * 1024) {

            $error =
                'Profile photo must not exceed 5 MB.';

        } else {

            $allowedPhotoMimeTypes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            $finfo = new finfo(FILEINFO_MIME_TYPE);

            $photoMimeType = $finfo->file(
                $photo['tmp_name']
            );

            if (
                !isset(
                    $allowedPhotoMimeTypes[$photoMimeType]
                )
            ) {

                $error =
                    'Invalid profile photo type. Please use JPG, PNG, or WEBP.';

            } elseif (!getimagesize($photo['tmp_name'])) {

                $error =
                    'The selected profile photo is not a valid image.';

            } else {

                $photoExtension =
                    $allowedPhotoMimeTypes[$photoMimeType];

                $newProfilePhoto =
                    'profile_' .
                    bin2hex(random_bytes(16)) .
                    '.' .
                    $photoExtension;

                $photoDestination =
                    $uploadDirectory .
                    $newProfilePhoto;

                if (
                    !move_uploaded_file(
                        $photo['tmp_name'],
                        $photoDestination
                    )
                ) {

                    $error =
                        'Unable to save the uploaded profile photo.';

                    $newProfilePhoto = null;
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validate CV
    |--------------------------------------------------------------------------
    */

    if (
        $error === '' &&
        isset($_FILES['cv_file']) &&
        $_FILES['cv_file']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $cv = $_FILES['cv_file'];

        if ($cv['error'] !== UPLOAD_ERR_OK) {

            $error =
                'There was a problem uploading the CV.';

        } elseif ($cv['size'] > 10 * 1024 * 1024) {

            $error =
                'CV must not exceed 10 MB.';

        } else {

            $allowedCvMimeTypes = [
                'application/pdf' => 'pdf'
            ];

            $finfo = new finfo(FILEINFO_MIME_TYPE);

            $cvMimeType = $finfo->file(
                $cv['tmp_name']
            );

            if (
                !isset(
                    $allowedCvMimeTypes[$cvMimeType]
                )
            ) {

                $error =
                    'Invalid CV file. Only PDF files are allowed.';

            } else {

                $cvExtension =
                    $allowedCvMimeTypes[$cvMimeType];

                $newCvFile =
                    'cv_' .
                    bin2hex(random_bytes(16)) .
                    '.' .
                    $cvExtension;

                $cvDestination =
                    $uploadDirectory .
                    $newCvFile;

                if (
                    !move_uploaded_file(
                        $cv['tmp_name'],
                        $cvDestination
                    )
                ) {

                    $error =
                        'Unable to save the uploaded CV.';

                    $newCvFile = null;
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Save Profile
    |--------------------------------------------------------------------------
    */

    if ($error === '') {

        /*
        |--------------------------------------------------------------------------
        | Use Newly Uploaded Files When Available
        |--------------------------------------------------------------------------
        */

        if ($newProfilePhoto !== null) {

            $profilePhoto = $newProfilePhoto;
        }

        if ($newCvFile !== null) {

            $cvFile = $newCvFile;
        }

        try {

            $pdo->beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | Update Existing Profile
            |--------------------------------------------------------------------------
            */

            if (!empty($profile['id'])) {

                $stmt = $pdo->prepare("
                    UPDATE profile
                    SET
                        full_name = :full_name,
                        professional_title = :professional_title,
                        headline = :headline,
                        bio = :bio,
                        location = :location,
                        phone_primary = :phone_primary,
                        phone_secondary = :phone_secondary,
                        email_primary = :email_primary,
                        email_secondary = :email_secondary,
                        linkedin_url = :linkedin_url,
                        website_url = :website_url,
                        profile_photo = :profile_photo,
                        cv_file = :cv_file
                    WHERE id = :id
                ");

                $stmt->execute([
                    'full_name' => $fullName,
                    'professional_title' => $professionalTitle,
                    'headline' => $headline,
                    'bio' => $bio,
                    'location' => $location,
                    'phone_primary' => $phonePrimary,
                    'phone_secondary' => $phoneSecondary,
                    'email_primary' => $emailPrimary,
                    'email_secondary' => $emailSecondary,
                    'linkedin_url' => $linkedinUrl,
                    'website_url' => $websiteUrl,
                    'profile_photo' => $profilePhoto,
                    'cv_file' => $cvFile,
                    'id' => $profile['id']
                ]);

            } else {

                /*
                |--------------------------------------------------------------------------
                | Create First Profile Record
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    INSERT INTO profile (
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
                        cv_file
                    )
                    VALUES (
                        :full_name,
                        :professional_title,
                        :headline,
                        :bio,
                        :location,
                        :phone_primary,
                        :phone_secondary,
                        :email_primary,
                        :email_secondary,
                        :linkedin_url,
                        :website_url,
                        :profile_photo,
                        :cv_file
                    )
                ");

                $stmt->execute([
                    'full_name' => $fullName,
                    'professional_title' => $professionalTitle,
                    'headline' => $headline,
                    'bio' => $bio,
                    'location' => $location,
                    'phone_primary' => $phonePrimary,
                    'phone_secondary' => $phoneSecondary,
                    'email_primary' => $emailPrimary,
                    'email_secondary' => $emailSecondary,
                    'linkedin_url' => $linkedinUrl,
                    'website_url' => $websiteUrl,
                    'profile_photo' => $profilePhoto,
                    'cv_file' => $cvFile
                ]);
            }

            $pdo->commit();

            /*
            |--------------------------------------------------------------------------
            | Delete Old Files After Successful Database Save
            |--------------------------------------------------------------------------
            */

            if (
                $newProfilePhoto !== null &&
                !empty($profile['profile_photo'])
            ) {

                $oldPhoto =
                    $uploadDirectory .
                    basename($profile['profile_photo']);

                if (
                    is_file($oldPhoto) &&
                    $oldPhoto !==
                    $uploadDirectory . $newProfilePhoto
                ) {

                    unlink($oldPhoto);
                }
            }

            if (
                $newCvFile !== null &&
                !empty($profile['cv_file'])
            ) {

                $oldCv =
                    $uploadDirectory .
                    basename($profile['cv_file']);

                if (
                    is_file($oldCv) &&
                    $oldCv !==
                    $uploadDirectory . $newCvFile
                ) {

                    unlink($oldCv);
                }
            }

            header('Location: index.php?updated=1');
            exit;

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            /*
            |--------------------------------------------------------------------------
            | Remove Newly Uploaded Files If Database Save Failed
            |--------------------------------------------------------------------------
            */

            if ($newProfilePhoto !== null) {

                $newPhotoPath =
                    $uploadDirectory .
                    $newProfilePhoto;

                if (is_file($newPhotoPath)) {
                    unlink($newPhotoPath);
                }
            }

            if ($newCvFile !== null) {

                $newCvPath =
                    $uploadDirectory .
                    $newCvFile;

                if (is_file($newCvPath)) {
                    unlink($newCvPath);
                }
            }

            error_log(
                'Profile save error: ' .
                $e->getMessage()
            );

            $error =
                'Unable to save the professional profile.';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Preserve Entered Values After Validation Error
    |--------------------------------------------------------------------------
    */

    $profile['full_name'] =
        $fullName;

    $profile['professional_title'] =
        $professionalTitle;

    $profile['headline'] =
        $headline;

    $profile['bio'] =
        $bio;

    $profile['location'] =
        $location;

    $profile['phone_primary'] =
        $phonePrimary;

    $profile['phone_secondary'] =
        $phoneSecondary;

    $profile['email_primary'] =
        $emailPrimary;

    $profile['email_secondary'] =
        $emailSecondary;

    $profile['linkedin_url'] =
        $linkedinUrl;

    $profile['website_url'] =
        $websiteUrl;

    /*
    |--------------------------------------------------------------------------
    | Preserve Uploaded Filenames If Successfully Staged
    |--------------------------------------------------------------------------
    */

    $profile['profile_photo'] =
        $profilePhoto;

    $profile['cv_file'] =
        $cvFile;
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
        Edit Profile | Portfolio CMS
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
                        Edit Professional Profile
                    </h1>

                    <p class="text-muted mb-4">
                        Update the professional information displayed throughout the portfolio.
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


                    <form
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <!-- ==================================================
                             BASIC INFORMATION
                             ================================================== -->

                        <h2 class="h5 fw-bold mb-3">
                            Basic Information
                        </h2>

                        <div class="row g-3">

                            <div class="col-md-6">

                                <label
                                    for="full_name"
                                    class="form-label"
                                >
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="full_name"
                                    name="full_name"
                                    value="<?= htmlspecialchars(
                                        $profile['full_name'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    required
                                >

                            </div>


                            <div class="col-md-6">

                                <label
                                    for="professional_title"
                                    class="form-label"
                                >
                                    Professional Title
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="professional_title"
                                    name="professional_title"
                                    value="<?= htmlspecialchars(
                                        $profile['professional_title'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    placeholder="IT Specialist · Health Data Analytics · Digital Entrepreneur"
                                    required
                                >

                            </div>


                            <div class="col-12">

                                <label
                                    for="headline"
                                    class="form-label"
                                >
                                    Professional Headline
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="headline"
                                    name="headline"
                                    value="<?= htmlspecialchars(
                                        $profile['headline'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    placeholder="Building Technology, Advancing Digital Health, and Creating Impact"
                                >

                            </div>


                            <div class="col-12">

                                <label
                                    for="bio"
                                    class="form-label"
                                >
                                    Professional Biography
                                </label>

                                <textarea
                                    class="form-control"
                                    id="bio"
                                    name="bio"
                                    rows="8"
                                    placeholder="Enter the professional biography..."
                                ><?= htmlspecialchars(
                                    $profile['bio'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?></textarea>

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
                                        $profile['location'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    placeholder="Yaoundé, Cameroon"
                                >

                            </div>

                        </div>


                        <hr class="my-4">


                        <!-- ==================================================
                             CONTACT INFORMATION
                             ================================================== -->

                        <h2 class="h5 fw-bold mb-3">
                            Contact Information
                        </h2>

                        <div class="row g-3">

                            <div class="col-md-6">

                                <label
                                    for="phone_primary"
                                    class="form-label"
                                >
                                    Primary Phone
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="phone_primary"
                                    name="phone_primary"
                                    value="<?= htmlspecialchars(
                                        $profile['phone_primary'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                            </div>


                            <div class="col-md-6">

                                <label
                                    for="phone_secondary"
                                    class="form-label"
                                >
                                    Secondary Phone
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="phone_secondary"
                                    name="phone_secondary"
                                    value="<?= htmlspecialchars(
                                        $profile['phone_secondary'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                            </div>


                            <div class="col-md-6">

                                <label
                                    for="email_primary"
                                    class="form-label"
                                >
                                    Primary Email
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="email_primary"
                                    name="email_primary"
                                    value="<?= htmlspecialchars(
                                        $profile['email_primary'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                            </div>


                            <div class="col-md-6">

                                <label
                                    for="email_secondary"
                                    class="form-label"
                                >
                                    Secondary Email
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="email_secondary"
                                    name="email_secondary"
                                    value="<?= htmlspecialchars(
                                        $profile['email_secondary'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                >

                            </div>

                        </div>


                        <hr class="my-4">


                        <!-- ==================================================
                             ONLINE PROFILES
                             ================================================== -->

                        <h2 class="h5 fw-bold mb-3">
                            Online Profiles
                        </h2>

                        <div class="row g-3">

                            <div class="col-md-6">

                                <label
                                    for="linkedin_url"
                                    class="form-label"
                                >
                                    LinkedIn URL
                                </label>

                                <input
                                    type="url"
                                    class="form-control"
                                    id="linkedin_url"
                                    name="linkedin_url"
                                    value="<?= htmlspecialchars(
                                        $profile['linkedin_url'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    placeholder="https://www.linkedin.com/in/..."
                                >

                            </div>


                            <div class="col-md-6">

                                <label
                                    for="website_url"
                                    class="form-label"
                                >
                                    Website URL
                                </label>

                                <input
                                    type="url"
                                    class="form-control"
                                    id="website_url"
                                    name="website_url"
                                    value="<?= htmlspecialchars(
                                        $profile['website_url'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    placeholder="https://..."
                                >

                            </div>

                        </div>


                        <hr class="my-4">


                        <!-- ==================================================
                             PROFILE PHOTO
                             ================================================== -->

                        <h2 class="h5 fw-bold mb-3">
                            Profile Photo
                        </h2>

                        <?php if (!empty($profile['profile_photo'])): ?>

                            <div class="mb-3">

                                <img
                                    src="../../assets/uploads/<?= htmlspecialchars(
                                        $profile['profile_photo'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    alt="Current Profile Photo"
                                    class="rounded-circle shadow-sm"
                                    style="
                                        width: 130px;
                                        height: 130px;
                                        object-fit: cover;
                                    "
                                >

                            </div>

                        <?php endif; ?>


                        <div class="mb-4">

                            <label
                                for="profile_photo"
                                class="form-label"
                            >
                                <?= !empty($profile['profile_photo'])
                                    ? 'Replace Profile Photo'
                                    : 'Upload Profile Photo' ?>
                            </label>

                            <input
                                type="file"
                                class="form-control"
                                id="profile_photo"
                                name="profile_photo"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            >

                            <div class="form-text">
                                JPG, PNG or WEBP. Maximum size: 5 MB.
                            </div>

                        </div>


                        <!-- ==================================================
                             CV
                             ================================================== -->

                        <h2 class="h5 fw-bold mb-3">
                            Curriculum Vitae
                        </h2>

                        <?php if (!empty($profile['cv_file'])): ?>

                            <div class="mb-3">

                                <a
                                    href="../../assets/uploads/<?= htmlspecialchars(
                                        $profile['cv_file'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-outline-dark btn-sm"
                                >
                                    View Current CV
                                </a>

                            </div>

                        <?php endif; ?>


                        <div class="mb-4">

                            <label
                                for="cv_file"
                                class="form-label"
                            >
                                <?= !empty($profile['cv_file'])
                                    ? 'Replace CV'
                                    : 'Upload CV' ?>
                            </label>

                            <input
                                type="file"
                                class="form-control"
                                id="cv_file"
                                name="cv_file"
                                accept=".pdf,application/pdf"
                            >

                            <div class="form-text">
                                PDF only. Maximum size: 10 MB.
                            </div>

                        </div>


                        <!-- ==================================================
                             SECURITY NOTE
                             ================================================== -->

                        <div class="alert alert-info">

                            <strong>Upload Security:</strong>

                            Only supported image formats and PDF documents
                            are accepted. Uploaded filenames are replaced
                            with secure generated filenames.

                        </div>


                        <!-- ==================================================
                             ACTIONS
                             ================================================== -->

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
                                Save Profile
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</main>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>
```
