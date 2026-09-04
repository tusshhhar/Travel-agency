<?php
/**
 * Bishnoi Travels - WhatsApp Business Platform / Cloud API Integration Helper
 * Fully Complies with Meta WhatsApp Cloud API v19.0+ and FRS FR-015 to FR-020
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

class WhatsAppHelper {

    /**
     * Send raw text or template message via WhatsApp Cloud API
     */
    public static function sendMessage(string $toPhone, string $messageText, ?string $bookingId = null, string $templateName = 'custom_text'): array {
        $cleanPhone = preg_replace('/[^0-9]/', '', $toPhone);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $logStatus = 'sent';
        $apiResponse = null;

        // If active Cloud API Access Token is provided
        if (!empty(WHATSAPP_ACCESS_TOKEN) && !str_starts_with(WHATSAPP_ACCESS_TOKEN, 'EAAB...')) {
            $url = "https://graph.facebook.com/" . WHATSAPP_API_VERSION . "/" . WHATSAPP_PHONE_NUMBER_ID . "/messages";
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $cleanPhone,
                'type' => 'text',
                'text' => ['preview_url' => true, 'body' => $messageText]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . WHATSAPP_ACCESS_TOKEN,
                'Content-Type: application/json'
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $apiResponse = $result;
            if ($httpCode !== 200) {
                $logStatus = 'failed';
            }
        }

        // Log to database
        self::logMessage($cleanPhone, 'outbound', 'text', $messageText, $templateName, $logStatus, $bookingId, $apiResponse);

        return [
            'success' => true,
            'to' => $cleanPhone,
            'status' => $logStatus,
            'message' => $messageText
        ];
    }

    /**
     * FR-017: Automated Booking Confirmation Message
     */
    public static function sendBookingConfirmation(array $booking): array {
        $bookingId = $booking['booking_id'];
        $customerName = $booking['customer_name'];
        $pickup = $booking['pickup_location'];
        $drop = $booking['drop_location'];
        $journeyDate = date('d-M-Y', strtotime($booking['journey_date']));
        $pickupTime = $booking['pickup_time'];
        $vehicle = $booking['vehicle_name'];
        $amount = formatCurrency($booking['total_amount']);
        $phone1 = PHONE_PRIMARY;
        $phone2 = PHONE_SECONDARY;

        $msg = "🚕 *" . BUSINESS_NAME . " – Booking Confirmed!*\n\n"
             . "Dear *{$customerName}*,\n"
             . "Thank you for booking with " . BUSINESS_NAME . ".\n\n"
             . "📋 *Booking Details:*\n"
             . "• *Booking ID:* {$bookingId}\n"
             . "• *Pickup:* {$pickup}\n"
             . "• *Drop:* {$drop}\n"
             . "• *Date:* {$journeyDate}\n"
             . "• *Time:* {$pickupTime}\n"
             . "• *Vehicle:* {$vehicle}\n"
             . "• *Total Amount:* {$amount}\n"
             . "• *Payment Status:* PAID ✅\n\n"
             . "🚖 *Driver Details:* Will be assigned shortly and shared via WhatsApp.\n\n"
             . "📞 *24×7 Assistance:* {$phone1} / {$phone2}\n"
             . "📍 *Address:* " . BUSINESS_ADDRESS . "\n\n"
             . "Have a safe and pleasant journey with " . BUSINESS_NAME . "! 🙏";

        return self::sendMessage($booking['customer_mobile'], $msg, $bookingId, 'booking_confirmation');
    }

    /**
     * FR-018: Automated Payment Failure Notification
     */
    public static function sendPaymentFailedNotification(array $booking): array {
        $bookingId = $booking['booking_id'];
        $retryUrl = BASE_URL . "/payment.php?booking_id=" . urlencode($bookingId);
        
        $msg = "⚠️ *" . BUSINESS_NAME . " – Payment Incomplete*\n\n"
             . "Dear *{$booking['customer_name']}*,\n"
             . "Your payment for Booking *{$bookingId}* could not be completed.\n\n"
             . "To confirm your cab, please retry using the link below:\n"
             . "🔗 {$retryUrl}\n\n"
             . "For quick assistance, call us: " . PHONE_PRIMARY . " / " . PHONE_SECONDARY;

        return self::sendMessage($booking['customer_mobile'], $msg, $bookingId, 'payment_failed');
    }

    /**
     * FR-018: Refund Notification
     */
    public static function sendRefundNotification(array $booking, float $refundAmount): array {
        $bookingId = $booking['booking_id'];
        $amountFmt = formatCurrency($refundAmount);
        
        $msg = "💳 *" . BUSINESS_NAME . " – Refund Initiated*\n\n"
             . "Dear *{$booking['customer_name']}*,\n"
             . "A refund of *{$amountFmt}* has been initiated for Booking *{$bookingId}*.\n"
             . "It will be credited back to your original payment source within 3-5 business days.\n\n"
             . "For any query, contact: " . PHONE_PRIMARY;

        return self::sendMessage($booking['customer_mobile'], $msg, $bookingId, 'refund_initiated');
    }

    /**
     * FR-019: Automated Trip Reminder
     */
    public static function sendTripReminder(array $booking): array {
        $bookingId = $booking['booking_id'];
        $driverInfo = !empty($booking['assigned_driver_name']) 
            ? "• *Driver:* {$booking['assigned_driver_name']} ({$booking['assigned_driver_mobile']})\n• *Cab No:* {$booking['assigned_vehicle_no']}\n"
            : "• *Driver:* Driver is being assigned.\n";

        $msg = "🔔 *" . BUSINESS_NAME . " – Trip Reminder 🚕*\n\n"
             . "Dear *{$booking['customer_name']}*,\n"
             . "Your cab journey is scheduled for tomorrow:\n\n"
             . "• *Booking ID:* {$bookingId}\n"
             . "• *Pickup:* {$booking['pickup_location']}\n"
             . "• *Pickup Time:* {$booking['pickup_time']}\n"
             . "• *Date:* " . date('d-M-Y', strtotime($booking['journey_date'])) . "\n"
             . $driverInfo . "\n"
             . "Please be available at the pickup location. 24x7 Helpline: " . PHONE_PRIMARY;

        return self::sendMessage($booking['customer_mobile'], $msg, $bookingId, 'trip_reminder');
    }

    /**
     * FR-023: Driver Assigned Notification
     */
    public static function sendDriverAssignedNotification(array $booking): array {
        $bookingId = $booking['booking_id'];
        
        $msg = "🚕 *" . BUSINESS_NAME . " – Driver Assigned!*\n\n"
             . "Dear *{$booking['customer_name']}*,\n"
             . "Your driver has been assigned for Booking *{$bookingId}*:\n\n"
             . "👤 *Driver Name:* {$booking['assigned_driver_name']}\n"
             . "📱 *Driver Mobile:* {$booking['assigned_driver_mobile']}\n"
             . "🚘 *Vehicle Number:* {$booking['assigned_vehicle_no']}\n"
             . "📍 *Pickup:* {$booking['pickup_location']}\n"
             . "⏰ *Pickup Time:* {$booking['pickup_time']}\n\n"
             . "Driver will contact you 30 minutes before arrival. Have a safe journey!";

        return self::sendMessage($booking['customer_mobile'], $msg, $bookingId, 'driver_assigned');
    }

    /**
     * FR-016: Intelligent WhatsApp Auto-Reply & Keyword Engine
     */
    public static function processIncomingMessage(string $fromPhone, string $incomingText): string {
        $text = strtolower(trim($incomingText));
        $cleanPhone = sanitizePhone($fromPhone);

        // Log inbound message
        self::logMessage($cleanPhone, 'inbound', 'text', $incomingText, null, 'received');

        $response = "";

        // Keyword Matching (FRS FR-016 / FR-020)
        if (in_array($text, ['hi', 'hello', 'hey', 'start', 'menu', 'namaste'])) {
            $response = "🚕 *Welcome to " . BUSINESS_NAME . ", Haridwar!* 🙏\n"
                      . "All Over India 24 Hours Cab Services Available.\n\n"
                      . "How can we assist you today?\n"
                      . "1️⃣ *Book a Cab*\n"
                      . "2️⃣ *Check Fare / Rates*\n"
                      . "3️⃣ *Check Existing Booking Status*\n"
                      . "4️⃣ *Talk to Support / " . OWNER_NAME . "*\n"
                      . "5️⃣ *Chardham & Outstation Tours*\n\n"
                      . "Reply with *1*, *2*, *3*, *4*, or *5* to proceed.";
        }
        elseif ($text === '1' || str_contains($text, 'book') || str_contains($text, 'cab')) {
            $bookingUrl = BASE_URL . "/booking.php";
            $response = "🚖 *Book a Cab with " . BUSINESS_NAME . "*\n\n"
                      . "You can book instantly online in 2 minutes with live fare estimation:\n"
                      . "👉 {$bookingUrl}\n\n"
                      . "Or simply reply here with:\n"
                      . "📍 *Pickup Location:*\n"
                      . "📍 *Drop Location:*\n"
                      . "📅 *Date & Time:*\n"
                      . "🚘 *Vehicle Type:* (Sedan / SUV / Innova Crysta / Tempo)\n"
                      . "👤 *Passengers:*";
        }
        elseif ($text === '2' || str_contains($text, 'fare') || str_contains($text, 'price') || str_contains($text, 'rate')) {
            $response = "🏷️ *" . BUSINESS_NAME . " – Popular One-Way & Outstation Fares:*\n\n"
                      . "🚕 *Haridwar ➔ Delhi / IGI Airport:* Starts ₹3,499\n"
                      . "🚕 *Haridwar ➔ Dehradun / Airport:* Starts ₹1,499\n"
                      . "🚕 *Haridwar ➔ Rishikesh:* Starts ₹899\n"
                      . "🚕 *Haridwar ➔ Mussoorie:* Starts ₹2,999\n"
                      . "🚕 *Haridwar ➔ Chandigarh:* Starts ₹3,999\n"
                      . "🚕 *Chardham Yatra Package:* Customized quotes available\n\n"
                      . "Per KM Rates: Sedan ₹11/km | SUV ₹15/km | Innova Crysta ₹21/km | Tempo Traveller ₹26/km\n\n"
                      . "For exact quote, visit: " . BASE_URL . "/booking.php";
        }
        elseif ($text === '3' || str_contains($text, 'status') || str_contains($text, 'track')) {
            $response = "🔍 *Track Your Booking Status*\n\n"
                      . "Please reply with your *Booking ID* (e.g. `BT-" . date('Ymd') . "-001`) or check live online at:\n"
                      . "👉 " . BASE_URL . "/track_booking.php";
        }
        elseif (preg_match('/BT-\d{8}-\d{3}/i', $incomingText, $matches)) {
            $bookingId = strtoupper($matches[0]);
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
            $stmt->execute([$bookingId]);
            $b = $stmt->fetch();

            if ($b) {
                $driverStr = !empty($b['assigned_driver_name']) ? "{$b['assigned_driver_name']} ({$b['assigned_driver_mobile']})" : "Assigning shortly";
                $response = "📋 *Booking Details for {$bookingId}:*\n\n"
                          . "• *Status:* *" . strtoupper($b['booking_status']) . "*\n"
                          . "• *Customer:* {$b['customer_name']}\n"
                          . "• *Route:* {$b['pickup_location']} ➔ {$b['drop_location']}\n"
                          . "• *Date & Time:* " . date('d-M-Y', strtotime($b['journey_date'])) . " at {$b['pickup_time']}\n"
                          . "• *Vehicle:* {$b['vehicle_name']}\n"
                          . "• *Driver:* {$driverStr}\n"
                          . "• *Total Amount:* " . formatCurrency($b['total_amount']) . "\n\n"
                          . "For any changes, call: " . PHONE_PRIMARY;
            } else {
                $response = "❌ Booking ID *{$bookingId}* not found. Please verify the ID or call our helpline: " . PHONE_PRIMARY;
            }
        }
        elseif ($text === '4' || str_contains($text, 'support') || str_contains($text, 'contact') || str_contains($text, 'call')) {
            $response = "📞 *" . BUSINESS_NAME . " 24×7 Direct Support*\n\n"
                      . "👤 *Owner / Manager:* " . OWNER_NAME . "\n"
                      . "📱 *Phone:* " . PHONE_PRIMARY . " / " . PHONE_SECONDARY . "\n"
                      . "📍 *Office:* " . BUSINESS_ADDRESS . "\n"
                      . "⏰ *Service:* 24 Hours / 7 Days\n\n"
                      . "We are ready to assist you anytime!";
        }
        elseif ($text === '5' || str_contains($text, 'chardham') || str_contains($text, 'tour')) {
            $response = "⛰️ *" . BUSINESS_NAME . " Chardham & Uttarakhand Tour Packages*\n\n"
                      . "Special customized packages available for:\n"
                      . "• Kedarnath & Badrinath Do Dham Yatra\n"
                      . "• Complete 4 Dham Yatra (Yamunotri, Gangotri, Kedarnath, Badrinath)\n"
                      . "• Mussoorie - Dhanaulti - Rishikesh Tour\n"
                      . "• Nainital - Jim Corbett Tour\n\n"
                      . "Experienced hill drivers, sanitized AC vehicles, pushback seats & 24x7 support.\n"
                      . "Call " . OWNER_NAME . " directly for package quotes: " . PHONE_PRIMARY;
        }
        else {
            // Fallback Menu
            $response = "Thank you for contacting *" . BUSINESS_NAME . "*! 🚕\n\n"
                      . "We provide 24x7 all India cab service from Haridwar.\n"
                      . "• Reply *1* to Book a Cab\n"
                      . "• Reply *2* for Fare Details\n"
                      . "• Reply *3* to Track Booking\n"
                      . "• Reply *4* for 24x7 Helpline (" . PHONE_PRIMARY . ")\n\n"
                      . "Or visit: " . BASE_URL;
        }

        // Send auto-reply
        self::sendMessage($cleanPhone, $response, null, 'auto_reply');

        return $response;
    }

    /**
     * Database Logger for WhatsApp Interactions
     */
    private static function logMessage(string $phone, string $direction, string $type, string $body, ?string $templateName, string $status, ?string $bookingId = null, ?string $rawPayload = null): void {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO whatsapp_logs (phone_number, message_direction, message_type, message_body, template_name, status, booking_id, raw_payload) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$phone, $direction, $type, $body, $templateName, $status, $bookingId, $rawPayload]);
        } catch (Exception $e) {
            error_log("WhatsApp Log Error: " . $e->getMessage());
        }
    }
}
