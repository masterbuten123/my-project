<?php
session_start();

// Retry limit logic
if (!isset($_SESSION['verify_attempts'])) {
    $_SESSION['verify_attempts'] = 0;
} elseif ($_SESSION['verify_attempts'] >= 5) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => "Too many failed attempts. Please request a new code."];
    header("Location: forgot-password.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Code</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .strength {
            height: 8px;
            margin-top: 4px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-danger text-white text-center">
                <h4>Verify Reset Code</h4>
            </div>
            <div class="card-body">

                <form action="authcode.php" method="POST">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label">Enter OTP</label>
                        <input type="text" name="otp" class="form-control" required placeholder="6-digit code">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="Enter new password">
                        <div id="password-strength" class="progress strength mt-2">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required placeholder="Re-enter password">
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="reset_password_btn" class="btn btn-danger">Reset Password</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const password = document.getElementById('new_password');
    const strengthBar = document.querySelector('.progress-bar');

    password.addEventListener('input', function () {
        const value = password.value;
        let strength = 0;

        if (value.length >= 6) strength += 25;
        if (/[A-Z]/.test(value)) strength += 25;
        if (/[0-9]/.test(value)) strength += 25;
        if (/[^A-Za-z0-9]/.test(value)) strength += 25;

        strengthBar.style.width = strength + '%';
        strengthBar.className = 'progress-bar';

        if (strength < 50) {
            strengthBar.classList.add('bg-danger');
        } else if (strength < 75) {
            strengthBar.classList.add('bg-warning');
        } else {
            strengthBar.classList.add('bg-success');
        }
    });
</script>
</body>
</html>
