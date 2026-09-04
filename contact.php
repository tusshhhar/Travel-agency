<?php
define('PAGE_TITLE', 'Contact Us & Enquiries - Jambho Haridwar Travels');
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/whatsapp_helper.php';

$enquirySuccess = false;
$enquiryError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = cleanInput($_POST['name'] ?? '');
    $mobile = sanitizePhone(cleanInput($_POST['mobile'] ?? ''));
    $email = cleanInput($_POST['email'] ?? '');
    $from = cleanInput($_POST['travel_from'] ?? '');
    $to = cleanInput($_POST['travel_to'] ?? '');
    $date = cleanInput($_POST['travel_date'] ?? date('Y-m-d'));
    $passengers = (int)($_POST['passengers'] ?? 1);
    $message = cleanInput($_POST['message'] ?? '');

    if (!empty($name) && !empty($mobile)) {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO enquiries (name, mobile, email, travel_from, travel_to, travel_date, passengers, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $mobile, $email, $from, $to, $date, $passengers, $message]);

            // Trigger automated WhatsApp confirmation to customer (FR-031)
            $replyMsg = "🙏 *Namaste {$name}!* Thank you for your enquiry with *Jambho Haridwar Travels*.\n\n"
                      . "We have received your request for travel from *{$from}* to *{$to}* on *" . date('d-M-Y', strtotime($date)) . "*.\n"
                      . "Our travel coordinator (*" . OWNER_NAME . "*) will call you shortly with the best customized quote.\n\n"
                      . "For immediate booking, call: " . PHONE_PRIMARY;

            WhatsAppHelper::sendMessage($mobile, $replyMsg, null, 'enquiry_auto_reply');

            $enquirySuccess = true;
        } catch (Exception $e) {
            $enquiryError = "Error saving enquiry. Please call us directly: " . PHONE_PRIMARY;
        }
    } else {
        $enquiryError = "Please fill in all mandatory fields (Name and Mobile Number).";
    }
}
?>

<section class="section" style="padding-top: 40px;">
  <div class="container">
    <div class="section-header">
      <div class="badge-pill">24×7 Direct Assistance</div>
      <h2>Get in Touch with Jambho Haridwar Travels</h2>
      <p>Have a custom itinerary, Chardham Yatra query, or need an urgent cab? Call, WhatsApp, or drop your enquiry below.</p>
    </div>

    <div class="contact-grid">
      
      <!-- Contact Info Cards -->
      <div>
        <div class="booking-widget-card" style="padding: 30px; margin-bottom: 24px;">
          <h3 style="font-size: 1.3rem; margin-bottom: 16px; color: #fff;">📞 Contact Numbers</h3>
          <p style="margin-bottom: 12px; font-size: 1.05rem;">
            <strong>Primary Helpline:</strong><br>
            <a href="tel:<?php echo PHONE_PRIMARY; ?>" style="color: var(--primary); font-size: 1.2rem; font-weight: 700;">+91 <?php echo PHONE_PRIMARY; ?></a>
          </p>
          <p style="margin-bottom: 16px; font-size: 1.05rem;">
            <strong>Secondary / Support:</strong><br>
            <a href="tel:<?php echo PHONE_SECONDARY; ?>" style="color: var(--accent-cyan); font-size: 1.2rem; font-weight: 700;">+91 <?php echo PHONE_SECONDARY; ?></a>
          </p>
          <div style="display: flex; gap: 10px;">
            <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>" target="_blank" class="btn btn-whatsapp btn-sm">
              <span>💬 WhatsApp Chat</span>
            </a>
            <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="btn btn-call btn-sm">
              <span>📞 Direct Call</span>
            </a>
          </div>
        </div>

        <div class="booking-widget-card" style="padding: 30px;">
          <h3 style="font-size: 1.3rem; margin-bottom: 16px; color: #fff;">📍 Main Office Address</h3>
          <p style="margin-bottom: 8px; font-size: 1rem; color: #f8fafc;">
            <strong><?php echo BUSINESS_NAME; ?></strong><br>
            <?php echo BUSINESS_ADDRESS; ?>
          </p>
          <p style="color: var(--text-dim); font-size: 0.85rem; margin-bottom: 14px;">
            Manager: <strong><?php echo OWNER_NAME; ?></strong>
          </p>
          <p style="font-size: 0.85rem; color: var(--accent-emerald);">
            🟢 <strong>Open 24 Hours / 7 Days a Week</strong>
          </p>
        </div>
      </div>

      <!-- Customer Enquiry Form (FR-031) -->
      <div class="booking-widget-card" style="padding: 36px;">
        <h3 style="font-size: 1.4rem; margin-bottom: 8px; color: #fff;">📝 Send a Travel Enquiry</h3>
        <p style="margin-bottom: 24px; font-size: 0.9rem;">Fill in your travel requirements for an instant callback and WhatsApp quotation.</p>

        <?php if ($enquirySuccess): ?>
          <div class="alert alert-success">
            <span>✅ Thank you! Your enquiry has been received. Our team will contact you shortly and has dispatched a confirmation to your WhatsApp.</span>
          </div>
        <?php endif; ?>

        <?php if ($enquiryError): ?>
          <div class="alert alert-error">
            <span>❌ <?php echo htmlspecialchars($enquiryError); ?></span>
          </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/contact.php" method="POST">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="name">👤 Your Name *</label>
              <input type="text" name="name" id="name" class="form-control" placeholder="Full Name" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="mobile">📱 Mobile Number (WhatsApp) *</label>
              <input type="tel" name="mobile" id="mobile" class="form-control" placeholder="10-digit mobile" pattern="[0-9]{10}" required>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="travel_from">📍 Travel From</label>
              <input type="text" name="travel_from" id="travel_from" class="form-control" placeholder="e.g. Haridwar" value="Haridwar">
            </div>
            <div class="form-group">
              <label class="form-label" for="travel_to">📍 Travel To</label>
              <input type="text" name="travel_to" id="travel_to" class="form-control" placeholder="e.g. Kedarnath / Delhi / Mussoorie">
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="travel_date">📅 Travel Date</label>
              <input type="date" name="travel_date" id="travel_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d', strtotime('+2 days')); ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="passengers">👥 Passengers</label>
              <select name="passengers" id="passengers" class="form-control">
                <option value="1">1 Person</option>
                <option value="2">2 Persons</option>
                <option value="4" selected>4 Persons (Sedan)</option>
                <option value="6">6 Persons (SUV)</option>
                <option value="7">7 Persons (Innova Crysta)</option>
                <option value="13">13+ Persons (Tempo Traveller)</option>
              </select>
            </div>
          </div>

          <div class="form-group full-width" style="margin-bottom: 20px;">
            <label class="form-label" for="message">✉️ Your Message / Tour Requirement</label>
            <textarea name="message" id="message" class="form-control" placeholder="Tell us more about your travel plans, number of days, hotel requirements, etc."></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-lg btn-block">
            <span>Submit Enquiry ➔</span>
          </button>
        </form>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
