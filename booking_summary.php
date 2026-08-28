<?php
define('PAGE_TITLE', 'Booking Summary & Review');
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/booking.php');
    exit;
}

$pickup = cleanInput($_POST['pickup_location'] ?? '');
$drop = cleanInput($_POST['drop_location'] ?? '');
$journeyDate = cleanInput($_POST['journey_date'] ?? '');
$pickupTime = cleanInput($_POST['pickup_time'] ?? '');
$returnDate = cleanInput($_POST['return_date'] ?? null);
$returnTime = cleanInput($_POST['return_time'] ?? null);
$tripType = cleanInput($_POST['trip_type'] ?? 'One Way');
$vehicleId = (int)($_POST['vehicle_id'] ?? 1);
$passengers = (int)($_POST['passengers'] ?? 1);
$customerName = cleanInput($_POST['customer_name'] ?? '');
$customerMobile = sanitizePhone(cleanInput($_POST['customer_mobile'] ?? ''));
$customerEmail = cleanInput($_POST['customer_email'] ?? '');
$flightTrainNo = cleanInput($_POST['flight_train_no'] ?? '');
$specialReq = cleanInput($_POST['special_requirements'] ?? '');

if (empty($pickup) || empty($drop) || empty($journeyDate) || empty($customerName) || empty($customerMobile)) {
    setFlashMessage('error', 'Please fill in all mandatory fields.');
    header('Location: ' . BASE_URL . '/booking.php');
    exit;
}

// Calculate Accurate Fare
$distance = estimateDistance($pickup, $drop);
$fare = calculateFare($vehicleId, $tripType, $distance, $pickupTime);

$db = Database::getConnection();

// Upsert or retrieve Customer
$cStmt = $db->prepare("SELECT id FROM customers WHERE mobile = ?");
$cStmt->execute([$customerMobile]);
$customerId = $cStmt->fetchColumn();

if (!$customerId) {
    $insC = $db->prepare("INSERT INTO customers (name, mobile, email) VALUES (?, ?, ?)");
    $insC->execute([$customerName, $customerMobile, $customerEmail]);
    $customerId = $db->lastInsertId();
}

// Generate Unique Booking ID (FR-012)
$bookingId = generateBookingId();

// Insert Booking in 'Payment Pending' state
$bStmt = $db->prepare("INSERT INTO bookings (
    booking_id, customer_id, customer_name, customer_mobile, customer_email,
    trip_type, pickup_location, drop_location, journey_date, pickup_time,
    return_date, return_time, vehicle_id, vehicle_name, passengers,
    flight_train_no, special_requirements, estimated_distance, base_fare,
    distance_charge, driver_allowance, night_charge, toll_tax_charge,
    total_amount, advance_paid, balance_amount, booking_status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$bStmt->execute([
    $bookingId, $customerId, $customerName, $customerMobile, $customerEmail,
    $tripType, $pickup, $drop, $journeyDate, $pickupTime,
    $returnDate, $returnTime, $vehicleId, $fare['vehicle_name'], $passengers,
    $flightTrainNo, $specialReq, $fare['estimated_distance'], $fare['base_fare'],
    $fare['distance_charge'], $fare['driver_allowance'], $fare['night_charge'], $fare['toll_tax_charge'],
    $fare['total_amount'], 0, $fare['total_amount'], 'Payment Pending'
]);
?>

<section class="section" style="padding-top: 40px;">
  <div class="container">
    <div class="section-header" style="margin-bottom: 30px;">
      <div class="badge-pill">Step 2 of 3 • Review Booking</div>
      <h2>Booking Summary & Fare Review</h2>
      <p>Please verify your journey details below before proceeding to secure online payment.</p>
    </div>

    <div style="max-width: 820px; margin: 0 auto;">
      <div class="booking-widget-card" style="padding: 36px;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 24px; flex-wrap: wrap; gap: 10px;">
          <div>
            <span style="font-size: 0.8rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px;">Generated Booking Reference</span>
            <h3 style="font-size: 1.5rem; color: var(--primary); margin: 0;"><?php echo htmlspecialchars($bookingId); ?></h3>
          </div>
          <div>
            <?php echo getStatusBadge('Payment Pending'); ?>
          </div>
        </div>

        <!-- Summary Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 30px;">
          <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <h4 style="font-size: 1rem; color: var(--primary); margin-bottom: 12px;">🚖 Journey Route</h4>
            <p style="margin-bottom: 6px;"><strong>Trip Type:</strong> <?php echo htmlspecialchars($tripType); ?></p>
            <p style="margin-bottom: 6px;"><strong>Pickup:</strong> <?php echo htmlspecialchars($pickup); ?></p>
            <p style="margin-bottom: 6px;"><strong>Drop:</strong> <?php echo htmlspecialchars($drop); ?></p>
            <p style="margin-bottom: 6px;"><strong>Date & Time:</strong> <?php echo date('d-M-Y', strtotime($journeyDate)); ?> at <?php echo htmlspecialchars($pickupTime); ?></p>
            <?php if ($tripType === 'Round Trip' && !empty($returnDate)): ?>
              <p style="margin-bottom: 6px;"><strong>Return:</strong> <?php echo date('d-M-Y', strtotime($returnDate)); ?> <?php echo htmlspecialchars($returnTime); ?></p>
            <?php endif; ?>
            <p style="margin-bottom: 0;"><strong>Estimated Distance:</strong> ~<?php echo $fare['estimated_distance']; ?> KM</p>
          </div>

          <div style="background: var(--bg-secondary); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <h4 style="font-size: 1rem; color: var(--primary); margin-bottom: 12px;">👤 Passenger & Vehicle</h4>
            <p style="margin-bottom: 6px;"><strong>Vehicle:</strong> <?php echo htmlspecialchars($fare['vehicle_name']); ?></p>
            <p style="margin-bottom: 6px;"><strong>Passenger:</strong> <?php echo htmlspecialchars($customerName); ?> (<?php echo $passengers; ?> Pax)</p>
            <p style="margin-bottom: 6px;"><strong>Mobile:</strong> <?php echo htmlspecialchars($customerMobile); ?></p>
            <?php if (!empty($customerEmail)): ?>
              <p style="margin-bottom: 6px;"><strong>Email:</strong> <?php echo htmlspecialchars($customerEmail); ?></p>
            <?php endif; ?>
            <?php if (!empty($flightTrainNo)): ?>
              <p style="margin-bottom: 0;"><strong>Flight/Train:</strong> <?php echo htmlspecialchars($flightTrainNo); ?></p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Detailed Fare Breakdown Box (FR-009) -->
        <div class="fare-estimate-box" style="margin-bottom: 30px;">
          <h4>
            <span>Itemized Fare Breakdown</span>
            <span style="font-size: 0.85rem; color: var(--text-dim);">Zero Hidden Charges</span>
          </h4>
          <div class="fare-row">
            <span>Base Fare:</span>
            <span>₹<?php echo number_format($fare['base_fare'], 2); ?></span>
          </div>
          <div class="fare-row">
            <span>Distance Charges (~<?php echo $fare['billable_distance']; ?> KM @ ₹<?php echo $fare['per_km_rate']; ?>/km):</span>
            <span>₹<?php echo number_format($fare['distance_charge'], 2); ?></span>
          </div>
          <?php if ($fare['driver_allowance'] > 0): ?>
            <div class="fare-row">
              <span>Driver Day Allowance:</span>
              <span>₹<?php echo number_format($fare['driver_allowance'], 2); ?></span>
            </div>
          <?php endif; ?>
          <?php if ($fare['night_charge'] > 0): ?>
            <div class="fare-row">
              <span>Night Travel Charge (10 PM - 6 AM):</span>
              <span>₹<?php echo number_format($fare['night_charge'], 2); ?></span>
            </div>
          <?php endif; ?>
          <?php if ($fare['toll_tax_charge'] > 0): ?>
            <div class="fare-row">
              <span>Estimated Toll & State Border Tax:</span>
              <span>₹<?php echo number_format($fare['toll_tax_charge'], 2); ?></span>
            </div>
          <?php endif; ?>
          <div class="fare-row total-row">
            <span>Total Payable Amount:</span>
            <strong style="font-size: 1.5rem; color: var(--primary);">₹<?php echo number_format($fare['total_amount'], 2); ?></strong>
          </div>
        </div>

        <div style="background: rgba(37, 211, 102, 0.08); border: 1px solid rgba(37, 211, 102, 0.25); border-radius: var(--radius-md); padding: 16px; margin-bottom: 30px; display: flex; align-items: center; gap: 14px;">
          <span style="font-size: 2rem;">💬</span>
          <p style="margin: 0; font-size: 0.88rem; color: #e2e8f0;">
            <strong>WhatsApp Automation Active:</strong> Upon payment completion, you will instantly receive your booking voucher, driver details, and invoice on WhatsApp at <strong>+91 <?php echo htmlspecialchars($customerMobile); ?></strong>.
          </p>
        </div>

        <div style="display: flex; gap: 16px; flex-wrap: wrap;">
          <a href="<?php echo BASE_URL; ?>/payment.php?booking_id=<?php echo urlencode($bookingId); ?>" class="btn btn-primary btn-lg" style="flex: 2;">
            <span>💳 Pay Online & Confirm Booking ➔</span>
          </a>
          <a href="<?php echo BASE_URL; ?>/booking.php" class="btn btn-secondary btn-lg" style="flex: 1;">
            <span>Edit Details</span>
          </a>
        </div>

      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
