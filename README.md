# 💳 Puffx Pay – SMM Panel Payment Gateway Module

**Puffx Pay** is a secure and lightweight payment gateway module built for SMM Panels. It enables customers to make payments via the Puffx Pay API with real-time payment verification using a callback system. Perfect for SMM providers and digital service platforms using PHP.

---

## 🚀 Features

- 🔐 Token-based secure API integration  
- ⚡ Real-time callback and payment verification  
- 💸 Supports INR and multi-currency payments  
- 🧩 Works with any PHP-based SMM panel  
- 🧾 Clean transaction logging support  
- 🖥️ Lightweight and easy to install  

---

## 📦 Files Included

| File Name             | Purpose                                       |
|-----------------------|-----------------------------------------------|
| `addfunds.twig`       | Payment UI Template                           |
| `puffxpay.php`        | Configuration and Payment Request Script      |
| `webhook.php`         | Callback handler to receive payment response  |

---

## 🛠️ Installation Guide

### Step 1: Download

Download the latest version of this module from:

- [GitHub Releases](https://github.com/puffxhost/Puffx-Pay-SMM-Panel-Module)  
- Or [Download from Puffx Server](https://api.puffxhost.com/module/Puffx%20Pay%20SMM.zip)

---

### Step 2: Upload Files

Upload the following files into your project directory:<br>
app/views/xblur/addfunds.twig<br>
app/views/theme path/puffxpay/puffxpay.php<br>
puffxpay/webhook.php

---

### Step 3: Configure `puffxpay.php
Edit the configuration file with your API token and redirect URL:
```php
<?php
define('PUFFX_API_URL', 'https://pay.x-server.in/api/create-order');
define('PUFFX_TOKEN', 'your_api_token_here');
define('PUFFX_CALLBACK_URL', 'https://yourdomain.com/callback.php');
?>
```
--- 

### Step 4: Start a Payment Request
To test payment integration, open this URL in your browser:
```php
https://yourdomain.com/example_payment.php?amount=100&user_id=123
```
> This will redirect the user to Puffx Pay’s payment gateway to complete the transaction.

---

### 🔄 Callback Handling
The file ```webhook.php``` automatically handles the payment status sent from Puffx Pay's API once the user completes the payment.

Sample callback JSON received:
```php
{
  "order_id": "order_xyz123",
  "payment_status": "success",
  "amount": "100.00",
  "transaction_id": "txn_abc456"
}
```
You can verify and update your panel/database based on the ```payment_status``` value. Log the callback data for future reference or debugging.

✅ Example PHP logging:
```php
file_put_contents("payment_log.txt", json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
```

---

### ⚙️ Requirements
<ul>
  <li>✅ PHP 5.6 or higher</li>
  <li>✅ cURL enabled on your hosting server</li>
  <li>✅ A valid Puffx Pay merchant account with active API token</li>
</ul>

---

### 🧰 Example Usage
| Setting               | Example Value                               |
|-----------------------|---------------------------------------------|
| `API Token`           | ee2a9cd47872e3109434c8c57616bc2f            |
| `webhook.php`         | https://yourdomain.com/callback.php         |
| `Payment URL`         | https://yourdomain.com/example_payment.php  |

---

### 🔐 Security Tips
<ul>
  <li>🔒 Never expose your API token publicly</li>
  <li>🌐 Use only secure ```https://``` URLs for all integrations</li>
  <li>🛡️ Validate and sanitize all incoming data in ```webhook.php```</li>
  <li>🔁 Rotate your token periodically via Puffx dashboard</li>
</ul>

---

### 📞 Support
Need help with setup, customization, or error troubleshooting?
<p>📧 Email: <a href="mailto:support@puffxhost.in" target="_blank">support@puffxhost.in</a><br>
     🌐 Website: <a href="https://pay.x-server.in/" target="_blank">https://pay.x-server.in</a><br>
     📱 WhatsApp: <a href="https://wa.me/918602967573" target="_blank">+91 8602967573</a><br>
  </p>

---

### 📜 License
This module is open-source and released under the <strong>MIT License.</strong>

---
### 🧾 Changelog
v1.0.0 – 06 August 2025
<ul>
  <li>✅ First stable release</li>
  <li>✅ Added token-based payment request system</li>
  <li>✅ Implemented real-time callback verification</li>
</ul>

---

👨‍💻 Author</h2>
  <p>Made with ❤️ by <a href="https://github.com/puffxhost" target="_blank">Nitin Mehta</a><br>
     Instagram: <a href="https://instagram.com/unknown_coder1x" target="_blank">@unknown_coder1x</a>
  </p>

<hr>
<p align="center">
  Developed with ❤️ by <strong>Puffx Pay</strong>
</p>


