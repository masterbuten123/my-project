<?php
session_start();
require 'config/dbcon.php';
require 'config/paymongo.php';

// --- Get logged-in user ---
if (isset($_SESSION['auth_user']['account_id'])) {
    $account_id = $_SESSION['auth_user']['account_id'];
} elseif (isset($_SESSION['new_user_id'])) {
    $account_id = $_SESSION['new_user_id'];
} else {
    die("Please log in or register first.");
}

// --- Get POST data ---
$amount = (float)($_POST['amount'] ?? 0);
$name   = $_POST['name'] ?? 'Customer';
$email  = $_POST['email'] ?? 'noemail@example.com';
$phone  = $_POST['phone'] ?? '';
$mode   = $_POST['mode'] ?? null;

// --- Validate amount ---
if ($amount <= 0) die("Invalid amount");

// --- Map frontend payment methods to PayMongo ---
$mode_map = [
    'gcash' => 'gcash',
    'bank'  => 'card',   // PayMongo only accepts 'card' or 'gcash'
    'cod'   => null       // COD cannot go through PayMongo
];

$selected_mode = $mode_map[$mode] ?? null;

// If COD, skip PayMongo and mark order as pending for manual processing
if ($selected_mode === null && $mode === 'cod') {
    $tracking_no = "MUSEO-ORD-" . date("Ymd") . "-" . strtoupper(substr(uniqid(), -5));
    $stmt = $con->prepare("INSERT INTO orders (account_id,total_amount,payment_status,order_status,tracking_no,payment_method) VALUES (?,?,?,?,?,?)");
    $status = 'pending';
    $order_status = 'pending';
    $stmt->bind_param("idsdss", $account_id, $amount, $status, $order_status, $tracking_no, $mode);
    $stmt->execute();
    $stmt->close();
    header("Location: order_success.php?order_id=" . $con->insert_id);
    exit();
}

if (!$selected_mode) die("Invalid payment method selected.");

// --- Generate tracking number ---
$tracking_no = "MUSEO-ORD-" . date("Ymd") . "-" . strtoupper(substr(uniqid(), -5));

// --- Insert order into DB ---
$status = 'pending';
$order_status = 'pending';
$stmt = $con->prepare("INSERT INTO orders (account_id,total_amount,payment_status,order_status,tracking_no,payment_method) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("idsdss", $account_id, $amount, $status, $order_status, $tracking_no, $mode);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// --- Prepare line items ---
$line_items = [];

// Products
if (!empty($_POST['item_ids'])) {
    foreach ($_POST['item_ids'] as $i => $pid) {
        $qty = $_POST['quantities'][$i] ?? 1;
        $product = $con->query("SELECT price,name FROM products WHERE product_id=$pid")->fetch_assoc();
        $price = floatval($product['price'] ?? 0);
        $prod_name = $product['name'] ?? "Product #$pid";
        $line_items[] = [
            "name"     => $prod_name,
            "amount"   => intval($price * 100),
            "currency" => "PHP",
            "quantity" => intval($qty)
        ];
    }
}

// Subscriptions
if (!empty($_POST['sub_ids'])) {
    foreach ($_POST['sub_ids'] as $i => $pid) {
        $sub_name = $_POST['sub_names'][$i] ?? "Subscription #$pid";
        $price = floatval($_POST['sub_prices'][$i] ?? 0);   
        $line_items[] = [
            "name"     => $sub_name,
            "amount"   => intval($price * 100),
            "currency" => "PHP",
            "quantity" => 1
        ];
    }
}

// --- Prepare payload for PayMongo ---
$payload = [
    "data" => [
        "attributes" => [
            "send_email_receipt" => true,
            "show_line_items"    => true,
            "cancel_url"         => "http://localhost/checkout_cancelled.php",
            "success_url"        => "http://localhost/order_success.php?order_id=$order_id",
            "description"        => "Order #$order_id",
            "line_items"         => $line_items,
            "payment_method_types" => [$selected_mode],
            "billing" => [
                "name"  => $name,
                "email" => $email,
                "phone" => $phone
            ],
            "metadata" => [
                "order_id"   => $order_id,
                "account_id" => $account_id
            ]
        ]
    ]
];

// --- Send request to PayMongo ---
try {
    $resp = pm_post("https://api.paymongo.com/v1/checkout_sessions", $payload);
    $checkout_url = $resp["data"]["attributes"]["checkout_url"] ?? null;
    if ($checkout_url) {
        header("Location: $checkout_url");
        exit();
    } else {
        die("No checkout URL returned from PayMongo.");
    }
} catch (Exception $e) {
    die("PayMongo Error: " . $e->getMessage());
}
