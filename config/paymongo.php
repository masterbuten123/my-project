<?php
// NEVER commit real keys to git. Use env vars in prod.
define('PM_SECRET', getenv('PAYMONGO_SECRET') ?: 'sk_test_uYvjaghuA6TZpDsFmEUzjF9T');
define('PM_PUBLIC', getenv('PAYMONGO_PUBLIC') ?: 'pk_test_V1kh1PPcMtfon7i4WrWMBQK9');

function pm_headers() {
  return [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . base64_encode(PM_SECRET . ':')
  ];
}

function pm_post($url, $payload) {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => pm_headers(),
    CURLOPT_POSTFIELDS => json_encode($payload)
  ]);
  $res = curl_exec($ch);
  if ($res === false) throw new Exception('Curl error: '.curl_error($ch));
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($code >= 400) throw new Exception("PayMongo error ($code): $res");
  return json_decode($res, true);
}
