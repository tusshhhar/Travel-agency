<?php
/**
 * Bishnoi Travels - Razorpay Payment Gateway Helper & Webhook Verifier
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/whatsapp_helper.php';

class RazorpayHelper {
    
    /**
     * Create an order on Razorpay or generate a valid sandbox order ID
     */
    public static function createOrder(string $bookingId, float $amountInInr, string $currency = 'INR'): array {
        $amountInPaise = (int)round($amountInInr * 100);
        $receipt = substr($bookingId, 0, 40);

        // If in live mode with valid API keys
        if (!RAZORPAY_ENABLE_SANDBOX_SIMULATOR && str_starts_with(RAZORPAY_KEY_ID, 'rzp_live_')) {
            $ch = curl_init('https://api.razorpay.com/v1/orders');
            $data = [
                'amount' => $amountInPaise,
                'currency' => $currency,
                'receipt' => $receipt,
                'payment_capture' => 1
            ];
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $orderData = json_decode($response, true);
                return [
                    'success' => true,
                    'order_id' => $orderData['id'],
                    'amount' => $amountInPaise,
                    'currency' => $currency,
                    'key_id' => RAZORPAY_KEY_ID
                ];
            }
        }

        // Offline / Sandbox Simulator Fallback
        $simulatedOrderId = 'order_BT_' . strtoupper(bin2hex(random_bytes(6)));
        return [
            'success' => true,
            'order_id' => $simulatedOrderId,
            'amount' => $amountInPaise,
            'currency' => $currency,
            'key_id' => RAZORPAY_KEY_ID,
            'is_simulated' => true
        ];
    }

    /**
     * Verify payment signature (HMAC SHA256)
     */
    public static function verifySignature(string $orderId, string $paymentId, string $signature): bool {
        // If simulator mode, allow test signatures
        if (RAZORPAY_ENABLE_SANDBOX_SIMULATOR && str_starts_with($signature, 'test_sig_')) {
            return true;
        }

        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify Razorpay Webhook signature
     */
    public static function verifyWebhookSignature(string $payload, string $webhookSignature): bool {
        $expectedSignature = hash_hmac('sha256', $payload, RAZORPAY_WEBHOOK_SECRET);
        return hash_equals($expectedSignature, $webhookSignature);
    }

    /**
     * Mark booking as PAID and trigger confirmation & WhatsApp notifications
     */
    public static function processSuccessfulPayment(string $bookingId, string $paymentId, string $orderId, float $amount, string $method = 'UPI/Online', string $rawResponse = ''): bool {
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            // Record in payments table
            $stmt = $db->prepare("INSERT INTO payments (booking_id, gateway, gateway_order_id, transaction_id, signature, amount, currency, payment_status, payment_method, gateway_response) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $bookingId, 'Razorpay', $orderId, $paymentId, 'VERIFIED', $amount, 'INR', 'Paid', $method, $rawResponse
            ]);

            // Update booking status
            $uStmt = $db->prepare("UPDATE bookings SET booking_status = 'Confirmed', advance_paid = ?, balance_amount = 0 WHERE booking_id = ?");
            $uStmt->execute([$amount, $bookingId]);

            $db->commit();

            // Fetch booking details for WhatsApp trigger
            $bStmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
            $bStmt->execute([$bookingId]);
            $booking = $bStmt->fetch();

            if ($booking) {
                // Send automated WhatsApp booking confirmation (FR-017 / FR-018)
                WhatsAppHelper::sendBookingConfirmation($booking);
            }

            return true;
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Payment Process Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark booking payment as Failed
     */
    public static function processFailedPayment(string $bookingId, string $reason = 'Payment Failed or Cancelled by User'): bool {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE bookings SET booking_status = 'Payment Failed' WHERE booking_id = ?");
            $stmt->execute([$bookingId]);

            // Fetch booking details for WhatsApp failure notification
            $bStmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
            $bStmt->execute([$bookingId]);
            $booking = $bStmt->fetch();

            if ($booking) {
                WhatsAppHelper::sendPaymentFailedNotification($booking);
            }
            return true;
        } catch (Exception $e) {
            error_log("Failed payment record error: " . $e->getMessage());
            return false;
        }
    }
}
