<?php
/**
 * Online Payment Gateway - Currently Disabled (Direct Pay on Trip Mode Active)
 * Automatically routes bookings directly to confirmation.
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$bookingId = cleanInput($_GET['booking_id'] ?? '');

if (empty($bookingId)) {
    header('Location: ' . BASE_URL . '/booking.php');
    exit;
}

header('Location: ' . BASE_URL . '/booking_confirmation.php?booking_id=' . urlencode($bookingId));
exit;
