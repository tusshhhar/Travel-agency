<?php
define('ADMIN_PAGE_TITLE', 'Vehicle Fleet & Pricing Management');
require_once __DIR__ . '/header.php';

$db = Database::getConnection();

// Handle Add / Edit / Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = cleanInput($_POST['action'] ?? '');

    if ($action === 'save_vehicle') {
        $id = (int)($_POST['id'] ?? 0);
        $name = cleanInput($_POST['name'] ?? '');
        $category = cleanInput($_POST['category'] ?? 'Sedan');
        $model = cleanInput($_POST['model_example'] ?? '');
        $seats = (int)($_POST['seating_capacity'] ?? 4);
        $luggage = (int)($_POST['luggage_capacity'] ?? 2);
        $ac = cleanInput($_POST['ac_type'] ?? 'AC');
        $rate = (float)($_POST['per_km_rate'] ?? 11);
        $base = (float)($_POST['base_fare'] ?? 1200);
        $minKm = (int)($_POST['min_km'] ?? 250);
        $allowance = (float)($_POST['driver_allowance_per_day'] ?? 300);
        $desc = cleanInput($_POST['description'] ?? '');
        $img = cleanInput($_POST['image_url'] ?? 'assets/images/sedan.svg');

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE vehicles SET name = ?, category = ?, model_example = ?, seating_capacity = ?, luggage_capacity = ?, ac_type = ?, per_km_rate = ?, base_fare = ?, min_km = ?, driver_allowance_per_day = ?, description = ?, image_url = ? WHERE id = ?");
            $stmt->execute([$name, $category, $model, $seats, $luggage, $ac, $rate, $base, $minKm, $allowance, $desc, $img, $id]);
            setFlashMessage('success', "Vehicle {$name} updated successfully.");
        } else {
            $stmt = $db->prepare("INSERT INTO vehicles (name, category, model_example, seating_capacity, luggage_capacity, ac_type, per_km_rate, base_fare, min_km, driver_allowance_per_day, description, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category, $model, $seats, $luggage, $ac, $rate, $base, $minKm, $allowance, $desc, $img]);
            setFlashMessage('success', "New vehicle {$name} added to fleet.");
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        $current = (int)($_POST['current_status'] ?? 1);
        $new = $current === 1 ? 0 : 1;
        $stmt = $db->prepare("UPDATE vehicles SET is_active = ? WHERE id = ?");
        $stmt->execute([$new, $id]);
        setFlashMessage('info', "Vehicle status toggled.");
    }
    header('Location: ' . BASE_URL . '/admin/vehicles.php');
    exit;
}

$vehicles = $db->query("SELECT * FROM vehicles ORDER BY id ASC")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
  <div>
    <h2 style="font-size: 1.5rem; color: #fff; margin: 0;">Vehicle Fleet & Pricing</h2>
    <p style="color: var(--text-dim); font-size: 0.85rem;">Manage vehicles, seating capacities, and per-kilometer outstation rates.</p>
  </div>
  <button type="button" onclick="openVehicleModal()" class="btn btn-primary btn-sm">+ Add New Vehicle</button>
</div>

<div class="card-table">
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr>
          <th>Vehicle</th>
          <th>Category</th>
          <th>Seating / Bags</th>
          <th>Per KM Rate</th>
          <th>Base Fare</th>
          <th>Min KM/Day</th>
          <th>Driver Allowance</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($vehicles as $v): ?>
          <tr>
            <td>
              <div style="display: flex; align-items: center; gap: 12px;">
                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($v['image_url']); ?>" alt="" style="width: 50px; height: 30px; object-fit: contain;">
                <div>
                  <strong><?php echo htmlspecialchars($v['name']); ?></strong>
                  <div style="font-size: 0.75rem; color: var(--text-dim);"><?php echo htmlspecialchars($v['model_example']); ?></div>
                </div>
              </div>
            </td>
            <td><span class="badge-pill" style="margin: 0; font-size: 0.75rem;"><?php echo htmlspecialchars($v['category']); ?></span></td>
            <td><?php echo $v['seating_capacity']; ?> Seats / <?php echo $v['luggage_capacity']; ?> Bags</td>
            <td><strong style="color: var(--primary);">₹<?php echo $v['per_km_rate']; ?> / KM</strong></td>
            <td>₹<?php echo number_format($v['base_fare'], 2); ?></td>
            <td><?php echo $v['min_km']; ?> KM</td>
            <td>₹<?php echo $v['driver_allowance_per_day']; ?>/day</td>
            <td>
              <?php if ($v['is_active']): ?>
                <span class="status-badge badge-success">Active</span>
              <?php else: ?>
                <span class="status-badge badge-muted">Inactive</span>
              <?php endif; ?>
            </td>
            <td>
              <div style="display: flex; gap: 6px;">
                <button type="button" onclick='editVehicle(<?php echo json_encode($v); ?>)' class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">
                  Edit
                </button>
                <form action="<?php echo BASE_URL; ?>/admin/vehicles.php" method="POST" style="display: inline;">
                  <input type="hidden" name="action" value="toggle_status">
                  <input type="hidden" name="id" value="<?php echo $v['id']; ?>">
                  <input type="hidden" name="current_status" value="<?php echo $v['is_active']; ?>">
                  <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">
                    <?php echo $v['is_active'] ? 'Deactivate' : 'Activate'; ?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add / Edit Vehicle Modal -->
<div class="admin-modal-backdrop" id="vehicleModal">
  <div class="admin-modal-box" style="max-width: 650px;">
    <div class="modal-header">
      <h3 style="color: #fff; margin: 0;" id="vehicleModalTitle">Add New Vehicle</h3>
      <button type="button" onclick="closeModal('vehicleModal')" style="background:none; border:none; color:#fff; font-size:1.5rem; cursor:pointer;">&times;</button>
    </div>
    <form action="<?php echo BASE_URL; ?>/admin/vehicles.php" method="POST">
      <input type="hidden" name="action" value="save_vehicle">
      <input type="hidden" name="id" id="v_id" value="0">

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Vehicle Name *</label>
          <input type="text" name="name" id="v_name" class="form-control" placeholder="e.g. Sedan (Dzire / Etios)" required>
        </div>
        <div class="form-group">
          <label class="form-label">Category *</label>
          <select name="category" id="v_category" class="form-control" required>
            <option value="Sedan">Sedan</option>
            <option value="Premium Sedan">Premium Sedan</option>
            <option value="SUV">SUV</option>
            <option value="Innova">Innova</option>
            <option value="Innova Crysta">Innova Crysta</option>
            <option value="Tempo Traveller">Tempo Traveller</option>
          </select>
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Model Examples</label>
          <input type="text" name="model_example" id="v_model" class="form-control" placeholder="e.g. Maruti Dzire, Toyota Etios">
        </div>
        <div class="form-group">
          <label class="form-label">Vector Image URL</label>
          <input type="text" name="image_url" id="v_image" class="form-control" value="assets/images/sedan.svg">
        </div>
      </div>

      <div class="form-grid-3" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:14px; margin-bottom:16px;">
        <div class="form-group">
          <label class="form-label">Per KM Rate (₹) *</label>
          <input type="number" step="0.5" name="per_km_rate" id="v_rate" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Base Fare (₹) *</label>
          <input type="number" name="base_fare" id="v_base" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Min KM / Day</label>
          <input type="number" name="min_km" id="v_minkm" class="form-control" value="250">
        </div>
      </div>

      <div class="form-grid-3" style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:14px; margin-bottom:16px;">
        <div class="form-group">
          <label class="form-label">Seating Capacity</label>
          <input type="number" name="seating_capacity" id="v_seats" class="form-control" value="4">
        </div>
        <div class="form-group">
          <label class="form-label">Luggage Bags</label>
          <input type="number" name="luggage_capacity" id="v_luggage" class="form-control" value="2">
        </div>
        <div class="form-group">
          <label class="form-label">Driver Allowance/Day (₹)</label>
          <input type="number" name="driver_allowance_per_day" id="v_allowance" class="form-control" value="300">
        </div>
      </div>

      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label">Description</label>
        <textarea name="description" id="v_desc" class="form-control" placeholder="Brief vehicle highlights..."></textarea>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 10px;">
        <button type="button" onclick="closeModal('vehicleModal')" class="btn btn-secondary">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Vehicle Details</button>
      </div>
    </form>
  </div>
</div>

<script>
function openVehicleModal() {
  document.getElementById('vehicleModalTitle').textContent = 'Add New Vehicle';
  document.getElementById('v_id').value = '0';
  document.getElementById('v_name').value = '';
  document.getElementById('v_model').value = '';
  document.getElementById('v_rate').value = '12';
  document.getElementById('v_base').value = '1200';
  document.getElementById('v_minkm').value = '250';
  document.getElementById('v_seats').value = '4';
  document.getElementById('v_luggage').value = '2';
  document.getElementById('v_allowance').value = '300';
  document.getElementById('v_desc').value = '';
  document.getElementById('vehicleModal').classList.add('open');
}

function editVehicle(v) {
  document.getElementById('vehicleModalTitle').textContent = 'Edit ' + v.name;
  document.getElementById('v_id').value = v.id;
  document.getElementById('v_name').value = v.name;
  document.getElementById('v_category').value = v.category;
  document.getElementById('v_model').value = v.model_example;
  document.getElementById('v_image').value = v.image_url;
  document.getElementById('v_rate').value = v.per_km_rate;
  document.getElementById('v_base').value = v.base_fare;
  document.getElementById('v_minkm').value = v.min_km;
  document.getElementById('v_seats').value = v.seating_capacity;
  document.getElementById('v_luggage').value = v.luggage_capacity;
  document.getElementById('v_allowance').value = v.driver_allowance_per_day;
  document.getElementById('v_desc').value = v.description;
  document.getElementById('vehicleModal').classList.add('open');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
