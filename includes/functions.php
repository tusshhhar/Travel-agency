<?php
/**
 * Bishnoi Travels - Core Helper Functions
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

function cleanInput($data): string {
    if (is_null($data)) return '';
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function sanitizePhone(string $phone): string {
    $cleaned = preg_replace('/[^0-9]/', '', $phone);
    // If starts with 91 and 12 digits, strip or format
    if (strlen($cleaned) === 10) {
        return $cleaned;
    }
    if (strlen($cleaned) === 12 && str_starts_with($cleaned, '91')) {
        return substr($cleaned, 2);
    }
    return $cleaned;
}

function generateBookingId(): string {
    $db = Database::getConnection();
    $prefix = 'BT-' . date('Ymd') . '-';
    
    // Count today's bookings
    $stmt = $db->prepare("SELECT COUNT(*) FROM bookings WHERE booking_id LIKE ?");
    $stmt->execute([$prefix . '%']);
    $count = (int)$stmt->fetchColumn() + 1;
    
    return sprintf('%s%03d', $prefix, $count);
}

function formatCurrency(float|int $amount): string {
    return CURRENCY_SYMBOL . ' ' . number_format((float)$amount, 2);
}

function getFareSetting(string $key, $default = null) {
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT setting_value FROM fare_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Intelligent Distance Estimator based on popular Uttarakhand & North India routes
 */
function estimateDistance(string $pickup, string $drop): float {
    $p = strtolower(trim($pickup));
    $d = strtolower(trim($drop));
    
    // Route Distance Lookup Map in KM (One way)
    $routes = [
        'haridwar' => [
            'delhi' => 220, 'new delhi' => 225, 'igi airport' => 235, 'noida' => 215, 'gurgaon' => 245,
            'dehradun' => 52, 'jolly grant' => 38, 'rishikesh' => 25, 'mussoorie' => 85, 'roorkee' => 30,
            'chandigarh' => 205, 'meerut' => 140, 'muzaffarnagar' => 85, 'jaipur' => 490, 'agra' => 380,
            'badrinath' => 315, 'kedarnath' => 240, 'gangotri' => 285, 'yamunotri' => 240, 'nainital' => 235
        ],
        'rishikesh' => [
            'delhi' => 240, 'new delhi' => 245, 'igi airport' => 255, 'dehradun' => 42,
            'mussoorie' => 75, 'badrinath' => 295, 'kedarnath' => 220, 'gangotri' => 265, 'yamunotri' => 225,
            'haridwar' => 25, 'jolly grant' => 20, 'chandigarh' => 220
        ],
        'dehradun' => [
            'delhi' => 250, 'igi airport' => 265, 'mussoorie' => 35, 'haridwar' => 52,
            'rishikesh' => 42, 'chandigarh' => 170, 'jolly grant' => 28, 'paonta sahib' => 50
        ]
    ];
    
    // Search lookup map
    foreach ($routes as $origin => $destinations) {
        if (str_contains($p, $origin)) {
            foreach ($destinations as $dest => $km) {
                if (str_contains($d, $dest)) {
                    return (float)$km;
                }
            }
        }
        if (str_contains($d, $origin)) {
            foreach ($destinations as $dest => $km) {
                if (str_contains($p, $dest)) {
                    return (float)$km;
                }
            }
        }
    }
    
    // Fallback based on text heuristics
    if (str_contains($p, 'delhi') || str_contains($d, 'delhi')) return 230.0;
    if (str_contains($p, 'dehradun') || str_contains($d, 'dehradun')) return 55.0;
    if (str_contains($p, 'rishikesh') || str_contains($d, 'rishikesh')) return 30.0;
    if (str_contains($p, 'airport') || str_contains($d, 'airport')) return 45.0;
    if (str_contains($p, 'chardham') || str_contains($d, 'chardham')) return 900.0;
    
    return 150.0; // Standard default distance
}

/**
 * FRS Compliant Fare Calculator
 */
function calculateFare(int $vehicleId, string $tripType, float $distanceKm, string $pickupTime = '08:00 AM', int $days = 1): array {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicleId]);
    $vehicle = $stmt->fetch();
    
    if (!$vehicle) {
        throw new Exception("Vehicle not found.");
    }
    
    $perKmRate = (float)$vehicle['per_km_rate'];
    $baseFare = (float)$vehicle['base_fare'];
    $minKm = (int)$vehicle['min_km'];
    $driverAllowancePerDay = (float)$vehicle['driver_allowance_per_day'];
    
    // Distance Adjustment based on Trip Type
    $billableDistance = $distanceKm;
    $multiplier = 1;
    
    if ($tripType === 'Round Trip') {
        $multiplier = 2;
        $billableDistance = max($distanceKm * 2, $minKm * $days);
        $driverAllowance = $driverAllowancePerDay * max(1, $days);
    } elseif ($tripType === 'Local') {
        // Local 8hr/80km package logic
        $billableDistance = max(80, $distanceKm);
        $driverAllowance = 0;
    } elseif ($tripType === 'Airport Transfer') {
        $billableDistance = max(40, $distanceKm);
        $driverAllowance = 0;
    } else { // One Way
        $billableDistance = max($distanceKm, $minKm);
        $driverAllowance = 0;
    }
    
    $distanceCharge = $billableDistance * $perKmRate;
    
    // Check Night Charge (10:00 PM to 06:00 AM)
    $nightCharge = 0.0;
    $timeClean = strtolower(trim($pickupTime));
    $isNight = false;
    
    // Quick time parser
    if (preg_match('/(\d{1,2}):(\d{2})\s*(am|pm)/i', $pickupTime, $m)) {
        $hour = (int)$m[1];
        $ampm = strtolower($m[3]);
        if ($ampm === 'pm' && $hour < 12) $hour += 12;
        if ($ampm === 'am' && $hour == 12) $hour = 0;
        if ($hour >= 22 || $hour < 6) {
            $isNight = true;
        }
    }
    
    if ($isNight) {
        $nightCharge = (float)getFareSetting('night_charge_rate', 250);
    }
    
    // Toll & State Border Tax estimation
    $tollPer100Km = (float)getFareSetting('toll_tax_estimated_rate', 200);
    $tollTaxCharge = ($billableDistance >= 100) ? round(($billableDistance / 100) * $tollPer100Km) : 0;
    
    $totalAmount = $baseFare + $distanceCharge + $driverAllowance + $nightCharge + $tollTaxCharge;
    
    // Minimum advance payment calculation (default 25%)
    $advancePercentage = (float)getFareSetting('advance_payment_percentage', 25);
    $advanceAmount = round(($totalAmount * $advancePercentage) / 100);
    
    return [
        'vehicle_id' => $vehicle['id'],
        'vehicle_name' => $vehicle['name'],
        'per_km_rate' => $perKmRate,
        'base_fare' => $baseFare,
        'estimated_distance' => $distanceKm,
        'billable_distance' => $billableDistance,
        'distance_charge' => $distanceCharge,
        'driver_allowance' => $driverAllowance,
        'night_charge' => $nightCharge,
        'toll_tax_charge' => $tollTaxCharge,
        'total_amount' => round($totalAmount),
        'advance_amount' => $advanceAmount,
        'balance_amount' => round($totalAmount) - $advanceAmount,
        'is_night' => $isNight,
        'trip_type' => $tripType
    ];
}

function getStatusBadge(string $status): string {
    $classMap = [
        'New' => 'badge-info',
        'Payment Pending' => 'badge-warning',
        'Payment Failed' => 'badge-danger',
        'Paid' => 'badge-success',
        'Confirmed' => 'badge-primary',
        'Driver Assigned' => 'badge-purple',
        'Driver On The Way' => 'badge-cyan',
        'Trip Started' => 'badge-accent',
        'Trip Completed' => 'badge-success',
        'Cancelled' => 'badge-muted',
        'Refund Initiated' => 'badge-warning',
        'Refund Completed' => 'badge-teal',
        'Available' => 'badge-success',
        'On Trip' => 'badge-warning',
        'Off Duty' => 'badge-muted'
    ];
    $cls = $classMap[$status] ?? 'badge-secondary';
    return '<span class="status-badge ' . $cls . '">' . htmlspecialchars($status) . '</span>';
}

function setFlashMessage(string $type, string $message): void {
    $_SESSION['flash_msg'] = [
        'type' => $type, // success, error, info, warning
        'text' => $message
    ];
}

function getFlashMessage(): ?array {
    if (isset($_SESSION['flash_msg'])) {
        $msg = $_SESSION['flash_msg'];
        unset($_SESSION['flash_msg']);
        return $msg;
    }
    return null;
}
