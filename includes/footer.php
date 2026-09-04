  <!-- Site Footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-col">
          <div class="brand-text" style="margin-bottom: 16px;">
            <h2 style="font-size: 1.5rem; color: #fff;">JAMBHO HARIDWAR <span style="color: var(--primary);">TRAVELS</span></h2>
            <small style="color: var(--text-dim); letter-spacing: 1px;">24×7 CAB & TRAVEL SERVICES</small>
          </div>
          <p style="margin-bottom: 16px; font-size: 0.9rem;">
            Premier cab & travel agency based in Haridwar, Uttarakhand. Providing clean, luxury, and reliable AC taxi services across Delhi NCR, Uttarakhand, Himachal, and Chardham Pilgrimage.
          </p>
          <p style="color: var(--text-dim); font-size: 0.85rem;">
            <strong>Managed By:</strong> <?php echo OWNER_NAME; ?>
          </p>
        </div>

        <div class="footer-col">
          <h4>Quick Links</h4>
          <ul class="footer-links">
            <li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
            <li><a href="<?php echo BASE_URL; ?>/booking.php">Book a Cab Online</a></li>
            <li><a href="<?php echo BASE_URL; ?>/services.php">Our Cab Services</a></li>
            <li><a href="<?php echo BASE_URL; ?>/fleet.php">Vehicle Fleet & Rates</a></li>
            <li><a href="<?php echo BASE_URL; ?>/track_booking.php">Track Booking Status</a></li>
            <li><a href="<?php echo BASE_URL; ?>/about.php">About Jambho Haridwar Travels</a></li>
            <li><a href="<?php echo BASE_URL; ?>/contact.php">Contact & Enquiries</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>Popular Routes</h4>
          <ul class="footer-links">
            <li><a href="<?php echo BASE_URL; ?>/booking.php">Haridwar to Delhi Airport</a></li>
            <li><a href="<?php echo BASE_URL; ?>/booking.php">Haridwar to Rishikesh / Dehradun</a></li>
            <li><a href="<?php echo BASE_URL; ?>/booking.php">Haridwar to Mussoorie Taxi</a></li>
            <li><a href="<?php echo BASE_URL; ?>/booking.php">Chardham 4-Dham Tour Package</a></li>
            <li><a href="<?php echo BASE_URL; ?>/booking.php">Kedarnath / Badrinath Yatra</a></li>
            <li><a href="<?php echo BASE_URL; ?>/booking.php">Haridwar Local Sightseeing</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h4>24×7 Contact & Office</h4>
          <p style="margin-bottom: 12px; font-size: 0.9rem;">
            📍 <?php echo BUSINESS_ADDRESS; ?>
          </p>
          <p style="margin-bottom: 8px;">
            📞 <a href="tel:<?php echo PHONE_PRIMARY; ?>" style="color: #fff; font-weight: 600;"><?php echo PHONE_PRIMARY; ?></a>
          </p>
          <p style="margin-bottom: 14px;">
            📱 <a href="tel:<?php echo PHONE_SECONDARY; ?>" style="color: #fff; font-weight: 600;"><?php echo PHONE_SECONDARY; ?></a>
          </p>
          <div style="display: flex; gap: 8px; margin-top: 10px;">
            <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>" target="_blank" class="btn btn-whatsapp btn-sm">
              <span>💬 Direct WhatsApp</span>
            </a>
            <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="btn btn-call btn-sm">
              <span>📞 Direct Call</span>
            </a>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> <?php echo BUSINESS_NAME; ?>. All rights reserved. Managed by <?php echo OWNER_NAME; ?>.</p>
        <p>Reliable 24×7 Cab Service • Haridwar • Uttarakhand</p>
      </div>
    </div>
  </footer>

  <!-- Floating WhatsApp Interactive Widget (FR-015 to FR-020) -->
  <div class="whatsapp-widget-bubble">
    <button class="whatsapp-trigger-btn" id="whatsapp_widget_btn" title="Chat with Jambho Haridwar Travels on WhatsApp" aria-label="Open WhatsApp Chat">
      <span>💬</span>
      <span class="whatsapp-online-badge"></span>
    </button>
  </div>

  <!-- Interactive WhatsApp Chat Modal / Simulator -->
  <div class="whatsapp-chat-modal" id="whatsapp_chat_modal">
    <div class="chat-header">
      <div class="chat-header-profile">
        <div class="chat-avatar">🚕</div>
        <div>
          <h4><?php echo BUSINESS_NAME; ?></h4>
          <small>🟢 Online • Automated Fast Reply</small>
        </div>
      </div>
      <button class="chat-close-btn" id="chat_close_btn">&times;</button>
    </div>

    <div class="chat-body" id="chat_body">
      <!-- Dynamic Messages appended here -->
    </div>

    <!-- Quick Reply Chips -->
    <div class="chat-quick-replies">
      <button class="quick-btn" data-reply="1">1️⃣ Book Cab</button>
      <button class="quick-btn" data-reply="2">2️⃣ Fare Chart</button>
      <button class="quick-btn" data-reply="3">3️⃣ Track Status</button>
      <button class="quick-btn" data-reply="4">4️⃣ Call Support</button>
      <button class="quick-btn" data-reply="5">5️⃣ Chardham Tour</button>
    </div>

    <form class="chat-input-row" id="chat_input_form">
      <input type="text" class="chat-input" id="chat_text_input" placeholder="Type a message (e.g. Hi, Book, Fare)..." autocomplete="off" required>
      <button type="submit" class="chat-send-btn">➤</button>
    </form>
  </div>

  <!-- Main JavaScript File -->
  <script src="<?php echo BASE_URL; ?>/assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
