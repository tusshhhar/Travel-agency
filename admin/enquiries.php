<?php
define('ADMIN_PAGE_TITLE', 'Customer Leads & Enquiries');
require_once __DIR__ . '/header.php';

$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $status = cleanInput($_POST['status'] ?? 'Contacted');
    if ($id > 0) {
        $uStmt = $db->prepare("UPDATE enquiries SET status = ? WHERE id = ?");
        $uStmt->execute([$status, $id]);
        setFlashMessage('success', "Enquiry status updated.");
    }
    header('Location: ' . BASE_URL . '/admin/enquiries.php');
    exit;
}

$enquiries = $db->query("SELECT * FROM enquiries ORDER BY id DESC")->fetchAll();
?>

<div style="margin-bottom: 24px;">
  <h2 style="font-size: 1.5rem; color: #fff; margin: 0;">Customer Tour Enquiries & Leads</h2>
  <p style="color: var(--text-dim); font-size: 0.85rem;">Review prospective traveler leads submitted via website contact form.</p>
</div>

<div class="card-table">
  <div class="table-responsive">
    <table class="data-table">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Mobile (WhatsApp)</th>
          <th>Route Requested</th>
          <th>Travel Date</th>
          <th>Pax</th>
          <th>Requirement Notes</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($enquiries)): ?>
          <tr>
            <td colspan="8" style="text-align: center; color: var(--text-dim); padding: 30px;">No enquiries received yet.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($enquiries as $e): ?>
            <tr>
              <td>
                <strong><?php echo htmlspecialchars($e['name']); ?></strong>
                <?php if (!empty($e['email'])): ?>
                  <div style="font-size: 0.75rem; color: var(--text-dim);"><?php echo htmlspecialchars($e['email']); ?></div>
                <?php endif; ?>
              </td>
              <td>
                <a href="tel:<?php echo htmlspecialchars($e['mobile']); ?>" style="color: var(--primary); font-weight: 600;">
                  <?php echo htmlspecialchars($e['mobile']); ?>
                </a>
              </td>
              <td>
                <strong><?php echo htmlspecialchars($e['travel_from']); ?></strong> ➔ <?php echo htmlspecialchars($e['travel_to']); ?>
              </td>
              <td><?php echo date('d-M-Y', strtotime($e['travel_date'])); ?></td>
              <td><?php echo $e['passengers']; ?> Pax</td>
              <td style="max-width: 250px;">
                <div style="font-size: 0.82rem; color: var(--text-muted);"><?php echo htmlspecialchars($e['message'] ?: 'No additional notes'); ?></div>
              </td>
              <td>
                <?php echo getStatusBadge($e['status']); ?>
              </td>
              <td>
                <div style="display: flex; gap: 6px;">
                  <a href="https://wa.me/91<?php echo htmlspecialchars($e['mobile']); ?>?text=Hello%20<?php echo urlencode($e['name']); ?>,%20this%20is%20<?php echo urlencode(OWNER_NAME); ?>%20from%20<?php echo urlencode(BUSINESS_NAME); ?>%20regarding%20your%20cab%20enquiry." target="_blank" class="btn btn-whatsapp btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">
                    💬 WhatsApp
                  </a>
                  <form action="<?php echo BASE_URL; ?>/admin/enquiries.php" method="POST" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $e['id']; ?>">
                    <input type="hidden" name="status" value="<?php echo $e['status'] === 'New' ? 'Contacted' : 'Closed'; ?>">
                    <button type="submit" class="btn btn-secondary btn-sm" style="padding: 4px 8px; font-size: 0.75rem;">
                      Mark <?php echo $e['status'] === 'New' ? 'Contacted' : 'Closed'; ?>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
