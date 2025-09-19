<?php
session_start();
include('config/dbcon.php');
include('functions/myfunctions.php');

// Redirect if no success message
if (!isset($_SESSION['success_message'])) {
    header("Location: entertainment-request.php");
    exit();
}

$success_message = $_SESSION['success_message'];
unset($_SESSION['success_message']); // Clear message after showing

// Fetch last booking for current user
$account_id = $_SESSION['auth_user']['account_id'] ?? 0;
$booking = null;

if ($account_id) {
    $res = mysqli_query($con, "
        SELECT b.*, a.name AS artist_name 
        FROM bookings b
        LEFT JOIN accounts a ON a.account_id = b.artist_id
        WHERE b.account_id = '$account_id'
        ORDER BY b.booking_id DESC
        LIMIT 1
    ");

    if ($res && mysqli_num_rows($res) > 0) {
        $booking = mysqli_fetch_assoc($res);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">

    <!-- Success Message -->
    <div class="text-center mb-5">
        <h2 class="text-success"><i class="fas fa-check-circle"></i> Booking Confirmed!</h2>
        <p><?= htmlspecialchars($success_message) ?></p>
    </div>

    <!-- Booking Details -->
    <?php if ($booking): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                Booking Details
            </div>
            <div class="card-body">
                <p><strong>Booking ID:</strong> <?= $booking['booking_id'] ?></p>
                <p><strong>Artist:</strong> <?= htmlspecialchars($booking['artist_name']) ?></p>
                <p><strong>Event Type:</strong> <?= htmlspecialchars($booking['event_type'] ?? 'N/A') ?></p>
                <p><strong>Date & Time:</strong> <?= htmlspecialchars($booking['booking_start']) ?></p>
                <p><strong>Attendees:</strong> <?= htmlspecialchars($booking['attendees']) ?></p>
                <p><strong>Special Request:</strong> <?= htmlspecialchars($booking['additional_notes']) ?></p>
                <p><strong>Status:</strong> <?= ucfirst($booking['status']) ?></p>
                <p><strong>Payment Status:</strong> <?= ucfirst($booking['payment_status']) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="text-center">
        <a href="index.php" class="btn btn-primary me-2">
            <i class="fas fa-home"></i> Go to Homepage
        </a>
        <a href="entertainment-request.php" class="btn btn-secondary">
            <i class="fas fa-plus-circle"></i> Make Another Booking
        </a>
    </div>

</div>
</body>
</html>
