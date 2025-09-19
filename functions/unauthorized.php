<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is authenticated and has the admin role
if (
    !isset($_SESSION['auth']) ||
    !isset($_SESSION['auth_user']) ||
    !isset($_SESSION['role_as']) ||
    $_SESSION['role_as'] !== 'artist'
) {
    $_SESSION['message'] = "Unauthorized access. Artist only.";
    header("Location: ../index.php");
    exit();
}

// Session timeout logic
$timeout = 1800; // 30 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    $_SESSION['message'] = "Session expired. Please log in again.";
    header("Location: ../index.php");
    exit();
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// ✅ All checks passed: authenticated admin with valid session
?>
