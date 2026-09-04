<?php
define('PAGE_TITLE', 'Our Vehicle Fleet & Rates - Jambho Haridwar Travels');
require_once __DIR__ . '/includes/header.php';

$db = Database::getConnection();
$vehicles = $db->query("SELECT * FROM vehicles WHERE is_active = 1 ORDER BY per_km_rate ASC")->fetchAll();
?>

<section class="section" style="padding-top: 40px;">
  <div class="container">
    <div class="section-header">
      <div class="badge-pill">Premium Maintained Vehicles</div>
      <h2>Our Modern Cab & Taxi Fleet</h2>
      <p>Every vehicle in the Jambho Haridwar Travels fleet undergoes regular mechanical inspections, interior sanitization, and is driven by background-verified chauffeurs.</p>
    </div>

    <div class="fleet-grid">
      <?php foreach ($vehicles as $veh): ?>
        <div class="fleet-card">
          <div class="fleet-image-wrap">
            <span class="fleet-category-tag"><?php echo htmlspecialchars($veh['category']); ?></span>
            <img src="<?php echo getVehicleImageUrl($veh['image_url']); ?>" alt="<?php echo htmlspecialchars($veh['name']); ?>" onerror="this.onerror=null;this.src='<?php echo BASE_URL; ?>/assets/images/sedan.svg';" class="fleet-img">
          </div>
          <div class="fleet-card-body">
            <h3><?php echo htmlspecialchars($veh['name']); ?></h3>
            <div class="fleet-models"><?php echo htmlspecialchars($veh['model_example']); ?></div>
            
            <div class="fleet-specs">
              <span class="spec-pill">👥 <?php echo $veh['seating_capacity']; ?> Passenger Seats</span>
              <span class="spec-pill">🧳 <?php echo $veh['luggage_capacity']; ?> Luggage Bags</span>
              <span class="spec-pill">❄️ <?php echo htmlspecialchars($veh['ac_type']); ?></span>
              <span class="spec-pill">🛡️ GPS Enabled</span>
            </div>

            <p style="font-size: 0.88rem; margin-bottom: 20px;">
              <?php echo htmlspecialchars($veh['description']); ?>
            </p>

            <div style="background: var(--bg-secondary); padding: 14px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 0.85rem; border: 1px solid var(--border-color);">
              <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span style="color: var(--text-dim);">Base Starting Fare:</span>
                <strong>₹<?php echo number_format($veh['base_fare'], 2); ?></strong>
              </div>
              <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span style="color: var(--text-dim);">Min Billable KM/Day:</span>
                <strong><?php echo $veh['min_km']; ?> KM</strong>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-dim);">Driver Allowance:</span>
                <strong>₹<?php echo $veh['driver_allowance_per_day']; ?>/Day</strong>
              </div>
            </div>

            <div class="fleet-price-row">
              <div class="fleet-rate-box">
                <span>Per KM Rate</span>
                <strong>₹<?php echo $veh['per_km_rate']; ?> / KM</strong>
              </div>
              <a href="<?php echo BASE_URL; ?>/booking.php?vehicle_id=<?php echo $veh['id']; ?>" class="btn btn-primary btn-sm">
                <span>Book This Cab ➔</span>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
