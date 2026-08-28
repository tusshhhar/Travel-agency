<?php
/**
 * API: Create Payment Order
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

    if (empty($bookingId)) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
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

    // Determine amount to charge (full amount or advance amount)
    $amount = (float)$booking['total_amount'];
    
    $order = RazorpayHelper::createOrder($bookingId, $amount);

    echo json_encode($order);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
