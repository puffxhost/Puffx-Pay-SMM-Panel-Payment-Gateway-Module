<?php
// Configure PUFFXPAY_USER_TOKEN on the server; never commit merchant credentials.
$apiToken = trim((string) getenv('PUFFXPAY_USER_TOKEN'));
if ($apiToken === '') {
    http_response_code(503);
    exit('Payment gateway is not configured.');
}

$currentUrl = preg_replace('/^www\./', '', (string) ($_SERVER['HTTP_HOST'] ?? ''));
$user = isset($_GET['user']) ? trim((string) $_GET['user']) : '';
$amountInput = isset($_GET['amount']) ? trim((string) $_GET['amount']) : '';

if ($currentUrl === '' || $user === '' || !is_numeric($amountInput)) {
    http_response_code(400);
    exit('Invalid payment request.');
}

$amount = number_format((float) $amountInput, 2, '.', '');
if ((float) $amount < 10 || (float) $amount > 100000) {
    http_response_code(400);
    exit('Amount is outside the configured payment limits.');
}

try {
    $orderId = 'SMM' . bin2hex(random_bytes(12));
} catch (Throwable $exception) {
    http_response_code(500);
    exit('Unable to create a payment order.');
}

// The HMAC binds the order, account, and amount so callback fields cannot be
// changed to credit another account or a different amount.
$callbackSignature = hash_hmac('sha256', $orderId . '|' . $user . '|' . $amount, $apiToken);
$payload = [
    'customer_mobile' => '8145344963',
    'user_token' => $apiToken,
    'amount' => $amount,
    'order_id' => $orderId,
    'redirect_url' => 'https://' . $currentUrl,
    'current_url' => $currentUrl,
    'remark1' => $user,
    'remark2' => $callbackSignature,
    'route' => '2',
];

$curl = curl_init('https://pay.x-server.in/api/create-order');
if ($curl === false) {
    http_response_code(503);
    exit('Payment gateway is unavailable.');
}

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($payload),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

$response = curl_exec($curl);
$curlError = curl_errno($curl);
$httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$data = is_string($response) ? json_decode($response, true) : null;
$paymentUrl = is_array($data) ? ($data['result']['payment_url'] ?? '') : '';

if ($curlError || $httpCode < 200 || $httpCode >= 300
    || empty($data['status']) || !is_string($paymentUrl)
    || !filter_var($paymentUrl, FILTER_VALIDATE_URL)
    || parse_url($paymentUrl, PHP_URL_SCHEME) !== 'https') {
    http_response_code(502);
    exit('Unable to create a payment order.');
}

header('Location: ' . $paymentUrl, true, 303);
exit;
