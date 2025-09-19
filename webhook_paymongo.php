<?php
require 'config/dbcon.php';
$raw = file_get_contents("php://input");
$event = json_decode($raw, true);

$type = $event["data"]["attributes"]["type"] ?? "";

if ($type === "checkout_session.payment.paid") {
    $cs = $event["data"]["attributes"]["data"]["attributes"] ?? [];
    $meta = $cs["metadata"] ?? [];
    $order_id = $meta["order_id"] ?? null;
    $account_id = $meta["account_id"] ?? null; // siguraduhin na sinet mo ito sa metadata nung gumawa ka ng checkout session

    if ($order_id && $account_id) {
        // Update orders table
        $stmt = $con->prepare("UPDATE orders SET payment_status='paid', order_status='processing' WHERE order_id=?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        // Update cart table: set all active items of this user to checked_out
        $stmt2 = $con->prepare("UPDATE cart SET status='checked_out' WHERE account_id=? AND status='active'");
        $stmt2->bind_param("i", $account_id);
        $stmt2->execute();
    }
}

http_response_code(200);
echo "OK";
