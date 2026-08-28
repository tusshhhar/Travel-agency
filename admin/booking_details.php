<?php
define('ADMIN_PAGE_TITLE', 'Booking Detailed View');
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../includes/whatsapp_helper.php';

$id = (int)($_GET['id'] ?? 0);
$db = Database::getConnection();

$stmt = $db->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$id]);
$b = $stmt->fetch();

if (!$b) {
    echo "<div class='alert alert-error'>Booking not found.</div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

// Fetch Payments
$pStmt = $db->prepare("SELECT * FROM payments WHERE booking_id = ? ORDER BY id DESC");
$pStmt->execute([$b['booking_id']]);
$payments = $pStmt->fetchAll();

// Fetch WhatsApp history for this booking
$wStmt = $db->prepare("SELECT * FROM whatsapp_logs WHERE booking_id = ? OR phone_number LIKE ? ORDER BY id DESC");
$wStmt->execute([$b['booking_id'], '%' . $b['customer_mobile'] . '%']);
$whatsappLogs = $wStmt->fetchAll();

// Handle Manual WhatsApp Trigger
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_custom_whatsapp'])) {
    $customMsg = cleanInput($_POST['custom_message'] ?? '');
    if (!empty($customMsg)) {
        WhatsAppHelper::sendMessage($b['customer_mobile'], $customMsg, $b['booking_id'], 'admin_custom');
        setFlashMessage('success', "WhatsApp message sent to customer!");
        header('Location: ' . BASE_URL . '/admin/booking_details.php?id=' . $id);
        exit;
    }
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
  <div>
    <a href="<?php echo BASE_URL; ?>/admin/bookings.php" style="color: var(--text-dim); text-decoration: none; font-size: 0.9rem;">← Back to Bookings</a>
    <h2 style="font-size: 1.6rem; color: #fff; margin: 4px 0 0;">
      Booking: <span style="color: var(--primary);"><?php echo htmlspecialchars($b['booking_id']); ?></span>
    </h2>
  </div>
  <div style="display: flex; gap: 10px;">
    <a href="<?php echo BASE_URL; ?>/invoice.php?booking_id=<?php echo urlencode($b['booking_id']); ?>" target="_blank" class="btn btn-secondary btn-sm">
      <span>📄 Print Travel Voucher</span>
    </a>
    <a href="https://wa.me/91<?php echo htmlspecialchars($b['customer_mobile']); ?>" target="_blank" class="btn btn-whatsapp btn-sm">
      <span>💬 Chat on WhatsApp</span>
    </a>
  </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
  
  <!-- Left Main Column -->
  <div>
    
    <!-- Journey & Route Card -->
    <div class="card-table" style="padding: 24px; margin-bottom: 24px;">
      <h3 style="margin-bottom: 16px; color: #fff; font-size: 1.15rem;">🚖 Journey & Route Information</h3>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 0.9rem;">
        <div>
          <span style="color: var(--text-dim); display: block;">Trip Type:</span>
          <strong><?php echo htmlspecialchars($b['trip_type']); ?></strong>
        </div>
        <div>
          <span style="color: var(--text-dim); display: block;">Booking Status:</span>
          <?php echo getStatusBadge($b['booking_status']); ?>
        </div>
        <div>
          <span style="color: var(--text-dim); display: block;">Pickup Location:</span>
          <strong><?php echo htmlspecialchars($b['pickup_location']); ?></strong>
        </div>
        <div>
          <span style="color: var(--text-dim); display: block;">Drop Destination:</span>
          <strong><?php echo htmlspecialchars($b['drop_location']); ?></strong>
        </div>
        <div>
          <span style="color: var(--text-dim); display: block;">Journey Date & Time:</span>
          <strong><?php echo date('d-M-Y', strtotime($b['journey_date'])); ?> at <?php echo htmlspecialchars($b['pickup_time']); ?></strong>
        </div>
        <?php if (!empty($b['return_date'])): ?>
          <div>
            <span style="color: var(--text-dim); display: block;">Return Date:</span>
            <strong><?php echo date('d-M-Y', strtotime($b['return_date'])); ?> (<?php echo htmlspecialchars($b['return_time']); ?>)</strong>
          </div>
        <?php endif; ?>
        <div>
          <span style="color: var(--text-dim); display: block;">Estimated Distance:</span>
          <strong>~<?php echo $b['estimated_distance']; ?> KM</strong>
        </div>
        <div>
          <span style="color: var(--text-dim); display: block;">Vehicle Type:</span>
          <strong><?php echo htmlspecialchars($b['vehicle_name']); ?></strong>
        </div>
      </div>

      <?php if (!empty($b['special_requirements'])): ?>
        <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--admin-border); font-size: 0.88rem;">
          <span style="color: var(--text-dim); display: block;">Special Requirements / Instructions:</span>
          <em><?php echo htmlspecialchars($b['special_requirements']); ?></em>
        </div>
      <?php endif; ?>
    </div>

    <!-- Payment & Gateway Logs Card -->
    <div class="card-table" style="padding: 24px; margin-bottom: 24px;">
      <h3 style="margin-bottom: 16px; color: #fff; font-size: 1.15rem;">💳 Payment Transactions</h3>
      <?php if (empty($payments)): ?>
        <p style="color: var(--text-dim); font-size: 0.9rem;">No recorded payments for this booking yet.</p>
      <?php else: ?>
        <table class="data-table">
          <thead>
            <tr>
              <th>Gateway</th>
              <th>Transaction ID</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $p): ?>
              <tr>
                <td><?php echo htmlspecialchars($p['gateway']); ?></td>
                <td><code style="color: var(--primary);"><?php echo htmlspecialchars($p['transaction_id']); ?></code></td>
                <td><strong>₹<?php echo number_format($p['amount'], 2); ?></strong></td>
                <td><?php echo getStatusBadge($p['payment_status']); ?></td>
                <td><?php echo date('d-M-Y H:i', strtotime($p['created_at'])); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <!-- WhatsApp Logs Card -->
    <div class="card-table" style="padding: 24px;">
      <h3 style="margin-bottom: 16px; color: #fff; font-size: 1.15rem;">💬 WhatsApp Message History</h3>
      <?php if (empty($whatsappLogs)): ?>
        <p style="color: var(--text-dim); font-size: 0.9rem;">No WhatsApp messages logged for this customer yet.</p>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
          <?php foreach ($whatsappLogs as $w): ?>
            <div style="background: var(--admin-sidebar); border: 1px solid var(--admin-border); border-radius: 8px; padding: 14px; font-size: 0.85rem;">
              <div style="display: flex; justify-content: space-between; margin-bottom: 6px; color: var(--text-dim);">
                <span>
                  <strong><?php echo strtoupper($w['message_direction']); ?></strong> • Template: <?php echo htmlspecialchars($w['template_name'] ?: 'Text'); ?>
                </span>
                <span><?php echo date('d-M-Y H:i', strtotime($w['created_at'])); ?></span>
              </div>
              <div style="white-space: pre-wrap; color: #e2e8f0;"><?php echo htmlspecialchars($w['message_body']); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>

  <!-- Right Sidebar Column -->
  <div>
    
    <!-- Customer Card -->
    <div class="card-table" style="padding: 24px; margin-bottom: 24px;">
      <h3 style="margin-bottom: 14px; color: #fff; font-size: 1.1rem;">👤 Customer Profile</h3>
      <p style="margin-bottom: 6px; font-size: 0.9rem;"><strong>Name:</strong> <?php echo htmlspecialchars($b['customer_name']); ?></p>
      <p style="margin-bottom: 6px; font-size: 0.9rem;"><strong>Phone:</strong> <?php echo htmlspecialchars($b['customer_mobile']); ?></p>
      <p style="margin-bottom: 6px; font-size: 0.9rem;"><strong>Email:</strong> <?php echo htmlspecialchars($b['customer_email'] ?: 'N/A'); ?></p>
      <p style="margin-bottom: 0; font-size: 0.9rem;"><strong>Passengers:</strong> <?php echo $b['passengers']; ?> Pax</p>
    </div>

    <!-- Assigned Driver Card -->
    <div class="card-table" style="padding: 24px; margin-bottom: 24px;">
      <h3 style="margin-bottom: 14px; color: #fff; font-size: 1.1rem;">🚖 Assigned Driver</h3>
      <?php if (!empty($b['assigned_driver_name'])): ?>
        <p style="margin-bottom: 6px; font-size: 0.9rem;"><strong>Driver:</strong> <?php echo htmlspecialchars($b['assigned_driver_name']); ?></p>
        <p style="margin-bottom: 6px; font-size: 0.9rem;"><strong>Contact:</strong> <?php echo htmlspecialchars($b['assigned_driver_mobile']); ?></p>
        <p style="margin-bottom: 14px; font-size: 0.9rem;"><strong>Cab No:</strong> <?php echo htmlspecialchars($b['assigned_vehicle_no']); ?></p>
      <?php else: ?>
        <p style="color: var(--text-dim); font-size: 0.85rem; margin-bottom: 14px;">No driver assigned yet.</p>
      <?php endif; ?>
    </div>

    <!-- Send Custom WhatsApp Message -->
    <div class="card-table" style="padding: 24px;">
      <h3 style="margin-bottom: 14px; color: #fff; font-size: 1.1rem;">💬 Send WhatsApp Update</h3>
      <form action="<?php echo BASE_URL; ?>/admin/booking_details.php?id=<?php echo $b['id']; ?>" method="POST">
        <div class="form-group" style="margin-bottom: 14px;">
          <textarea name="custom_message" class="form-control" placeholder="Type custom message or trip update to customer..." required style="min-height: 100px; font-size: 0.85rem;"></textarea>
        </div>
        <button type="submit" name="send_custom_whatsapp" class="btn btn-whatsapp btn-block btn-sm">
          <span>Send via WhatsApp ➔</span>
        </button>
      </form>
    </div>

  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
