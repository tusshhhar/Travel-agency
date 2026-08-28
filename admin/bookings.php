<?php
define('ADMIN_PAGE_TITLE', 'Manage Cab Bookings');
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../includes/whatsapp_helper.php';

$db = Database::getConnection();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = cleanInput($_POST['action'] ?? '');
    $bookingId = cleanInput($_POST['booking_id'] ?? '');

    if ($action === 'assign_driver') {
        $driverId = (int)($_POST['driver_id'] ?? 0);
        $dStmt = $db->prepare("SELECT * FROM drivers WHERE id = ?");
        $dStmt->execute([$driverId]);
        $driver = $dStmt->fetch();

        if ($driver && !empty($bookingId)) {
            $uStmt = $db->prepare("UPDATE bookings SET driver_id = ?, assigned_driver_name = ?, assigned_driver_mobile = ?, assigned_vehicle_no = ?, booking_status = 'Driver Assigned' WHERE booking_id = ?");
            $uStmt->execute([$driver['id'], $driver['name'], $driver['mobile'], $driver['vehicle_number'], $bookingId]);

            // Fetch booking and send WhatsApp notification (FR-023)
            $bStmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
            $bStmt->execute([$bookingId]);
            $booking = $bStmt->fetch();

            if ($booking) {
                WhatsAppHelper::sendDriverAssignedNotification($booking);
            }
            setFlashMessage('success', "Driver {$driver['name']} successfully assigned to {$bookingId} and WhatsApp notification sent!");
        }
    } elseif ($action === 'update_status') {
        $status = cleanInput($_POST['status'] ?? 'Confirmed');
        if (!empty($bookingId)) {
            $uStmt = $db->prepare("UPDATE bookings SET booking_status = ? WHERE booking_id = ?");
            $uStmt->execute([$status, $bookingId]);
            setFlashMessage('success', "Booking {$bookingId} status updated to {$status}.");
        }
    } elseif ($action === 'delete_booking') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $delStmt = $db->prepare("DELETE FROM bookings WHERE id = ?");
            $delStmt->execute([$id]);
            setFlashMessage('info', "Booking deleted successfully.");
        }
    }
    header('Location: ' . BASE_URL . '/admin/bookings.php');
    exit;
}

// Search and Filter logic
$search = cleanInput($_GET['search'] ?? '');
$statusFilter = cleanInput($_GET['status'] ?? '');

$query = "SELECT * FROM bookings WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (booking_id LIKE ? OR customer_name LIKE ? OR customer_mobile LIKE ? OR pickup_location LIKE ? OR drop_location LIKE ?)";
    $term = '%' . $search . '%';
    $params = array_merge($params, [$term, $term, $term, $term, $term]);
}

if (!empty($statusFilter) && $statusFilter !== 'all') {
    $query .= " AND booking_status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY id DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$drivers = $db->query("SELECT * FROM drivers WHERE is_active = 1")->fetchAll();
?>

<!-- Search & Filter Controls -->
<div class="card-table" style="padding: 20px; margin-bottom: 24px;">
  <form action="<?php echo BASE_URL; ?>/admin/bookings.php" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
    <div style="flex: 2; min-width: 240px;">
      <input type="text" name="search" class="form-control" placeholder="Search by Booking ID, Customer Name, Mobile, Route..." value="<?php echo htmlspecialchars($search); ?>">
    </div>
    <div style="flex: 1; min-width: 180px;">
      <select name="status" class="form-control" onchange="this.form.submit()">
        <option value="all">-- All Booking Statuses --</option>
        <option value="Confirmed" <?php echo $statusFilter === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
        <option value="Driver Assigned" <?php echo $statusFilter === 'Driver Assigned' ? 'selected' : ''; ?>>Driver Assigned</option>
        <option value="Trip Started" <?php echo $statusFilter === 'Trip Started' ? 'selected' : ''; ?>>Trip Started</option>
        <option value="Trip Completed" <?php echo $statusFilter === 'Trip Completed' ? 'selected' : ''; ?>>Trip Completed</option>
        <option value="Payment Pending" <?php echo $statusFilter === 'Payment Pending' ? 'selected' : ''; ?>>Payment Pending</option>
        <option value="Cancelled" <?php echo $statusFilter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
      </select>
    </div>
    <div>
      <button type="submit" class="btn btn-primary btn-sm">Filter</button>
      <a href="<?php echo BASE_URL; ?>/admin/bookings.php" class="btn btn-secondary btn-sm">Reset</a>
    </div>
  </form>
</div>

<!-- Bookings List Table -->
<div class="card-table">
  <div class="card-table-header">
    <h3>All Bookings (<?php echo count($bookings); ?> Found)</h3>
    <a href="<?php echo BASE_URL; ?>/booking.php" target="_blank" class="btn btn-primary btn-sm">+ New Manual Booking</a>
  </div>

  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr>
          <th>Booking ID</th>
          <th>Customer</th>
          <th>Journey Route</th>
          <th>Date & Time</th>
          <th>Vehicle</th>
          <th>Fare</th>
          <th>Status</th>
          <th>Driver</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($bookings)): ?>
          <tr>
            <td colspan="9" style="text-align: center; color: var(--text-dim); padding: 30px;">No bookings match your criteria.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($bookings as $b): ?>
            <tr>
              <td>
                <strong style="color: var(--primary);"><?php echo htmlspecialchars($b['booking_id']); ?></strong>
                <div style="font-size: 0.75rem; color: var(--text-dim);"><?php echo htmlspecialchars($b['trip_type']); ?></div>
              </td>
              <td>
                <div><?php echo htmlspecialchars($b['customer_name']); ?></div>
                <small style="color: var(--text-dim);"><?php echo htmlspecialchars($b['customer_mobile']); ?></small>
              </td>
              <td>
                <div><strong>From:</strong> <?php echo htmlspecialchars($b['pickup_location']); ?></div>
                <div><strong>To:</strong> <?php echo htmlspecialchars($b['drop_location']); ?></div>
              </td>
              <td>
                <div><?php echo date('d-M-Y', strtotime($b['journey_date'])); ?></div>
                <small style="color: var(--text-dim);"><?php echo htmlspecialchars($b['pickup_time']); ?></small>
              </td>
              <td><?php echo htmlspecialchars($b['vehicle_name']); ?></td>
              <td>
                <strong>₹<?php echo number_format($b['total_amount'], 2); ?></strong>
                <div style="font-size: 0.75rem; color: #34d399;">Paid: ₹<?php echo number_format($b['advance_paid'], 2); ?></div>
              </td>
              <td>
                <?php echo getStatusBadge($b['booking_status']); ?>
              </td>
              <td>
                <?php if (!empty($b['assigned_driver_name'])): ?>
                  <div style="color: #34d399; font-weight: 600;"><?php echo htmlspecialchars($b['assigned_driver_name']); ?></div>
                  <small style="color: var(--text-dim);"><?php echo htmlspecialchars($b['assigned_driver_mobile']); ?></small>
                <?php else: ?>
                  <button type="button" onclick="openAssignModal('<?php echo $b['booking_id']; ?>', '<?php echo $b['driver_id']; ?>')" class="btn btn-secondary btn-sm" style="padding: 3px 8px; font-size: 0.75rem;">
                    + Assign
                  </button>
                <?php endif; ?>
              </td>
              <td>
                <div style="display: flex; gap: 6px;">
                  <a href="<?php echo BASE_URL; ?>/admin/booking_details.php?id=<?php echo $b['id']; ?>" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">
                    Details
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

<!-- Assign Driver Modal -->
<div class="admin-modal-backdrop" id="assignDriverModal">
  <div class="admin-modal-box">
    <div class="modal-header">
      <h3 style="color: #fff; margin: 0;">Assign Driver & Dispatch Cab</h3>
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
        <button type="submit" class="btn btn-primary">Assign Driver & Trigger WhatsApp</button>
      </div>
    </form>
  </div>
</div>

<!-- Status Update Modal -->
<div class="admin-modal-backdrop" id="updateStatusModal">
  <div class="admin-modal-box">
    <div class="modal-header">
      <h3 style="color: #fff; margin: 0;">Update Booking / Trip Status</h3>
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
