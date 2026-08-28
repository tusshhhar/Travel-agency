<?php
define('ADMIN_PAGE_TITLE', 'Fare Rules & Additional Charges');
require_once __DIR__ . '/header.php';

$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = $_POST['settings'] ?? [];
    foreach ($settings as $key => $val) {
        $uStmt = $db->prepare("UPDATE fare_settings SET setting_value = ? WHERE setting_key = ?");
        $uStmt->execute([cleanInput($val), cleanInput($key)]);
    }
    setFlashMessage('success', "Fare calculation parameters updated successfully.");
    header('Location: ' . BASE_URL . '/admin/fares.php');
    exit;
}

$rows = $db->query("SELECT * FROM fare_settings")->fetchAll();
?>

<div style="margin-bottom: 24px;">
  <h2 style="font-size: 1.5rem; color: #fff; margin: 0;">Dynamic Fare Calculation Configuration</h2>
  <p style="color: var(--text-dim); font-size: 0.85rem;">Adjust night driving allowances, toll estimation rates, advance payment percentages, and refund rules.</p>
</div>

<div class="card-table" style="padding: 30px; max-width: 800px;">
  <form action="<?php echo BASE_URL; ?>/admin/fares.php" method="POST">
    
    <?php foreach ($rows as $r): ?>
      <div class="form-group" style="margin-bottom: 20px;">
        <label class="form-label" style="font-size: 0.95rem; color: #fff;">
          ⚙️ <?php echo ucwords(str_replace('_', ' ', $r['setting_key'])); ?>
        </label>
        <input type="text" name="settings[<?php echo htmlspecialchars($r['setting_key']); ?>]" class="form-control" value="<?php echo htmlspecialchars($r['setting_value']); ?>" required>
        <small style="color: var(--text-dim); margin-top: 4px; font-size: 0.8rem;"><?php echo htmlspecialchars($r['description']); ?></small>
      </div>
    <?php endforeach; ?>

    <button type="submit" class="btn btn-primary btn-lg">
      <span>Save Fare Rules ➔</span>
    </button>
  </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
