<?php
define('ADMIN_PAGE_TITLE', 'Driver Management');
require_once __DIR__ . '/header.php';

$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = cleanInput($_POST['action'] ?? '');

    if ($action === 'save_driver') {
        $id = (int)($_POST['id'] ?? 0);
        $name = cleanInput($_POST['name'] ?? '');
        $mobile = sanitizePhone(cleanInput($_POST['mobile'] ?? ''));
        $license = cleanInput($_POST['license_no'] ?? '');
        $vehNo = cleanInput($_POST['vehicle_number'] ?? '');
        $vehType = cleanInput($_POST['assigned_vehicle_type'] ?? 'Sedan');
        $status = cleanInput($_POST['current_status'] ?? 'Available');

        if (!empty($name) && !empty($mobile)) {
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE drivers SET name = ?, mobile = ?, license_no = ?, vehicle_number = ?, assigned_vehicle_type = ?, current_status = ? WHERE id = ?");
                $stmt->execute([$name, $mobile, $license, $vehNo, $vehType, $status, $id]);
                setFlashMessage('success', "Driver {$name} updated.");
            } else {
                $stmt = $db->prepare("INSERT INTO drivers (name, mobile, license_no, vehicle_number, assigned_vehicle_type, current_status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $mobile, $license, $vehNo, $vehType, $status]);
                setFlashMessage('success', "New driver {$name} added.");
            }
        }
    } elseif ($action === 'delete_driver') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->prepare("DELETE FROM drivers WHERE id = ?");
            $stmt->execute([$id]);
            setFlashMessage('info', "Driver removed.");
        }
    }
    header('Location: ' . BASE_URL . '/admin/drivers.php');
    exit;
}

$drivers = $db->query("SELECT * FROM drivers ORDER BY id DESC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
  <div>
    <h2 style="font-size: 1.5rem; color: #fff; margin: 0;">Driver & Chauffeur Records</h2>
    <p style="color: var(--text-dim); font-size: 0.85rem;">Manage driver profiles, driving license verification, and cab assignments.</p>
  </div>
  <button type="button" onclick="openDriverModal()" class="btn btn-primary btn-sm">+ Add New Driver</button>
</div>

<div class="card-table">
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr>
          <th>Driver Name</th>
          <th>Mobile Number</th>
          <th>Driving License</th>
          <th>Vehicle Number</th>
          <th>Vehicle Category</th>
          <th>Duty Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($drivers as $d): ?>
          <tr>
            <td>
              <strong><?php echo htmlspecialchars($d['name']); ?></strong>
            </td>
            <td>
              <a href="tel:<?php echo htmlspecialchars($d['mobile']); ?>" style="color: var(--primary); font-weight: 500;">
                <?php echo htmlspecialchars($d['mobile']); ?>
              </a>
            </td>
            <td><code><?php echo htmlspecialchars($d['license_no']); ?></code></td>
            <td><strong style="color: #fff;"><?php echo htmlspecialchars($d['vehicle_number'] ?: 'UK08-N/A'); ?></strong></td>
            <td><?php echo htmlspecialchars($d['assigned_vehicle_type'] ?: 'Sedan'); ?></td>
            <td><?php echo getStatusBadge($d['current_status']); ?></td>
            <td>
              <div style="display: flex; gap: 6px;">
                <button type="button" onclick='editDriver(<?php echo json_encode($d); ?>)' class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">
                  Edit
                </button>
                <a href="https://wa.me/91<?php echo htmlspecialchars($d['mobile']); ?>" target="_blank" class="btn btn-whatsapp btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">
                  WhatsApp
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add / Edit Driver Modal -->
<div class="admin-modal-backdrop" id="driverModal">
  <div class="admin-modal-box">
    <div class="modal-header">
      <h3 style="color: #fff; margin: 0;" id="driverModalTitle">Add New Driver</h3>
      <button type="button" onclick="closeModal('driverModal')" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;">&times;</button>
    </div>
    <form action="<?php echo BASE_URL; ?>/admin/drivers.php" method="POST">
      <input type="hidden" name="action" value="save_driver">
      <input type="hidden" name="id" id="d_id" value="0">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Driver Full Name *</label>
          <input type="text" name="name" id="d_name" class="form-control" placeholder="Driver Name" required>
        </div>
        <div class="form-group">
          <label class="form-label">Mobile Number *</label>
          <input type="tel" name="mobile" id="d_mobile" class="form-control" placeholder="10-digit mobile" required>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Driving License No. *</label>
          <input type="text" name="license_no" id="d_license" class="form-control" placeholder="e.g. UK08-2020-00123" required>
        </div>
        <div class="form-group">
          <label class="form-label">Vehicle Registration No.</label>
          <input type="text" name="vehicle_number" id="d_vehNo" class="form-control" placeholder="e.g. UK08-TA-5566">
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Assigned Vehicle Category</label>
          <select name="assigned_vehicle_type" id="d_vehType" class="form-control">
            <option value="Sedan">Sedan</option>
            <option value="Premium Sedan">Premium Sedan</option>
            <option value="SUV">SUV</option>
            <option value="Innova">Innova</option>
            <option value="Innova Crysta">Innova Crysta</option>
            <option value="Tempo Traveller">Tempo Traveller</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Availability Status</label>
          <select name="current_status" id="d_status" class="form-control">
            <option value="Available">Available</option>
            <option value="On Trip">On Trip</option>
            <option value="Off Duty">Off Duty</option>
          </select>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
        <button type="button" onclick="closeModal('driverModal')" class="btn btn-secondary">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Driver</button>
      </div>
    </form>
  </div>
</div>

<script>
function openDriverModal() {
  document.getElementById('driverModalTitle').textContent = 'Add New Driver';
  document.getElementById('d_id').value = '0';
  document.getElementById('d_name').value = '';
  document.getElementById('d_mobile').value = '';
  document.getElementById('d_license').value = '';
  document.getElementById('d_vehNo').value = '';
  document.getElementById('d_status').value = 'Available';
  document.getElementById('driverModal').classList.add('open');
}

function editDriver(d) {
  document.getElementById('driverModalTitle').textContent = 'Edit Driver ' + d.name;
  document.getElementById('d_id').value = d.id;
  document.getElementById('d_name').value = d.name;
  document.getElementById('d_mobile').value = d.mobile;
  document.getElementById('d_license').value = d.license_no;
  document.getElementById('d_vehNo').value = d.vehicle_number;
  document.getElementById('d_vehType').value = d.assigned_vehicle_type;
  document.getElementById('d_status').value = d.current_status;
  document.getElementById('driverModal').classList.add('open');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
