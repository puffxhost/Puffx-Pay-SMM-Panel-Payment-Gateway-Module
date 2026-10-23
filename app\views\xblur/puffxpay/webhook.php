<?php
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Only POST requests are allowed.');
}

$apiToken = trim((string) getenv('PUFFXPAY_USER_TOKEN'));
$orderId = isset($_POST['order_id']) && is_string($_POST['order_id']) ? trim($_POST['order_id']) : '';
$user = isset($_POST['remark1']) && is_string($_POST['remark1']) ? trim($_POST['remark1']) : '';
$signature = isset($_POST['remark2']) && is_string($_POST['remark2']) ? trim($_POST['remark2']) : '';
$callbackAmount = isset($_POST['amount']) && is_string($_POST['amount']) ? trim($_POST['amount']) : '';
$callbackStatus = isset($_POST['status']) && is_string($_POST['status']) ? strtoupper(trim($_POST['status'])) : '';

if ($apiToken === '' || !preg_match('/^SMM[0-9a-f]{24}$/', $orderId)
    || $user === '' || !is_numeric($callbackAmount) || !in_array($callbackStatus, ['SUCCESS', 'COMPLETED'], true)) {
    http_response_code(400);
    exit('Invalid payment notification.');
}

$amount = number_format((float) $callbackAmount, 2, '.', '');
$expectedSignature = hash_hmac('sha256', $orderId . '|' . $user . '|' . $amount, $apiToken);
if (!hash_equals($expectedSignature, $signature)) {
    http_response_code(403);
    exit('Payment notification could not be authenticated.');
}

// Verify the transaction with the provider; callback data alone cannot credit balance.
$curl = curl_init('https://pay.x-server.in/api/check-order-status');
if ($curl === false) {
    http_response_code(503);
    exit('Payment verification is unavailable.');
}

curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'user_token' => $apiToken,
        'order_id' => $orderId,
    ]),
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

$verification = is_string($response) ? json_decode($response, true) : null;
$result = is_array($verification) && isset($verification['result']) && is_array($verification['result'])
    ? $verification['result']
    : [];
$verifiedOrderId = isset($result['orderId']) ? (string) $result['orderId'] : '';
$verifiedStatus = strtoupper((string) ($result['status'] ?? $result['txnStatus'] ?? ''));
$verifiedAmount = $result['amount'] ?? null;

if ($curlError || $httpCode < 200 || $httpCode >= 300 || $verifiedOrderId !== $orderId
    || !in_array($verifiedStatus, ['SUCCESS', 'COMPLETED'], true)
    || !is_numeric($verifiedAmount)
    || number_format((float) $verifiedAmount, 2, '.', '') !== $amount) {
    http_response_code(503);
    exit('Payment has not been verified.');
}

$dbHost = (string) getenv('PUFFXPAY_DB_HOST');
$dbUser = (string) getenv('PUFFXPAY_DB_USER');
$dbPassword = (string) getenv('PUFFXPAY_DB_PASSWORD');
$dbName = (string) getenv('PUFFXPAY_DB_NAME');

if ($dbHost === '' || $dbUser === '' || $dbName === '') {
    http_response_code(503);
    exit('Payment processing is not configured.');
}

$conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
if ($conn->connect_error) {
    http_response_code(503);
    exit('Payment processing is unavailable.');
}
$conn->set_charset('utf8mb4');

try {
    $conn->begin_transaction();

    // Lock this account while checking the transaction ID to make retries safe.
    $stmt = $conn->prepare('SELECT client_id, balance FROM clients WHERE username = ? FOR UPDATE');
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $client = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$client) {
        $conn->rollback();
        http_response_code(404);
        exit('Account not found.');
    }

    $stmt = $conn->prepare('SELECT t_id FROM payments WHERE t_id = ? LIMIT 1');
    $stmt->bind_param('s', $orderId);
    $stmt->execute();
    $alreadyProcessed = (bool) $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($alreadyProcessed) {
        $conn->commit();
        http_response_code(200);
        exit('Payment already processed.');
    }

    $clientId = (int) $client['client_id'];
    $newBalance = round((float) $client['balance'] + (float) $amount, 2);
    $update = $conn->prepare('UPDATE clients SET balance = ? WHERE client_id = ?');
    $update->bind_param('di', $newBalance, $clientId);
    $update->execute();
    $update->close();

    // Status 1 is the success status used by this panel's payment schema.
    $paymentMethod = 110;
    $paymentStatus = 1;
    $paymentDelivery = 2;
    $paymentMode = 'Automatic';
    $paymentNote = 'Auto payment from PuffxPay.';
    $paymentBank = 0;
    $now = date('Y-m-d H:i:s');
    $paymentIp = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

    $insert = $conn->prepare('INSERT INTO payments (
        client_id, client_balance, payment_amount, payment_method,
        payment_status, payment_delivery, payment_note,
        payment_mode, payment_create_date, payment_update_date,
        payment_ip, payment_extra, payment_bank, t_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $paymentExtra = '';
    $insert->bind_param('iddiiissssssis', $clientId, $newBalance, $amount, $paymentMethod,
        $paymentStatus, $paymentDelivery, $paymentNote, $paymentMode, $now, $now,
        $paymentIp, $paymentExtra, $paymentBank, $orderId);
    $insert->execute();
    $insert->close();

    $conn->commit();
    http_response_code(200);
    echo 'Payment verified and processed.';
} catch (Throwable $exception) {
    $conn->rollback();
    http_response_code(500);
    echo 'Payment processing failed.';
} finally {
    $conn->close();
}
