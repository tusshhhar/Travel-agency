<?php
/**
 * API: Booking Cancellation & Refund Request (FR-028, FR-029)
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/whatsapp_helper.php';

try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;

    $bookingId = cleanInput($data['booking_id'] ?? '');
    $mobile = sanitizePhone(cleanInput($data['mobile'] ?? ''));
    $reason = cleanInput($data['reason'] ?? 'Customer cancellation request');

    if (empty($bookingId) || empty($mobile)) {
        echo json_encode(['success' => false, 'message' => 'Booking ID and Mobile number are required.']);
        exit;
    }

    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = ? AND customer_mobile LIKE ?");
    $stmt->execute([$bookingId, '%' . $mobile . '%']);
    $booking = $stmt->fetch();

    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found or mobile number mismatch.']);
        exit;
    }

    if (in_array($booking['booking_status'], ['Cancelled', 'Trip Completed', 'Refund Completed'])) {
        echo json_encode(['success' => false, 'message' => 'This booking is already ' . $booking['booking_status'] . '.']);
        exit;
    }

    // Cancellation & Refund Calculation Rules
    $paidAmount = (float)$booking['advance_paid'];
    $refundAmount = 0.0;
    $refundStatus = null;

    if ($paidAmount > 0) {
        $journeyDateTime = strtotime($booking['journey_date'] . ' ' . $booking['pickup_time']);
        $hoursRemaining = ($journeyDateTime - time()) / 3600;
        
        $freeHours = (float)getFareSetting('cancellation_free_hours', 12);
        $deductionPercent = (float)getFareSetting('cancellation_charge_percentage', 15);

        if ($hoursRemaining >= $freeHours) {
            // Full Refund
            $refundAmount = $paidAmount;
        } else {
            // Deduct nominal cancellation charge
            $deduction = ($paidAmount * $deductionPercent) / 100;
            $refundAmount = max(0, $paidAmount - $deduction);
        }
        $refundStatus = 'Refund Initiated';
    }

    $uStmt = $db->prepare("UPDATE bookings SET booking_status = 'Cancelled', cancellation_reason = ?, refund_amount = ?, refund_status = ? WHERE booking_id = ?");
    $uStmt->execute([$reason, $refundAmount, $refundStatus, $bookingId]);

    // Send WhatsApp notification
    if ($refundAmount > 0) {
        WhatsAppHelper::sendRefundNotification($booking, $refundAmount);
    } else {
        $cancelMsg = "⚠️ *Bishnoi Travels – Booking Cancelled*\n\n"
                   . "Your Booking *{$bookingId}* for " . date('d-M-Y', strtotime($booking['journey_date'])) . " has been successfully cancelled.\n\n"
                   . "If you need a cab again, we are always here to serve you! 24x7 Helpline: " . PHONE_PRIMARY;
        WhatsAppHelper::sendMessage($booking['customer_mobile'], $cancelMsg, $bookingId, 'booking_cancelled');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Booking cancelled successfully.' . ($refundAmount > 0 ? ' Refund of ' . formatCurrency($refundAmount) . ' has been initiated.' : ''),
        'refund_amount' => $refundAmount
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
