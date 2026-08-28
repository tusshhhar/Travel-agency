<?php
/**
 * API: Official WhatsApp Business Platform / Cloud API Webhook (FR-020)
 * Handles both Meta Verification Handshake (GET) and Incoming Events / Messages (POST)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/whatsapp_helper.php';

// -------------------------------------------------------------
// 1. Meta Webhook Verification Handshake (GET)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? $_GET['hub.mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? $_GET['hub.verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? $_GET['hub.challenge'] ?? '';

    if ($mode === 'subscribe' && $token === WHATSAPP_WEBHOOK_VERIFY_TOKEN) {
        http_response_code(200);
        echo $challenge;
        exit;
    } else {
        http_response_code(403);
        echo "Forbidden: Verification token mismatch.";
        exit;
    }
}

// -------------------------------------------------------------
// 2. Incoming Event / Message Payload Handling (POST)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $payload = json_decode($rawInput, true);

    if (!$payload) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    // Process WhatsApp Cloud API Standard Event Structure
    if (isset($payload['entry'][0]['changes'][0]['value']['messages'][0])) {
        $messageData = $payload['entry'][0]['changes'][0]['value']['messages'][0];
        $fromPhone = $messageData['from'] ?? '';
        $messageType = $messageData['type'] ?? 'text';
        $messageText = '';

        if ($messageType === 'text') {
            $messageText = $messageData['text']['body'] ?? '';
        } elseif ($messageType === 'button') {
            $messageText = $messageData['button']['text'] ?? '';
        } elseif ($messageType === 'interactive') {
            $messageText = $messageData['interactive']['button_reply']['title'] 
                ?? $messageData['interactive']['list_reply']['title'] ?? '';
        }

        if (!empty($fromPhone) && !empty($messageText)) {
            // Process Auto-reply (FR-016)
            WhatsAppHelper::processIncomingMessage($fromPhone, $messageText);
        }
    }

    http_response_code(200);
    echo json_encode(['status' => 'EVENT_RECEIVED']);
    exit;
}

http_response_code(405);
echo "Method Not Allowed";
