<?php
define('PAGE_TITLE', 'About Us - Bishnoi Travels');
require_once __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top: 40px;">
  <div class="container">
    <div class="section-header">
      <div class="badge-pill">Our Heritage & Commitment</div>
      <h2>About Bishnoi Travels</h2>
      <p>Delivering trusted 24-hour taxi and travel services across Haridwar, Uttarakhand, and throughout India with safety, comfort, and integrity.</p>
    </div>

    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 40px; align-items: center; margin-bottom: 60px;">
      <div>
        <h3 style="font-size: 1.8rem; margin-bottom: 16px; color: #fff;">
          Welcome to <span style="color: var(--primary);">Bishnoi Travels</span>
        </h3>
        <p style="margin-bottom: 16px; font-size: 1rem;">
          Founded and managed by <strong><?php echo OWNER_NAME; ?></strong> in the holy city of Haridwar, Bishnoi Travels has grown into one of Uttarakhand's most reputable cab agencies. Our mission is to transform outstation and local travel by delivering prompt 24×7 customer service, transparent per-kilometer pricing, and impeccably maintained vehicles.
        </p>
        <p style="margin-bottom: 20px; font-size: 1rem;">
          Whether assisting pilgrims embarking on the auspicious Chardham Yatra, ensuring corporate commuters reach Delhi Airport on time, or facilitating family holidays across Mussoorie, Nainital, and Rishikesh, we treat every traveler with authentic Himalayan warmth and professionalism.
        </p>
        
        <div style="background: var(--bg-card); border-left: 4px solid var(--primary); padding: 18px; border-radius: var(--radius-sm); margin-bottom: 24px;">
          <p style="margin: 0; font-style: italic; color: #f8fafc;">
            "Our promise is simple: Zero hidden fees, courteous drivers, neat and clean sanitized cars, and round-the-clock personal assistance for every single trip."
          </p>
          <strong style="display: block; margin-top: 8px; color: var(--primary); font-size: 0.9rem;">— <?php echo OWNER_NAME; ?>, Founder & Managing Director</strong>
        </div>

        <div style="display: flex; gap: 16px;">
          <a href="<?php echo BASE_URL; ?>/booking.php" class="btn btn-primary">Book a Cab Online</a>
          <a href="<?php echo BASE_URL; ?>/contact.php" class="btn btn-secondary">Contact Our Team</a>
        </div>
      </div>

      <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-xl); padding: 36px;">
        <h4 style="font-size: 1.3rem; color: #fff; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
          🏢 Business Information
        </h4>

        <div style="font-size: 0.95rem; display: flex; flex-direction: column; gap: 16px;">
          <div>
            <span style="color: var(--text-dim); display: block; font-size: 0.8rem; text-transform: uppercase;">Company Name:</span>
            <strong style="color: #fff; font-size: 1.1rem;"><?php echo BUSINESS_NAME; ?></strong>
          </div>
          <div>
            <span style="color: var(--text-dim); display: block; font-size: 0.8rem; text-transform: uppercase;">Proprietor / Contact:</span>
            <strong style="color: #fff;"><?php echo OWNER_NAME; ?></strong>
          </div>
          <div>
            <span style="color: var(--text-dim); display: block; font-size: 0.8rem; text-transform: uppercase;">Service Coverage:</span>
            <strong style="color: var(--primary);">All Over India 24 Hours Available</strong>
          </div>
          <div>
            <span style="color: var(--text-dim); display: block; font-size: 0.8rem; text-transform: uppercase;">Headquarters Office:</span>
            <strong style="color: #fff;"><?php echo BUSINESS_ADDRESS; ?></strong>
          </div>
          <div>
            <span style="color: var(--text-dim); display: block; font-size: 0.8rem; text-transform: uppercase;">Direct Helplines:</span>
            <strong style="color: var(--accent-cyan); font-size: 1.1rem;"><?php echo PHONE_PRIMARY; ?> / <?php echo PHONE_SECONDARY; ?></strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
