<?php
define('PAGE_TITLE', 'Track & Manage Booking');
require_once __DIR__ . '/includes/header.php';

$bookingId = cleanInput($_GET['booking_id'] ?? '');
$mobile = sanitizePhone(cleanInput($_GET['mobile'] ?? ''));

$booking = null;
$error = null;

if (!empty($bookingId) && !empty($mobile)) {
    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = ? AND customer_mobile LIKE ?");
    $stmt->execute([$bookingId, '%' . $mobile . '%']);
    $booking = $stmt->fetch();

    if (!$booking) {
        $error = "No booking found with Reference ID '{$bookingId}' and Mobile '{$mobile}'. Please verify and try again.";
    }
}
?>

<section class="section" style="padding-top: 40px;">
  <div class="container">
    <div class="section-header" style="margin-bottom: 30px;">
      <div class="badge-pill">Self-Service Portal</div>
      <h2>Track Your Cab & Booking Status</h2>
      <p>Enter your Booking Reference ID and registered Mobile Number to check live status or manage your trip.</p>
    </div>

    <div style="max-width: 760px; margin: 0 auto;">
      
      <!-- Lookup Form -->
      <div class="booking-widget-card" style="padding: 30px; margin-bottom: 30px;">
        <form action="<?php echo BASE_URL; ?>/track_booking.php" method="GET">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="booking_id">🔍 Booking ID *</label>
              <input type="text" name="booking_id" id="booking_id" class="form-control" placeholder="e.g. BT-<?php echo date('Ymd'); ?>-001" value="<?php echo htmlspecialchars($bookingId); ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="mobile">📱 Registered Mobile *</label>
              <input type="tel" name="mobile" id="mobile" class="form-control" placeholder="10-digit mobile number" value="<?php echo htmlspecialchars($mobile); ?>" pattern="[0-9]{10}" required>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block">
            <span>Find Booking Details ➔</span>
          </button>
        </form>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <span>❌ <?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <!-- If Booking Found -->
      <?php if ($booking): ?>
        <div class="booking-widget-card" style="padding: 36px; border-color: var(--primary);">
          <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 24px; flex-wrap: wrap; gap: 10px;">
            <div>
              <span style="font-size: 0.8rem; color: var(--text-dim); text-transform: uppercase;">Booking Reference</span>
              <h3 style="font-size: 1.5rem; color: var(--primary); margin: 0;"><?php echo htmlspecialchars($booking['booking_id']); ?></h3>
            </div>
            <div>
              <?php echo getStatusBadge($booking['booking_status']); ?>
            </div>
          </div>

          <div class="info-two-col" style="margin-bottom: 24px; font-size: 0.9rem;">
            <div>
              <span style="color: var(--text-dim); display: block;">Route:</span>
              <strong><?php echo htmlspecialchars($booking['pickup_location']); ?> ➔ <?php echo htmlspecialchars($booking['drop_location']); ?></strong>
            </div>
            <div>
              <span style="color: var(--text-dim); display: block;">Date & Time:</span>
              <strong><?php echo date('d-M-Y', strtotime($booking['journey_date'])); ?> at <?php echo htmlspecialchars($booking['pickup_time']); ?></strong>
            </div>
            <div>
              <span style="color: var(--text-dim); display: block;">Vehicle:</span>
              <strong><?php echo htmlspecialchars($booking['vehicle_name']); ?></strong>
            </div>
            <div>
              <span style="color: var(--text-dim); display: block;">Payment:</span>
              <strong style="color: #34d399;">Pay to Driver on Trip (₹<?php echo number_format($booking['total_amount'], 2); ?>)</strong>
            </div>
            <div>
              <span style="color: var(--text-dim); display: block;">Driver Assigned:</span>
              <strong><?php echo !empty($booking['assigned_driver_name']) ? htmlspecialchars($booking['assigned_driver_name']) . ' (' . htmlspecialchars($booking['assigned_driver_mobile']) . ')' : 'Driver will be assigned 2 hours prior to journey'; ?></strong>
            </div>
            <div>
              <span style="color: var(--text-dim); display: block;">Vehicle Number:</span>
              <strong><?php echo !empty($booking['assigned_vehicle_no']) ? htmlspecialchars($booking['assigned_vehicle_no']) : 'To be assigned'; ?></strong>
            </div>
          </div>

          <!-- Cancellation Section -->
          <?php if (!in_array($booking['booking_status'], ['Cancelled', 'Trip Completed'])): ?>
            <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: var(--radius-md); padding: 20px; margin-top: 24px;">
              <h4 style="font-size: 1rem; color: #f87171; margin-bottom: 8px;">Cancel Booking</h4>
              <p style="font-size: 0.85rem; margin-bottom: 14px;">
                Free instant cancellation. Let us know if your plan has changed or if you need to reschedule.
              </p>
              
              <form id="cancelForm" onsubmit="handleCancel(event)">
                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking['booking_id']); ?>">
                <input type="hidden" name="mobile" value="<?php echo htmlspecialchars($booking['customer_mobile']); ?>">
                <div class="form-group" style="margin-bottom: 12px;">
                  <label class="form-label" style="font-size: 0.8rem;">Reason for cancellation (optional):</label>
                  <input type="text" name="reason" id="cancel_reason" class="form-control" placeholder="e.g. Plan changed / Travel rescheduled">
                </div>
                <button type="submit" class="btn btn-secondary btn-sm" style="border-color: #ef4444; color: #f87171;">
                  <span>❌ Confirm Cancellation</span>
                </button>
              </form>
              <div id="cancel_alert" style="margin-top: 14px; display: none;"></div>
            </div>
          <?php endif; ?>

          <div style="margin-top: 24px; display: flex; gap: 12px;">
            <a href="<?php echo BASE_URL; ?>/invoice.php?booking_id=<?php echo urlencode($booking['booking_id']); ?>" target="_blank" class="btn btn-primary btn-sm">
              <span>📄 View Travel Voucher</span>
            </a>
            <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>" target="_blank" class="btn btn-whatsapp btn-sm">
              <span>💬 Contact Support on WhatsApp</span>
            </a>
          </div>

        </div>
      <?php endif; ?>

    </div>
  </div>
</section>

<script>
function handleCancel(e) {
  e.preventDefault();
  if (!confirm("Are you sure you want to cancel this booking?")) return;

  const form = document.getElementById('cancelForm');
  const alertBox = document.getElementById('cancel_alert');
  const bookingId = form.booking_id.value;
  const mobile = form.mobile.value;
  const reason = form.reason.value;

  alertBox.style.display = 'block';
  alertBox.className = 'alert alert-warning';
  alertBox.textContent = 'Processing cancellation...';

  fetch('api/cancel_booking.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ booking_id: bookingId, mobile: mobile, reason: reason })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alertBox.className = 'alert alert-success';
      alertBox.textContent = '✅ ' + data.message;
      setTimeout(() => { location.reload(); }, 1500);
    } else {
      alertBox.className = 'alert alert-error';
      alertBox.textContent = '❌ ' + (data.message || 'Cancellation failed.');
    }
  })
  .catch(err => {
    alertBox.className = 'alert alert-error';
    alertBox.textContent = '❌ Network error processing cancellation.';
  });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
