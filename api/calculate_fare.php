<?php
/**
 * API: Dynamic Live Fare Calculator
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $pickup = cleanInput($_GET['pickup'] ?? '');
    $drop = cleanInput($_GET['drop'] ?? '');
    $vehicleId = (int)($_GET['vehicle_id'] ?? 0);
    $tripType = cleanInput($_GET['trip_type'] ?? 'One Way');
    $pickupTime = cleanInput($_GET['pickup_time'] ?? '08:00 AM');
    $days = (int)($_GET['days'] ?? 1);

    if (empty($pickup) || empty($drop) || $vehicleId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Please provide pickup, drop, and vehicle selection.']);
        exit;
    }

    $distance = estimateDistance($pickup, $drop);
    $fare = calculateFare($vehicleId, $tripType, $distance, $pickupTime, $days);

    echo json_encode(array_merge(['success' => true], $fare));
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
