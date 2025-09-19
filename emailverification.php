<?php
session_start();
require 'config/dbcon.php';

// Get email from URL
$email = trim($_GET['email'] ?? '');

// If no email provided, redirect
if (empty($email)) {
    $_SESSION['otp_alert'] = ['type' => 'error', 'message' => 'Invalid access. Email not specified.'];
    header("Location: index.php");
    exit();
}

// Retrieve alert message if exists
$alert = $_SESSION['otp_alert'] ?? null;
unset($_SESSION['otp_alert']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">
                    <h4 class="mb-3 text-center">Email Verification</h4>
                    <p class="text-muted text-center">Enter the 6-digit OTP sent to your email: <b><?= htmlspecialchars($email) ?></b></p>

                    <form action="authcode.php" method="POST">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">OTP Code</label>
                            <input type="text" name="otp" class="form-control" maxlength="6" required placeholder="Enter OTP">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="verify_otp_btn" class="btn btn-primary">Verify</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <form action="authcode.php" method="POST">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                            <button type="submit" name="resend_otp_btn" class="btn btn-link">Resend OTP</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($alert): ?>
<script>
Swal.fire({
    icon: "<?= $alert['type'] ?>",
    text: "<?= $alert['message'] ?>",
    confirmButtonColor: "#3085d6"
});
</script>
<?php endif; ?>
<?php if (isset($_SESSION['otp_alert'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
    icon: '<?= $_SESSION['otp_alert']['type'] ?>',
    title: '<?= ($_SESSION['otp_alert']['type'] === "success") ? "Success" : "Error" ?>',
    text: '<?= $_SESSION['otp_alert']['message'] ?>',
    timer: 3000, // Auto close after 3 seconds
    timerProgressBar: true,
    showConfirmButton: false
}).then(() => {
    if ('<?= $_SESSION['otp_alert']['type'] ?>' === 'success') {
        window.location.href = "index.php";
    }
});
</script>
<?php unset($_SESSION['otp_alert']); endif; ?>
</body>
</html>
