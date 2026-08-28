<?php
define('PAGE_TITLE', 'Booking Confirmed - Bishnoi Travels');
require_once __DIR__ . '/includes/header.php';

$bookingId = cleanInput($_GET['booking_id'] ?? '');

if (empty($bookingId)) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if (!$booking) {
    echo "<div class='container' style='padding: 50px;'><div class='alert alert-error'>Booking not found.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}
?>

<section class="section" style="padding-top: 40px;">
  <div class="container">
    <div style="max-width: 780px; margin: 0 auto;">
      
      <!-- Success Banner Card -->
      <div class="booking-widget-card" style="padding: 40px; text-align: center; border-color: rgba(16, 185, 129, 0.4);">
        
        <div style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.15); border: 2px solid #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px;">
          ✅
        </div>

        <h2 style="font-size: 2rem; color: #10b981; margin-bottom: 8px;">Booking Confirmed!</h2>
        <p style="font-size: 1rem; color: var(--text-muted); margin-bottom: 24px;">
          Thank you, <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>. Your cab booking with Bishnoi Travels has been successfully confirmed and scheduled.
        </p>

        <!-- Booking ID Callout -->
        <div style="background: var(--bg-secondary); border: 1px dashed var(--primary); border-radius: var(--radius-md); padding: 18px; display: inline-block; margin-bottom: 30px;">
          <span style="display: block; font-size: 0.75rem; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px;">Your Booking Reference ID</span>
          <h3 style="font-size: 1.8rem; color: var(--primary); font-family: var(--font-heading); margin: 4px 0 0;"><?php echo htmlspecialchars($booking['booking_id']); ?></h3>
        </div>

        <!-- WhatsApp Notification Badge (FR-017) -->
        <div style="background: rgba(37, 211, 102, 0.1); border: 1px solid rgba(37, 211, 102, 0.3); border-radius: var(--radius-md); padding: 16px; margin-bottom: 30px; text-align: left; display: flex; align-items: center; gap: 14px;">
          <span style="font-size: 2rem;">💬</span>
          <div>
            <h4 style="font-size: 0.95rem; color: #34d399; margin: 0 0 2px;">WhatsApp Confirmation Sent!</h4>
            <p style="margin: 0; font-size: 0.85rem; color: #e2e8f0;">
              An automated WhatsApp message with complete journey details, invoice receipt, and driver helpline has been dispatched to <strong>+91 <?php echo htmlspecialchars($booking['customer_mobile']); ?></strong>.
            </p>
          </div>
        </div>

        <!-- Journey Summary Table -->
        <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 24px; text-align: left; margin-bottom: 30px;">
          <h4 style="font-size: 1rem; color: #fff; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            📋 Trip & Payment Overview
          </h4>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 0.9rem;">
            <div>
              <span style="color: var(--text-dim); display: block;">Pickup Location:</span>
              <strong><?php echo htmlspecialchars($booking['pickup_location']); ?></strong>
            </div>
            <div>
              <span style="color: var(--text-dim); display: block;">Drop Location:</span>
              <strong><?php echo htmlspecialchars($booking['drop_location']); ?></strong>
            </div>
            <div>
              <span style="color: var(--text-dim); display: block;">Travel Date & Time:</span>
              <strong><?php echo date('d-M-Y', strtotime($booking['journey_date'])); ?> (<?php echo htmlspecialchars($booking['pickup_time']); ?>)</strong>
            </div>
            <div>
              <span style="color: var(--text-dim); display: block;">Assigned Cab:</span>
              <strong><?php echo htmlspecialchars($booking['vehicle_name']); ?></strong>
            </div>
            <div>
              <span style="color: var(--text-dim); display: block;">Total Paid:</span>
              <strong style="color: var(--primary);">₹<?php echo number_format($booking['total_amount'], 2); ?> (PAID)</strong>
            </div>
            <div>
              <span style="color: var(--text-dim); display: block;">Assigned Driver:</span>
              <strong><?php echo !empty($booking['assigned_driver_name']) ? htmlspecialchars($booking['assigned_driver_name']) . ' (' . htmlspecialchars($booking['assigned_driver_mobile']) . ')' : 'Will be assigned 2 hours prior'; ?></strong>
            </div>
          </div>
        </div>

        <!-- Action CTAs -->
        <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
          <a href="<?php echo BASE_URL; ?>/invoice.php?booking_id=<?php echo urlencode($booking['booking_id']); ?>" target="_blank" class="btn btn-primary btn-lg">
            <span>📄 Print / Download Voucher</span>
          </a>
          <a href="<?php echo BASE_URL; ?>/track_booking.php?booking_id=<?php echo urlencode($booking['booking_id']); ?>&mobile=<?php echo urlencode($booking['customer_mobile']); ?>" class="btn btn-secondary btn-lg">
            <span>🔍 Track Booking Status</span>
          </a>
          <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-secondary btn-lg">
            <span>Home</span>
          </a>
        </div>

      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
