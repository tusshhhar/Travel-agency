<?php
/**
 * API: Verify Payment & Confirm Booking (FR-011, FR-012, FR-013)
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/razorpay_helper.php';

try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;

    $bookingId = cleanInput($data['booking_id'] ?? '');
    $paymentId = cleanInput($data['razorpay_payment_id'] ?? $data['payment_id'] ?? '');
    $orderId = cleanInput($data['razorpay_order_id'] ?? $data['order_id'] ?? '');
    $signature = cleanInput($data['razorpay_signature'] ?? $data['signature'] ?? '');
    $method = cleanInput($data['payment_method'] ?? 'Online UPI/Card');

    if (empty($bookingId) || empty($paymentId)) {
        echo json_encode(['success' => false, 'message' => 'Missing payment parameters']);
        exit;
    }

    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }

    // Verify signature
    $isValid = RazorpayHelper::verifySignature($orderId, $paymentId, $signature);

    if ($isValid) {
        $amount = (float)$booking['total_amount'];
        $processed = RazorpayHelper::processSuccessfulPayment($bookingId, $paymentId, $orderId, $amount, $method, json_encode($data));

        if ($processed) {
            echo json_encode([
                'success' => true,
                'message' => 'Payment verified successfully! Booking Confirmed.',
                'booking_id' => $bookingId,
                'redirect_url' => BASE_URL . '/booking_confirmation.php?booking_id=' . urlencode($bookingId)
            ]);
            exit;
        }
    }

    // Failed payment handling
    RazorpayHelper::processFailedPayment($bookingId, 'Signature verification failed or payment declined');
    echo json_encode(['success' => false, 'message' => 'Payment verification failed. Please retry.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
