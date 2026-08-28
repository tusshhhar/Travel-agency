<?php
define('PAGE_TITLE', 'Book a Cab Online - Instant Fare Estimation');
require_once __DIR__ . '/includes/header.php';

$db = Database::getConnection();
$vehicles = $db->query("SELECT * FROM vehicles WHERE is_active = 1 ORDER BY per_km_rate ASC")->fetchAll();
$selectedVehicleId = (int)($_GET['vehicle_id'] ?? ($vehicles[0]['id'] ?? 1));
$prefilledPickup = cleanInput($_GET['pickup'] ?? 'Haridwar');
$prefilledDrop = cleanInput($_GET['drop'] ?? 'Delhi');
?>

<section class="section" style="padding-top: 40px;">
  <div class="container">
    <div class="section-header" style="margin-bottom: 30px;">
      <div class="badge-pill">Online Booking Engine</div>
      <h2>Book Your Cab with Bishnoi Travels</h2>
      <p>Fill in your journey details below to calculate real-time estimated fare and proceed to secure booking confirmation.</p>
    </div>

    <div style="max-width: 860px; margin: 0 auto;">
      <div class="booking-widget-card" style="padding: 40px;">
        <form action="<?php echo BASE_URL; ?>/booking_summary.php" method="POST" id="mainBookingForm">
          
          <!-- Trip Type Selector (FR-005) -->
          <h4 style="margin-bottom: 14px; font-size: 1rem; color: var(--text-muted);">1. Select Journey Type</h4>
          <div class="trip-tabs">
            <button type="button" class="trip-tab-btn active" data-trip="One Way">One Way</button>
            <button type="button" class="trip-tab-btn" data-trip="Round Trip">Round Trip</button>
            <button type="button" class="trip-tab-btn" data-trip="Local">Local City Tour</button>
            <button type="button" class="trip-tab-btn" data-trip="Airport Transfer">Airport Transfer</button>
          </div>
          <input type="hidden" name="trip_type" id="trip_type_input" value="One Way">

          <!-- Location Information (FR-006, FR-007) -->
          <h4 style="margin: 24px 0 14px; font-size: 1rem; color: var(--text-muted);">2. Route & Location Details</h4>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="pickup_location">📍 Pickup Location / Address *</label>
              <input type="text" name="pickup_location" id="pickup_location" class="form-control" placeholder="e.g. Haridwar Railway Station / Hotel" value="<?php echo htmlspecialchars($prefilledPickup); ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="drop_location">📍 Drop Location / City *</label>
              <input type="text" name="drop_location" id="drop_location" class="form-control" placeholder="e.g. IGI Airport Terminal 3, Delhi" value="<?php echo htmlspecialchars($prefilledDrop); ?>" required>
            </div>
          </div>

          <!-- Date & Time Information -->
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="journey_date">📅 Journey Date *</label>
              <input type="date" name="journey_date" id="journey_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="pickup_time">⏰ Pickup Time *</label>
              <select name="pickup_time" id="pickup_time" class="form-control" required>
                <option value="05:00 AM (Early)">05:00 AM (Early)</option>
                <option value="06:00 AM">06:00 AM</option>
                <option value="07:00 AM">07:00 AM</option>
                <option value="08:00 AM" selected>08:00 AM</option>
                <option value="09:00 AM">09:00 AM</option>
                <option value="10:00 AM">10:00 AM</option>
                <option value="11:00 AM">11:00 AM</option>
                <option value="12:00 PM">12:00 PM</option>
                <option value="02:00 PM">02:00 PM</option>
                <option value="04:00 PM">04:00 PM</option>
                <option value="06:00 PM">06:00 PM</option>
                <option value="08:00 PM">08:00 PM</option>
                <option value="10:00 PM (Night)">10:00 PM (Night)</option>
                <option value="11:30 PM (Night)">11:30 PM (Night)</option>
              </select>
            </div>
          </div>

          <!-- Return Date & Time for Round Trips -->
          <div class="form-grid" id="return_date_group" style="display: none;">
            <div class="form-group">
              <label class="form-label" for="return_date">📅 Return Date</label>
              <input type="date" name="return_date" id="return_date" class="form-control" min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="return_time">⏰ Return Time</label>
              <input type="text" name="return_time" id="return_time" class="form-control" placeholder="e.g. 06:00 PM">
            </div>
          </div>

          <!-- Vehicle Selection & Capacity (FR-003, FR-004) -->
          <h4 style="margin: 24px 0 14px; font-size: 1rem; color: var(--text-muted);">3. Cab & Passenger Details</h4>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="vehicle_id">🚘 Vehicle Type *</label>
              <select name="vehicle_id" id="vehicle_id" class="form-control" required>
                <?php foreach ($vehicles as $v): ?>
                  <option value="<?php echo $v['id']; ?>" <?php echo $v['id'] === $selectedVehicleId ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($v['name']); ?> — ₹<?php echo $v['per_km_rate']; ?>/KM (<?php echo $v['seating_capacity']; ?> Seats)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="passengers">👥 Total Passengers</label>
              <select name="passengers" id="passengers" class="form-control">
                <option value="1">1 Passenger</option>
                <option value="2" selected>2 Passengers</option>
                <option value="3">3 Passengers</option>
                <option value="4">4 Passengers</option>
                <option value="5">5 Passengers</option>
                <option value="6">6 Passengers</option>
                <option value="7">7+ Passengers (MUV/Tempo)</option>
              </select>
            </div>
          </div>

          <!-- Customer Contact Information -->
          <h4 style="margin: 24px 0 14px; font-size: 1rem; color: var(--text-muted);">4. Customer Contact Information</h4>
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="customer_name">👤 Passenger Full Name *</label>
              <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="Enter Full Name" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="customer_mobile">📱 Mobile Number (WhatsApp) *</label>
              <input type="tel" name="customer_mobile" id="customer_mobile" class="form-control" placeholder="10-digit mobile number" pattern="[0-9]{10}" required>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="customer_email">✉️ Email Address (Optional)</label>
              <input type="email" name="customer_email" id="customer_email" class="form-control" placeholder="name@example.com">
            </div>
            <div class="form-group">
              <label class="form-label" for="flight_train_no">✈️ Flight / Train No. (Optional)</label>
              <input type="text" name="flight_train_no" id="flight_train_no" class="form-control" placeholder="e.g. AI-432 / Shatabdi 12017">
            </div>
          </div>

          <div class="form-group full-width" style="margin-bottom: 20px;">
            <label class="form-label" for="special_requirements">📝 Special Requirements / Notes</label>
            <textarea name="special_requirements" id="special_requirements" class="form-control" placeholder="Luggage details, child seat requirement, intermediate stops, etc."></textarea>
          </div>

          <!-- Live Fare Estimation Box (FR-008, FR-009) -->
          <div class="fare-estimate-box" id="live_fare_display">
            <h4>
              <span>Live Fare Estimate</span>
              <span class="badge-pill" style="font-size: 0.75rem; margin:0;">Dynamic Pricing Engine</span>
            </h4>
            <div class="fare-row">
              <span>Approx Distance:</span>
              <strong id="est_distance_txt">220 KM</strong>
            </div>
            <div class="fare-row">
              <span>Per KM Rate:</span>
              <span id="est_rate_txt">₹11/KM</span>
            </div>
            <div class="fare-row">
              <span>Base Fare:</span>
              <span id="base_fare_txt">₹1,200</span>
            </div>
            <div class="fare-row">
              <span>Distance Charge:</span>
              <span id="distance_charge_txt">₹2,420</span>
            </div>
            <div class="fare-row" id="driver_allowance_row" style="display:none;">
              <span>Driver Allowance:</span>
              <span id="driver_allowance_txt">₹0</span>
            </div>
            <div class="fare-row" id="night_charge_row" style="display:none;">
              <span>Night Charge:</span>
              <span id="night_charge_txt">₹250</span>
            </div>
            <div class="fare-row" id="toll_tax_row">
              <span>Toll / Border Tax (Est.):</span>
              <span id="toll_tax_txt">₹400</span>
            </div>
            <div class="fare-row total-row">
              <span>Total Estimated Fare:</span>
              <strong id="total_fare_txt">₹3,820</strong>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg btn-block">
            <span>Confirm Details & Review Summary ➔</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
