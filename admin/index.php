<?php
define('ADMIN_PAGE_TITLE', 'Dashboard & Overview');
require_once __DIR__ . '/header.php';

$db = Database::getConnection();

// Calculate Analytics KPIs
$totalBookings = (int)$db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$todayBookings = (int)$db->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURRENT_DATE")->fetchColumn();
$confirmedBookings = (int)$db->query("SELECT COUNT(*) FROM bookings WHERE booking_status IN ('Confirmed', 'Driver Assigned', 'Driver On The Way', 'Trip Started', 'Trip Completed')")->fetchColumn();
$cancelledBookings = (int)$db->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Cancelled'")->fetchColumn();
$totalRevenue = (float)$db->query("SELECT SUM(advance_paid) FROM bookings WHERE advance_paid > 0")->fetchColumn();
$newEnquiries = (int)$db->query("SELECT COUNT(*) FROM enquiries WHERE status = 'New'")->fetchColumn();

// Fetch Recent Bookings
$recentBookings = $db->query("SELECT * FROM bookings ORDER BY id DESC LIMIT 6")->fetchAll();

// Fetch Available Drivers for Quick Assign Modal
$drivers = $db->query("SELECT * FROM drivers WHERE is_active = 1")->fetchAll();
?>

<!-- KPI Cards -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-info">
      <h5>Total Bookings</h5>
      <h3><?php echo number_format($totalBookings); ?></h3>
    </div>
    <div class="stat-icon">🚖</div>
  </div>

  <div class="stat-card">
    <div class="stat-info">
      <h5>Today's Bookings</h5>
      <h3 style="color: var(--accent-cyan);"><?php echo number_format($todayBookings); ?></h3>
    </div>
    <div class="stat-icon" style="color: var(--accent-cyan); background: rgba(6, 182, 212, 0.12);">📅</div>
  </div>

  <div class="stat-card">
    <div class="stat-info">
      <h5>Total Revenue</h5>
      <h3 style="color: var(--accent-emerald);">₹<?php echo number_format($totalRevenue, 2); ?></h3>
    </div>
    <div class="stat-icon" style="color: var(--accent-emerald); background: rgba(16, 185, 129, 0.12);">💰</div>
  </div>

  <div class="stat-card">
    <div class="stat-info">
      <h5>Confirmed Trips</h5>
      <h3 style="color: var(--primary);"><?php echo number_format($confirmedBookings); ?></h3>
    </div>
    <div class="stat-icon">✅</div>
  </div>

  <div class="stat-card">
    <div class="stat-info">
      <h5>New Enquiries</h5>
      <h3 style="color: #c084fc;"><?php echo number_format($newEnquiries); ?></h3>
    </div>
    <div class="stat-icon" style="color: #c084fc; background: rgba(168, 85, 247, 0.12);">✉️</div>
  </div>
</div>

<!-- Quick Action Shortcuts -->
<div style="display: flex; gap: 12px; margin-bottom: 30px; flex-wrap: wrap;">
  <a href="<?php echo BASE_URL; ?>/admin/bookings.php" class="btn btn-primary btn-sm">
    <span>🚖 Manage All Bookings</span>
  </a>
  <a href="<?php echo BASE_URL; ?>/admin/vehicles.php" class="btn btn-secondary btn-sm">
    <span>🚘 Update Fleet & Rates</span>
  </a>
  <a href="<?php echo BASE_URL; ?>/admin/drivers.php" class="btn btn-secondary btn-sm">
    <span>👤 Assign Drivers</span>
  </a>
  <a href="<?php echo BASE_URL; ?>/admin/whatsapp_logs.php" class="btn btn-whatsapp btn-sm">
    <span>💬 WhatsApp Live Logs</span>
  </a>
</div>

<!-- Recent Bookings Table -->
<div class="card-table">
  <div class="card-table-header">
    <h3>Recent Cab Bookings</h3>
    <a href="<?php echo BASE_URL; ?>/admin/bookings.php" class="btn btn-secondary btn-sm">View All Bookings ➔</a>
  </div>

  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr>
          <th>Booking ID</th>
          <th>Customer</th>
          <th>Route</th>
          <th>Travel Date</th>
          <th>Vehicle</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Driver</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recentBookings)): ?>
          <tr>
            <td colspan="9" style="text-align: center; color: var(--text-dim); padding: 30px;">No bookings found.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($recentBookings as $b): ?>
            <tr>
              <td>
                <strong style="color: var(--primary);"><?php echo htmlspecialchars($b['booking_id']); ?></strong>
              </td>
              <td>
                <div><?php echo htmlspecialchars($b['customer_name']); ?></div>
                <small style="color: var(--text-dim);"><?php echo htmlspecialchars($b['customer_mobile']); ?></small>
              </td>
              <td>
                <div><?php echo htmlspecialchars($b['pickup_location']); ?> ➔</div>
                <small style="color: var(--text-dim);"><?php echo htmlspecialchars($b['drop_location']); ?></small>
              </td>
              <td>
                <div><?php echo date('d-M-Y', strtotime($b['journey_date'])); ?></div>
                <small style="color: var(--text-dim);"><?php echo htmlspecialchars($b['pickup_time']); ?></small>
              </td>
              <td><?php echo htmlspecialchars($b['vehicle_name']); ?></td>
              <td>
                <strong>₹<?php echo number_format($b['total_amount'], 2); ?></strong>
              </td>
              <td>
                <?php echo getStatusBadge($b['booking_status']); ?>
              </td>
              <td>
                <?php if (!empty($b['assigned_driver_name'])): ?>
                  <span style="color: #34d399;"><?php echo htmlspecialchars($b['assigned_driver_name']); ?></span>
                <?php else: ?>
                  <button type="button" onclick="openAssignModal('<?php echo $b['booking_id']; ?>', '<?php echo $b['driver_id']; ?>')" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">
                    + Assign Driver
                  </button>
                <?php endif; ?>
              </td>
              <td>
                <div style="display: flex; gap: 6px;">
                  <a href="<?php echo BASE_URL; ?>/admin/booking_details.php?id=<?php echo $b['id']; ?>" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">
                    View
                  </a>
                  <button type="button" onclick="openStatusModal('<?php echo $b['booking_id']; ?>', '<?php echo $b['booking_status']; ?>')" class="btn btn-primary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">
                    Status
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Driver Assignment Modal (FR-025) -->
<div class="admin-modal-backdrop" id="assignDriverModal">
  <div class="admin-modal-box">
    <div class="modal-header">
      <h3 style="color: #fff; margin: 0;">Assign Driver to Cab</h3>
      <button type="button" onclick="closeModal('assignDriverModal')" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;">&times;</button>
    </div>
    <form action="<?php echo BASE_URL; ?>/admin/bookings.php" method="POST">
      <input type="hidden" name="action" value="assign_driver">
      <input type="hidden" name="booking_id" id="modal_booking_id" value="">
      
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Select Driver *</label>
        <select name="driver_id" id="modal_driver_id" class="form-control" required>
          <option value="">-- Choose Driver --</option>
          <?php foreach ($drivers as $d): ?>
            <option value="<?php echo $d['id']; ?>">
              <?php echo htmlspecialchars($d['name']); ?> (<?php echo htmlspecialchars($d['mobile']); ?>) — <?php echo htmlspecialchars($d['vehicle_number'] ?: 'Cab'); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" onclick="closeModal('assignDriverModal')" class="btn btn-secondary">Cancel</button>
        <button type="submit" class="btn btn-primary">Assign Driver & Send WhatsApp Alert</button>
      </div>
    </form>
  </div>
</div>

<!-- Status Update Modal (FR-021, FR-023) -->
<div class="admin-modal-backdrop" id="updateStatusModal">
  <div class="admin-modal-box">
    <div class="modal-header">
      <h3 style="color: #fff; margin: 0;">Update Journey / Trip Status</h3>
      <button type="button" onclick="closeModal('updateStatusModal')" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;">&times;</button>
    </div>
    <form action="<?php echo BASE_URL; ?>/admin/bookings.php" method="POST">
      <input type="hidden" name="action" value="update_status">
      <input type="hidden" name="booking_id" id="status_modal_booking_id" value="">
      
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Trip Status *</label>
        <select name="status" id="status_modal_status" class="form-control" required>
          <option value="New">New</option>
          <option value="Payment Pending">Payment Pending</option>
          <option value="Confirmed">Confirmed</option>
          <option value="Driver Assigned">Driver Assigned</option>
          <option value="Driver On The Way">Driver On The Way</option>
          <option value="Trip Started">Trip Started</option>
          <option value="Trip Completed">Trip Completed</option>
          <option value="Cancelled">Cancelled</option>
          <option value="Refund Completed">Refund Completed</option>
        </select>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" onclick="closeModal('updateStatusModal')" class="btn btn-secondary">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Status</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
