<?php
define('ADMIN_PAGE_TITLE', 'WhatsApp Business Logs & Broadcast');
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../includes/whatsapp_helper.php';

$db = Database::getConnection();

// Handle direct manual WhatsApp message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_whatsapp_direct'])) {
    $phone = sanitizePhone(cleanInput($_POST['phone'] ?? ''));
    $msg = cleanInput($_POST['message'] ?? '');
    $bookingId = cleanInput($_POST['booking_id'] ?? null);

    if (!empty($phone) && !empty($msg)) {
        WhatsAppHelper::sendMessage($phone, $msg, $bookingId, 'admin_broadcast');
        setFlashMessage('success', "WhatsApp message dispatched to +91 {$phone}.");
        header('Location: ' . BASE_URL . '/admin/whatsapp_logs.php');
        exit;
    }
}

$logs = $db->query("SELECT * FROM whatsapp_logs ORDER BY id DESC LIMIT 50")->fetchAll();
?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
  
  <!-- Left Column: Logs Table -->
  <div class="card-table">
    <div class="card-table-header">
      <h3>WhatsApp Interactions Audit Log</h3>
      <span class="badge-pill" style="margin: 0; font-size: 0.75rem;">Meta Cloud API Live Stream</span>
    </div>

    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Direction</th>
            <th>Phone</th>
            <th>Message Preview</th>
            <th>Template / Type</th>
            <th>Status</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <tr>
              <td colspan="6" style="text-align: center; color: var(--text-dim); padding: 30px;">No WhatsApp logs recorded yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($logs as $l): ?>
              <tr>
                <td>
                  <?php if ($l['message_direction'] === 'inbound'): ?>
                    <span class="status-badge badge-cyan">⬇ Inbound</span>
                  <?php else: ?>
                    <span class="status-badge badge-success">⬆ Outbound</span>
                  <?php endif; ?>
                </td>
                <td>
                  <strong>+<?php echo htmlspecialchars($l['phone_number']); ?></strong>
                  <?php if (!empty($l['booking_id'])): ?>
                    <div style="font-size: 0.75rem; color: var(--primary);"><?php echo htmlspecialchars($l['booking_id']); ?></div>
                  <?php endif; ?>
                </td>
                <td style="max-width: 320px;">
                  <div style="white-space: pre-wrap; font-size: 0.82rem; color: #e2e8f0;"><?php echo htmlspecialchars(substr($l['message_body'], 0, 150)) . (strlen($l['message_body']) > 150 ? '...' : ''); ?></div>
                </td>
                <td><small style="color: var(--text-dim);"><?php echo htmlspecialchars($l['template_name'] ?: 'Text'); ?></small></td>
                <td>
                  <span class="status-badge <?php echo $l['status'] === 'delivered' || $l['status'] === 'sent' || $l['status'] === 'received' ? 'badge-success' : 'badge-danger'; ?>">
                    <?php echo htmlspecialchars($l['status']); ?>
                  </span>
                </td>
                <td><small style="color: var(--text-dim);"><?php echo date('d-M H:i', strtotime($l['created_at'])); ?></small></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Right Column: Direct Dispatch Form -->
  <div>
    <div class="card-table" style="padding: 24px; margin-bottom: 24px;">
      <h3 style="margin-bottom: 16px; color: #fff; font-size: 1.15rem;">💬 Send Direct WhatsApp Message</h3>
      <form action="<?php echo BASE_URL; ?>/admin/whatsapp_logs.php" method="POST">
        <div class="form-group" style="margin-bottom: 14px;">
          <label class="form-label">Recipient Mobile Number *</label>
          <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile (e.g. 9536200261)" required>
        </div>

        <div class="form-group" style="margin-bottom: 14px;">
          <label class="form-label">Booking ID (Optional)</label>
          <input type="text" name="booking_id" class="form-control" placeholder="e.g. BT-<?php echo date('Ymd'); ?>-001">
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
          <label class="form-label">Message Text *</label>
          <textarea name="message" class="form-control" placeholder="Type custom message..." required style="min-height: 120px;"></textarea>
        </div>

        <button type="submit" name="send_whatsapp_direct" class="btn btn-whatsapp btn-block">
          <span>🚀 Dispatch WhatsApp Message</span>
        </button>
      </form>
    </div>

    <!-- Webhook Integration Card -->
    <div class="card-table" style="padding: 24px;">
      <h4 style="color: #fff; margin-bottom: 10px; font-size: 1rem;">🔗 Cloud API Webhook Configuration</h4>
      <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 10px;">
        To receive real WhatsApp messages from Meta Developer Portal, configure your webhook endpoint:
      </p>
      <div style="background: var(--admin-sidebar); padding: 10px; border-radius: 6px; font-size: 0.75rem; word-break: break-all; margin-bottom: 8px;">
        <strong>Callback URL:</strong><br>
        <code><?php echo BASE_URL; ?>/api/whatsapp_webhook.php</code>
      </div>
      <div style="background: var(--admin-sidebar); padding: 10px; border-radius: 6px; font-size: 0.75rem;">
        <strong>Verify Token:</strong><br>
        <code><?php echo WHATSAPP_WEBHOOK_VERIFY_TOKEN; ?></code>
      </div>
    </div>

  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
