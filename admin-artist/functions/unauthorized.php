<?php
function authorize($allowed_roles = []) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Session timeout
    $timeout = 1800; // 30 minutes
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['message'] = "Session expired. Please log in again.";
        header("Location: ../index.php");
        exit();
    }

    $_SESSION['last_activity'] = time();

    // Check authentication
    if (
        !isset($_SESSION['auth']) ||
        !isset($_SESSION['auth_user']) ||
        !isset($_SESSION['role_as']) ||
        !in_array($_SESSION['role_as'], $allowed_roles)
    ) {
        $_SESSION['message'] = "Unauthorized access.";
        header("Location: ../index.php");
        exit();
    }
}
?>
