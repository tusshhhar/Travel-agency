<?php
define('PAGE_TITLE', 'Our Cab Services - Bishnoi Travels');
require_once __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top: 40px;">
  <div class="container">
    <div class="section-header">
      <div class="badge-pill">24×7 Cab Services</div>
      <h2>Comprehensive Travel & Cab Solutions</h2>
      <p>Bishnoi Travels offers an extensive range of tailored transportation services for pilgrims, corporate clients, vacationers, and emergency travelers across India.</p>
    </div>

    <div class="services-grid">
      
      <div class="service-card">
        <div class="service-icon">🚗</div>
        <h3>One-Way Cab Drop</h3>
        <p>Pay only for one-way distance! Doorstep pickup from Haridwar/Rishikesh and drop to Delhi NCR, Chandigarh, Jaipur, Lucknow, or Agra without paying return fare.</p>
        <div style="margin-top: 20px;">
          <a href="<?php echo BASE_URL; ?>/booking.php?trip_type=One+Way" class="btn btn-primary btn-sm">Book One Way ➔</a>
        </div>
      </div>

      <div class="service-card">
        <div class="service-icon">🔄</div>
        <h3>Round Trip Outstation</h3>
        <p>Enjoy flexible multi-day outstation trips with a dedicated AC cab and professional driver. Perfect for family weekend getaways and multi-city business tours.</p>
        <div style="margin-top: 20px;">
          <a href="<?php echo BASE_URL; ?>/booking.php?trip_type=Round+Trip" class="btn btn-primary btn-sm">Book Round Trip ➔</a>
        </div>
      </div>

      <div class="service-card">
        <div class="service-icon">✈️</div>
        <h3>Airport Transfers</h3>
        <p>Guaranteed on-time pickup and drop to IGI Airport New Delhi (T1/T2/T3), Jolly Grant Airport Dehradun, and Chandigarh Airport with flight tracking.</p>
        <div style="margin-top: 20px;">
          <a href="<?php echo BASE_URL; ?>/booking.php?trip_type=Airport+Transfer" class="btn btn-primary btn-sm">Book Airport Cab ➔</a>
        </div>
      </div>

      <div class="service-card">
        <div class="service-icon">⛰️</div>
        <h3>Chardham Yatra Pilgrimage</h3>
        <p>Customized 9-day to 12-day packages for Yamunotri, Gangotri, Kedarnath, and Badrinath with experienced mountain drivers in Innova Crysta & Tempo Travellers.</p>
        <div style="margin-top: 20px;">
          <a href="<?php echo BASE_URL; ?>/contact.php" class="btn btn-primary btn-sm">Get Tour Quote ➔</a>
        </div>
      </div>

      <div class="service-card">
        <div class="service-icon">🏙️</div>
        <h3>Local Haridwar & Rishikesh Tour</h3>
        <p>Full-day 8hr/80km local package covering Har Ki Pauri Ganga Aarti, Mansa Devi Temple, Chandi Devi, Daksh Prajapati, Ram Jhula, Triveni Ghat, and Parmarth Niketan.</p>
        <div style="margin-top: 20px;">
          <a href="<?php echo BASE_URL; ?>/booking.php?trip_type=Local" class="btn btn-primary btn-sm">Book Local Sightseeing ➔</a>
        </div>
      </div>

      <div class="service-card">
        <div class="service-icon">🚐</div>
        <h3>Group & Tempo Rentals</h3>
        <p>13, 17, and 26-seater luxury pushback Force Tempo Travellers equipped with high-efficiency AC, luggage carriers, and entertainment systems for large groups.</p>
        <div style="margin-top: 20px;">
          <a href="<?php echo BASE_URL; ?>/fleet.php" class="btn btn-primary btn-sm">View Fleet ➔</a>
        </div>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
