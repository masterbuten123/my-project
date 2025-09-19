<?php
include_once 'config/dbcon.php';

$secret_key = "sk_test_uYvjaghuA6TZpDsFmEUzjF9T";
$url = "https://api.paymongo.com/v1/payment_intents";

$data = [
    "data" => [
        "attributes" => [
            "amount" => 50000, // 500 PHP
            "currency" => "PHP",
            "payment_method_allowed" => ["card", "gcash", "grab_pay"],
            "capture_type" => "automatic"
        ]
    ]
];

$payload = json_encode($data);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Basic " . base64_encode($secret_key . ":")
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$response = curl_exec($ch);

if(curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
    exit;
}
curl_close($ch);

$result = json_decode($response, true);

if(isset($result["data"]["id"])) {
    echo "Payment Intent Created! ID: " . $result["data"]["id"];
    echo "<br><pre>" . print_r($result, true) . "</pre>";
} else {
    echo "Error creating Payment Intent:<br>";
    echo "<pre>" . print_r($result, true) . "</pre>";
}
