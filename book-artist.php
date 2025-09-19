<?php
include('config/dbcon.php');
session_start();

if($_SERVER['REQUEST_METHOD']=='POST'){
    if(!isset($_SESSION['auth_user'])){
        $_SESSION['message']="Please log in to book an artist.";
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['auth_user']['account_id'];
    $artist_id = mysqli_real_escape_string($con,$_POST['artist_id']);
    $venue = mysqli_real_escape_string($con,$_POST['venue'] ?? '');
    $message = mysqli_real_escape_string($con,$_POST['message'] ?? '');
    $booking_date = mysqli_real_escape_string($con,$_POST['booking_date'] . ' 00:00:00');

    // Verify artist exists
    $artistQuery = mysqli_query($con,"SELECT * FROM accounts WHERE account_id='$artist_id' AND role='artist'");
    if(mysqli_num_rows($artistQuery)==0){
        header("Location: artist.php?booking=invalid_artist");
        exit();
    }
    $artist = mysqli_fetch_assoc($artistQuery);
    $total_price = $artist['price_per_hour'] ?? 0;

    $currentDate = date('Y-m-d 00:00:00');
    if($booking_date < $currentDate){
        header("Location: artist.php?booking=invalid_date");
        exit();
    }

    // Check double booking
    $checkBooking = mysqli_query($con,"SELECT * FROM bookings WHERE artist_id='$artist_id' AND booking_date='$booking_date' AND status!='cancelled'");
    if(mysqli_num_rows($checkBooking) > 0){
        header("Location: artist.php?booking=double_booking");
        exit();
    }

    $insert = "INSERT INTO bookings (user_id, artist_id, total_price, booking_date, status)
               VALUES ('$user_id','$artist_id','$total_price','$booking_date','pending')";
    if(mysqli_query($con,$insert)){
        header("Location: artist.php?booking=success");
        exit();
    } else {
        header("Location: artist.php?booking=failed");
        exit();
    }
}
?>
