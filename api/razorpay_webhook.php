<?php
/**
 * API: Razorpay Webhook Handler (FR-014)
 * Handles server-to-server asynchronous notifications from Razorpay
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/razorpay_helper.php';

$rawBody = file_get_contents('php://input');
$webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

// Validate Webhook Signature if live secret configured
if (!empty(RAZORPAY_WEBHOOK_SECRET) && !empty($webhookSignature)) {
    if (!RazorpayHelper::verifyWebhookSignature($rawBody, $webhookSignature)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid Webhook Signature']);
        exit;
    }
}

$payload = json_decode($rawBody, true);

if (!$payload || !isset($payload['event'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Payload']);
    exit;
}

$event = $payload['event'];
$entity = $payload['payload']['payment']['entity'] ?? null;

if ($entity) {
    $paymentId = $entity['id'] ?? '';
    $orderId = $entity['order_id'] ?? '';
    $amount = isset($entity['amount']) ? ($entity['amount'] / 100) : 0;
    $notes = $entity['notes'] ?? [];
    $bookingId = $notes['booking_id'] ?? '';

    // If booking_id not in notes, search by order_id or notes
    if (empty($bookingId) && !empty($orderId)) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT booking_id FROM payments WHERE gateway_order_id = ?");
        $stmt->execute([$orderId]);
        $bookingId = $stmt->fetchColumn() ?: '';
    }

    if (!empty($bookingId)) {
        switch ($event) {
            case 'payment.captured':
            case 'order.paid':
                RazorpayHelper::processSuccessfulPayment($bookingId, $paymentId, $orderId, $amount, $entity['method'] ?? 'Webhook Captured', $rawBody);
                break;

            case 'payment.failed':
                RazorpayHelper::processFailedPayment($bookingId, $entity['error_description'] ?? 'Payment Failed via Webhook');
                break;
                
            case 'refund.processed':
            case 'refund.created':
                $db = Database::getConnection();
                $stmt = $db->prepare("UPDATE bookings SET refund_status = 'Refund Completed', booking_status = 'Refund Completed' WHERE booking_id = ?");
                $stmt->execute([$bookingId]);
                break;
        }
    }
}

http_response_code(200);
echo json_encode(['status' => 'success', 'event' => $event]);
