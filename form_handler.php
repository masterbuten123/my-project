<?php
session_start();
include('config/dbcon.php');
include('functions/myfunctions.php');

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

// Send booking confirmation email
function sendBookingConfirmation($email, $name, $bookingDetails) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mikovargas5@gmail.com';
        $mail->Password   = 'dydwouieefblfyrf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        $mail->setFrom('mikovargas5@gmail.com', 'Museo Booking Confirmation');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = "Your Booking Confirmation";
        $mail->Body = "<p>Hi " . htmlspecialchars($name) . ",</p>
                       <p>Your booking has been confirmed.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}

// Handle form submission
if (!isset($_POST['submit'])) {
    $_SESSION['errors'][] = "Accessed page directly!";
    header("Location: entertainment-request.php");
    exit();
}

$form_type = $_POST['form_type'] ?? '';
if ($form_type !== 'entertainment_request') {
    $_SESSION['errors'][] = "Invalid form submission.";
    header("Location: entertainment-request.php");
    exit();
}

// 1️⃣ Collect input
$account_id       = $_SESSION['auth_user']['account_id'] ?? 0;
$artist_id        = intval($_POST['artist_id'] ?? 0);
$booking_raw      = $_POST['booking_start'] ?? '';
$duration_hours   = floatval($_POST['duration_hours'] ?? 0);
$attendees        = intval($_POST['attendees'] ?? 0);
$total_price      = floatval($_POST['budget'] ?? 0);
$additional_notes = trim($_POST['additional_notes'] ?? '');
$venue            = trim($_POST['venue'] ?? '');
$event_type       = $_POST['event_type'] ?? '';
$phone            = $_POST['phone'] ?? '';
if ($event_type === 'Other') {
    $event_type = trim($_POST['event_type_other'] ?? '');
}
$name             = trim($_POST['name'] ?? '');
$email            = trim($_POST['email'] ?? '');
$agree            = isset($_POST['agree']) ? 1 : 0;

// 2️⃣ Validate required fields
$errors = [];
if ($artist_id <= 0) $errors[] = "Artist selection is required.";
if ($duration_hours <= 0) $errors[] = "Duration must be greater than 0.";
if (empty($name)) $errors[] = "Name is required.";
if (empty($email)) $errors[] = "Email is required.";
if (!$agree) $errors[] = "You must confirm the information.";
if (empty($booking_raw)) $errors[] = "Booking date & time is required.";

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: entertainment-request.php?account_id={$artist_id}");
    exit();
}

// 3️⃣ Convert datetime-local to MySQL DATETIME
$booking_start = str_replace('T', ' ', $booking_raw) . ':00';
if (empty($booking_raw)) {
    $_SESSION['errors'][] = "Booking date & time is required.";
    header("Location: entertainment-request.php?account_id={$artist_id}");
    exit();
}
$booking_start = str_replace('T', ' ', $booking_raw) . ':00';
if (strtotime($booking_start) === false) {
    $_SESSION['errors'][] = "Invalid booking date & time.";
    header("Location: entertainment-request.php?account_id={$artist_id}");
    exit();
}


$booking_end = date('Y-m-d H:i:s', strtotime("+$duration_hours hours", strtotime($booking_start)));

// 4️⃣ Check overlapping bookings
$checkBooking = $con->prepare("
    SELECT * FROM bookings
    WHERE artist_id = ?
      AND booking_start < ?
      AND booking_end > ?
      AND status IN ('pending','accepted','confirmed')
    LIMIT 1
");
$checkBooking->bind_param("iss", $artist_id, $booking_end, $booking_start);
$checkBooking->execute();
$result = $checkBooking->get_result();
if ($result->num_rows > 0) {
$_SESSION['errors'] = ["Sorry, this artist is already booked for the selected time."];
header("Location: entertainment-request.php?account_id={$artist_id}");
exit();
}
$checkBooking->close();

// 5️⃣ Insert booking
// 5️⃣ Insert booking
$stmt = $con->prepare("
    INSERT INTO bookings
    (account_id, artist_id, total_price, phone, status, payment_status, booking_start, booking_end, duration_hours, attendees, venue, additional_notes, event_type, manager_note, created_at)
    VALUES (?, ?, ?, ?, 'pending', 'pending', ?, ?, ?, ?, ?, ?, ?, '', NOW())
");

$stmt->bind_param(
    "iidsssdisss",
    $account_id,       // i - int
    $artist_id,        // i - int
    $total_price,      // d - double
    $phone,            // s - string
    $booking_start,    // s - string DATETIME
    $booking_end,      // s - string DATETIME
    $duration_hours,   // d - double
    $attendees,        // i - int
    $venue,            // s - string
    $additional_notes, // s - string
    $event_type        // s - string
);

if (!$stmt->execute()) {
    $_SESSION['errors'] = ["Something went wrong. Please try again."];
    header("Location: entertainment-request.php?account_id={$artist_id}");
    exit();
}

// 6️⃣ Success
$booking_id = $con->insert_id;
$stmt->close();
$_SESSION['success_message'] = "Your entertainment request has been submitted successfully!";

// Optional: send email
sendBookingConfirmation($email, $name, [
    'booking_id' => $booking_id,
    'artist_name' => getArtistByIdd($artist_id)['name'],
    'booking_date' => $booking_start,
    'duration_hours' => $duration_hours,
    'attendees' => $attendees,
    'additional_notes' => $additional_notes
]);

header("Location: booking-confirmation.php?form=entertainment_request");
exit();
?>
