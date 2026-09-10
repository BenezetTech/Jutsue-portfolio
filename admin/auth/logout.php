<?php

/**
 * Administrator Logout
 * Jutsue Mekodjio Bilios Portfolio CMS
 */

require_once __DIR__ . '/../../includes/auth/auth.php';

logoutAdmin();

header('Location: login.php');
exit;