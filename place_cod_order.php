<?php
session_start();
require 'config/dbcon.php';

if (!isset($_SESSION['auth_user']['account_id'])) {
    die("Not logged in");
}

$account_id = $_SESSION['auth_user']['account_id'];
$total_amount = (float)($_POST['amount'] ?? 0);

if ($total_amount <= 0) {
    die("Invalid amount");
}

// generate tracking number
$tracking_no = "TFUCS-ORD-" . date("Ymd") . "-" . strtoupper(substr(uniqid(), -5));

$stmt = $con->prepare("INSERT INTO orders 
    (account_id, total_amount, payment_status, order_status, tracking_no) 
    VALUES (?, ?, 'pending', 'pending', ?)");
$stmt->bind_param("ids", $account_id, $total_amount, $tracking_no);

if ($stmt->execute()) {
    header("Location: order_success.php?order_id=" . $stmt->insert_id);
    exit();
} else {
    die("DB Error: " . $stmt->error);
}
