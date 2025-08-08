<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    $order_id = $_POST['order_id'] ?? '';
    $customer_mobile = $_POST['customer_mobile'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $remark1 = $_POST['remark1'] ?? ''; // username
    $remark2 = $_POST['remark2'] ?? '';
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $now = date("Y-m-d H:i:s");

    // Log webhook
    $logMessage = "[$now] Webhook received:\n";
    $logMessage .= "Status: $status | Order ID: $order_id | Mobile: $customer_mobile | Amount: $amount | Username: $remark1 | Remark: $remark2 | IP: $ip_address\n";
    $logMessage .= "----------------------------\n";
    file_put_contents('webhook_log.txt', $logMessage, FILE_APPEND);

    if (strtoupper($status) === 'SUCCESS') {
        // DB connection
        $conn = new mysqli("localhost", "dbName", "", "dbuser");
        if ($conn->connect_error) {
            http_response_code(500);
            echo "Database connection failed";
            exit;
        }

        // 1. Get client info by username
        $stmt = $conn->prepare("SELECT client_id, balance FROM clients WHERE username = ?");
        $stmt->bind_param("s", $remark1);
        $stmt->execute();
        $result = $stmt->get_result();
        $client = $result->fetch_assoc();
        $stmt->close();

        if ($client) {
            $client_id = $client['client_id'];
            $current_balance = (float)$client['balance'];
            $payment_amount = (float)$amount;
            $new_balance = $current_balance + $payment_amount;

            // 2. Update client balance
            $update = $conn->prepare("UPDATE clients SET balance = ? WHERE client_id = ?");
            $update->bind_param("di", $new_balance, $client_id);
            $update->execute();
            $update->close();

            // 3. Insert into payments
            $payment_status = '3'; // assuming '1' = success
            $payment_delivery = '2'; // assuming default
            $payment_method = 111; // default integer for method
            $payment_mode = 'Automatic';
            $payment_note = "Auto payment from PuffxPay. Remark: $remark2";
            $payment_bank = 0;

            $insert = $conn->prepare("INSERT INTO payments (
                client_id, client_balance, payment_amount, payment_method, 
                payment_status, payment_delivery, payment_note, 
                payment_mode, payment_create_date, payment_update_date, 
                payment_ip, payment_extra, payment_bank, t_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', ?, ?)");

            $insert->bind_param("iddiiisssssis",
                $client_id,
                $new_balance,
                $payment_amount,
                $payment_method,
                $payment_status,
                $payment_delivery,
                $payment_note,
                $payment_mode,
                $now,
                $now,
                $ip_address,
                $payment_bank,
                $order_id
            );

            if ($insert->execute()) {
                echo "Balance updated and payment logged successfully.";
            } else {
                echo "Balance updated but failed to insert payment.";
            }

            $insert->close();
        } else {
            echo "User not found";
        }

        $conn->close();
    } else {
        echo "Payment not successful.";
    }
} else {
    http_response_code(405);
    echo 'Only POST requests are allowed';
}
?>
