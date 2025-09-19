<?php
require 'config/paymongo.php';

// You’ll need a customer id and a saved payment method id (from initial checkout / vaulting).
$customer_id = $_POST['customer_id'];
$plan_id     = $_POST['plan_id'];

$payload = [
  'data' => [
    'attributes' => [
      'customer_id' => $customer_id,
      'plan_id'     => $plan_id,
      // Optional trial, start_date, etc. per docs.
    ]
  ]
];

$resp = pm_post('https://api.paymongo.com/v1/subscriptions', $payload);
echo 'Subscription: ' . htmlspecialchars($resp['data']['id']);
