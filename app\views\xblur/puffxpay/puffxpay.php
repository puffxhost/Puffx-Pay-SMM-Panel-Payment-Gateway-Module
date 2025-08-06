<?php
// API URL
$api_url = 'https://pay.x-server.in/api/create-order';
$current_url = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);

$amount = isset($_GET['amount']) ? $_GET['amount'] : '';
$user = isset($_GET['user']) ? $_GET['user'] : '';

// Form-encoded payload data
$post_data = [
    'customer_mobile' => '8145344963',
    'user_token' => 'ee2a9cd47872e3109434c8c57616bc2f',
    'amount' => $amount,
    'order_id' => 'SMM' . time(),
    'redirect_url' => 'https://'.$current_url,
    'current_url' => $current_url,
    'remark1' => $user,
    'remark2' => 'testremark2',
    'route' => '2'
];

// Initialize cURL session
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    // Decode JSON response
    $response_data = json_decode($response, true);

    if ($response_data && isset($response_data['status']) && $response_data['status'] === true) {
        $payment_url = $response_data['result']['payment_url'];

        // Redirect to payment URL
        header("Location: $payment_url");
        exit;
    } else {
        echo "Failed to create order. Response: " . $response;
    }
}

curl_close($ch);
?>
