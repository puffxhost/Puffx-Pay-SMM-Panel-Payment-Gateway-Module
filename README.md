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

Upload the following files into your project directory:
app/views/xblur/addfunds.twig
app/views/theme path/puffxpay/puffxpay.php
puffxpay/webhook.php

---

### Step 3: Configure `puffxpay_config.php`

Edit the configuration file with your API token and redirect URL:

```php
<?php
define('PUFFX_API_URL', 'https://pay.x-server.in/api/create-order');
define('PUFFX_TOKEN', 'your_api_token_here');
?>```

---
### Step 4: Start a Payment Request

