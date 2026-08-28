<?php
/**
 * API: WhatsApp Send Message & Notification Dispatcher
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/whatsapp_helper.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;

$phone = cleanInput($data['phone'] ?? '');
$message = cleanInput($data['message'] ?? '');
$bookingId = cleanInput($data['booking_id'] ?? '');

if (empty($phone) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Phone and message body required']);
    exit;
}

$res = WhatsAppHelper::sendMessage($phone, $message, $bookingId, 'custom_admin_dispatch');

echo json_encode($res);
