<?php
session_start();
include('config/dbcon.php');

if (!isset($_SESSION['auth_user']['account_id'])) {
    header("Location: index.php");
    exit();
}

$account_id = $_SESSION['auth_user']['account_id'];

// Validate input
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_id'])) {
    $plan_id = intval($_POST['plan_id']);

    // Fetch plan details
    $stmt = $con->prepare("SELECT * FROM subscription_plans WHERE plan_id=? AND status='active'");
    $stmt->bind_param("i", $plan_id);
    $stmt->execute();
    $plan = $stmt->get_result()->fetch_assoc();

    if (!$plan) {
        echo "<script>alert('Invalid or inactive plan selected.'); window.location.href='apply_artist.php';</script>";
        exit();
    }

    // Calculate subscription dates
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime("+{$plan['duration_days']} days"));

    // Insert into subscriptions
    $stmt = $con->prepare("INSERT INTO subscriptions (account_id, plan_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')");
    $stmt->bind_param("iiss", $account_id, $plan_id, $start_date, $end_date);
    $stmt->execute();

    // If FREE PLAN (₱0.00) → auto-upgrade to artist
    if ($plan['price'] == 0.00) {
        $stmt = $con->prepare("UPDATE accounts SET role='artist', status='active' WHERE account_id=?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();

        echo "<script>alert('You are now registered as an Artist (Free Plan).'); window.location.href='my-profile.php';</script>";
        exit();
    }

    // Else → redirect to payment page
    header("Location: payment.php?subscription_id=" . $con->insert_id);
    exit();
} else {
    header("Location: apply_artist.php");
    exit();
}
