<?php
// Check if the POST request has been made
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from POST request
    $status = $_POST['status'] ?? '';
    $order_id = $_POST['order_id'] ?? '';
    $customer_mobile = $_POST['customer_mobile'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $remark1 = $_POST['remark1'] ?? '';
    $remark2 = $_POST['remark2'] ?? '';

    // Prepare data to save
    $logData = "Status: $status\nOrder ID: $order_id\nCustomer Mobile: $customer_mobile\nAmount: $amount\nRemark1: $remark1\nRemark2: $remark2\nDate: " . date("Y-m-d H:i:s") . "\n\n";

    // Save to response.txt file
    file_put_contents("response.txt", $logData, FILE_APPEND);

    echo "Data saved successfully.";
} else {
    // Handle other request methods if necessary
    http_response_code(405); // Method Not Allowed
    echo 'Only POST requests are allowed';
}
?>
