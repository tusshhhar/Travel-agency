<?php
/**
 * API: In-Browser WhatsApp Chat Simulator Endpoint
 * Enables immediate live interactive testing of the bot engine
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/whatsapp_helper.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;

$incomingMessage = cleanInput($data['message'] ?? '');
$phone = cleanInput($data['phone'] ?? '919536200261');

if (empty($incomingMessage)) {
    echo json_encode(['error' => 'Message is required']);
    exit;
}

$botReply = WhatsAppHelper::processIncomingMessage($phone, $incomingMessage);

echo json_encode([
    'success' => true,
    'reply' => $botReply
]);
