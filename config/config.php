<?php
/**
 * Bishnoi Travels - Configuration File
 * Document Version: 1.0 (FRS Compliant)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------------
// 1. Business & Company Profile (As per FRS)
// -------------------------------------------------------------
define('BUSINESS_NAME', 'BISHNOI TRAVELS');
define('OWNER_NAME', 'ASHEESH BISHNOI');
define('BUSINESS_TAGLINE', 'All Over India 24 Hours Services Available');
define('BUSINESS_ADDRESS', 'D-Block, New Shivalik Nagar, Haridwar, Uttarakhand - 249403');
define('PHONE_PRIMARY', '9536200261');
define('PHONE_SECONDARY', '8449911315');
define('WHATSAPP_NUMBER', '919536200261'); // International format without +
define('SUPPORT_EMAIL', 'info@bishnoitravels.com');
define('CURRENCY_SYMBOL', '₹');
define('CURRENCY_CODE', 'INR');

// -------------------------------------------------------------
// 2. Base Paths & Application URL
// -------------------------------------------------------------
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$baseUrl = rtrim($protocol . $host . $scriptName, '/\\');
// If inside /admin or /api, compute root base URL
$baseUrl = preg_replace('/\/(admin|api)(\/.*)?$/', '', $baseUrl);
define('BASE_URL', $baseUrl);

// -------------------------------------------------------------
// 3. Database Configuration (Dual Support: SQLite & MySQL)
// -------------------------------------------------------------
// Options: 'sqlite' (Zero setup, runs anywhere out of the box) or 'mysql'
define('DB_DRIVER', 'sqlite'); 

// MySQL Configuration (Used if DB_DRIVER is set to 'mysql')
define('DB_HOST', 'localhost');
define('DB_NAME', 'bishnoi_travels');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// SQLite Configuration
define('DB_SQLITE_PATH', __DIR__ . '/../database/bishnoi_travels.sqlite');

// -------------------------------------------------------------
// 4. Razorpay Payment Gateway Configuration
// -------------------------------------------------------------
// Replace with your Razorpay Key ID and Key Secret from Dashboard
define('RAZORPAY_KEY_ID', 'rzp_test_BishnoiDemoKey123');
define('RAZORPAY_KEY_SECRET', 'BishnoiSecretKey456789');
define('RAZORPAY_WEBHOOK_SECRET', 'BishnoiWebhookSecretRazorpay_987');
define('RAZORPAY_ENABLE_SANDBOX_SIMULATOR', true); // Allows testing payment completion without live bank cards

// -------------------------------------------------------------
// 5. WhatsApp Business Platform / Cloud API Configuration
// -------------------------------------------------------------
// Meta Developer Portal -> WhatsApp Cloud API Settings
define('WHATSAPP_API_VERSION', 'v19.0');
define('WHATSAPP_PHONE_NUMBER_ID', '109876543210987'); // From Meta App Dashboard
define('WHATSAPP_BUSINESS_ACCOUNT_ID', '987654321098765');
define('WHATSAPP_ACCESS_TOKEN', 'EAAB...YOUR_SYSTEM_USER_ACCESS_TOKEN');
define('WHATSAPP_WEBHOOK_VERIFY_TOKEN', 'bishnoi_travels_verify_token_2026');
define('WHATSAPP_ENABLE_SIMULATOR', true); // In-browser WhatsApp bot simulator for demonstrations & testing

// -------------------------------------------------------------
// 6. Security & Admin Defaults
// -------------------------------------------------------------
define('ADMIN_DEFAULT_USER', 'admin');
define('ADMIN_DEFAULT_PASS', 'admin123'); // Changed upon first login
define('CSRF_TOKEN_NAME', 'bt_csrf_token');

// Timezone
date_default_timezone_set('Asia/Kolkata');
