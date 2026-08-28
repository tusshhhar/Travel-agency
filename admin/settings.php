<?php
define('ADMIN_PAGE_TITLE', 'System Settings & Integration Credentials');
require_once __DIR__ . '/header.php';
?>

<div style="margin-bottom: 24px;">
  <h2 style="font-size: 1.5rem; color: #fff; margin: 0;">System Configuration & API Integrations</h2>
  <p style="color: var(--text-dim); font-size: 0.85rem;">View business metadata, active database driver, and gateway webhook status.</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; max-width: 1000px;">
  
  <!-- Company Metadata -->
  <div class="card-table" style="padding: 24px;">
    <h3 style="margin-bottom: 16px; color: #fff; font-size: 1.15rem;">🏢 Business Information (FRS Master)</h3>
    <div style="display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem;">
      <div>
        <span style="color: var(--text-dim); display: block;">Business Name:</span>
        <strong style="color: #fff;"><?php echo BUSINESS_NAME; ?></strong>
      </div>
      <div>
        <span style="color: var(--text-dim); display: block;">Owner / Contact Person:</span>
        <strong style="color: #fff;"><?php echo OWNER_NAME; ?></strong>
      </div>
      <div>
        <span style="color: var(--text-dim); display: block;">Helpline Numbers:</span>
        <strong><?php echo PHONE_PRIMARY; ?> / <?php echo PHONE_SECONDARY; ?></strong>
      </div>
      <div>
        <span style="color: var(--text-dim); display: block;">Official WhatsApp:</span>
        <strong style="color: #34d399;">+<?php echo WHATSAPP_NUMBER; ?></strong>
      </div>
      <div>
        <span style="color: var(--text-dim); display: block;">Head Office Location:</span>
        <strong><?php echo BUSINESS_ADDRESS; ?></strong>
      </div>
      <div>
        <span style="color: var(--text-dim); display: block;">Database Driver:</span>
        <span class="badge-pill" style="margin:0; font-size:0.75rem;"><?php echo strtoupper(DB_DRIVER); ?> PDO (Active)</span>
      </div>
    </div>
  </div>

  <!-- Gateway Credentials & Webhook Endpoints -->
  <div class="card-table" style="padding: 24px;">
    <h3 style="margin-bottom: 16px; color: #fff; font-size: 1.15rem;">🔒 API & Webhook Configuration</h3>
    
    <div style="display: flex; flex-direction: column; gap: 16px; font-size: 0.85rem;">
      <div style="background: var(--admin-sidebar); padding: 12px; border-radius: 8px;">
        <strong style="color: var(--primary);">Razorpay Payment Gateway:</strong>
        <div style="margin-top: 4px; color: var(--text-muted);">
          Key ID: <code><?php echo RAZORPAY_KEY_ID; ?></code><br>
          Sandbox Simulator: <span style="color: #34d399;">Active (Offline testing enabled)</span><br>
          Webhook URL: <code><?php echo BASE_URL; ?>/api/razorpay_webhook.php</code>
        </div>
      </div>

      <div style="background: var(--admin-sidebar); padding: 12px; border-radius: 8px;">
        <strong style="color: #34d399;">WhatsApp Cloud API (Meta):</strong>
        <div style="margin-top: 4px; color: var(--text-muted);">
          API Version: <code><?php echo WHATSAPP_API_VERSION; ?></code><br>
          Phone Number ID: <code><?php echo WHATSAPP_PHONE_NUMBER_ID; ?></code><br>
          Webhook Endpoint: <code><?php echo BASE_URL; ?>/api/whatsapp_webhook.php</code><br>
          Verify Token: <code><?php echo WHATSAPP_WEBHOOK_VERIFY_TOKEN; ?></code>
        </div>
      </div>

      <div style="background: var(--admin-sidebar); padding: 12px; border-radius: 8px;">
        <strong style="color: var(--accent-cyan);">Admin Security Credentials:</strong>
        <div style="margin-top: 4px; color: var(--text-muted);">
          Username: <strong>admin</strong><br>
          Default Password: <strong>admin123</strong> (Configured in <code>config/config.php</code>)
        </div>
      </div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>
