<?php
define('PAGE_TITLE', 'Home - 24x7 Cab & Taxi Services Haridwar');
require_once __DIR__ . '/includes/header.php';

$db = Database::getConnection();
$vehicles = $db->query("SELECT * FROM vehicles WHERE is_active = 1 ORDER BY per_km_rate ASC")->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section">
  <div class="container">
    <div class="hero-grid">
      <!-- Left Hero Content -->
      <div class="hero-content">
        <div class="badge-pill">
          <span class="pulse-dot"></span>
          <span>Haridwar's Most Trusted Cab Agency</span>
        </div>
        <h1 class="hero-title">
          Travel Anywhere In India with <span>JAMBHO HARIDWAR TRAVELS</span>
        </h1>
        <p class="hero-desc">
          24×7 Premium & Affordable Outstation, Airport Transfer, Chardham Yatra & Local Cab Services from Haridwar. Verified drivers, neat & clean AC vehicles, transparent fares with zero hidden charges.
        </p>

        <div style="display: flex; gap: 16px; flex-wrap: wrap;">
          <a href="<?php echo BASE_URL; ?>/booking.php" class="btn btn-primary btn-lg">
            <span>🚖 Book a Cab Now</span>
          </a>
          <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>" target="_blank" class="btn btn-whatsapp btn-lg">
            <span>💬 WhatsApp Booking</span>
          </a>
        </div>

        <div class="hero-stats">
          <div class="stat-item">
            <h4>24/7</h4>
            <p>Availability</p>
          </div>
          <div class="stat-item">
            <h4>15,000+</h4>
            <p>Trips Completed</p>
          </div>
          <div class="stat-item">
            <h4>4.9 ★</h4>
            <p>Customer Rating</p>
          </div>
          <div class="stat-item">
            <h4>100%</h4>
            <p>Verified Drivers</p>
          </div>
        </div>
      </div>

      <!-- Right Quick Booking Widget (FR-004 to FR-009) -->
      <div class="booking-widget-card">
        <div class="booking-widget-header">
          <h3>⚡ Quick Cab Booking</h3>
          <p>Instant Fare Estimation & Online Confirmation</p>
        </div>

        <form action="<?php echo BASE_URL; ?>/booking_summary.php" method="POST">
          <!-- Trip Type Selector (FR-005) -->
          <div class="trip-tabs">
            <button type="button" class="trip-tab-btn active" data-trip="One Way">One Way</button>
            <button type="button" class="trip-tab-btn" data-trip="Round Trip">Round Trip</button>
            <button type="button" class="trip-tab-btn" data-trip="Local">Local (City)</button>
            <button type="button" class="trip-tab-btn" data-trip="Airport Transfer">Airport</button>
          </div>
          <input type="hidden" name="trip_type" id="trip_type_input" value="One Way">

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="pickup_location">📍 Pickup Location *</label>
              <input type="text" name="pickup_location" id="pickup_location" class="form-control" placeholder="e.g. Haridwar Railway Station / Hotel" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="drop_location">📍 Drop Location *</label>
              <input type="text" name="drop_location" id="drop_location" class="form-control" placeholder="e.g. IGI Airport Delhi / Rishikesh" required>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="journey_date">📅 Journey Date *</label>
              <input type="date" name="journey_date" id="journey_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="pickup_time">⏰ Pickup Time *</label>
              <select name="pickup_time" id="pickup_time" class="form-control" required>
                <option value="06:00 AM">06:00 AM</option>
                <option value="08:00 AM" selected>08:00 AM</option>
                <option value="10:00 AM">10:00 AM</option>
                <option value="12:00 PM">12:00 PM</option>
                <option value="02:00 PM">02:00 PM</option>
                <option value="04:00 PM">04:00 PM</option>
                <option value="06:00 PM">06:00 PM</option>
                <option value="08:00 PM">08:00 PM</option>
                <option value="10:00 PM (Night)">10:00 PM (Night)</option>
                <option value="11:30 PM (Night)">11:30 PM (Night)</option>
              </select>
            </div>
          </div>

          <div class="form-grid" id="return_date_group" style="display: none;">
            <div class="form-group">
              <label class="form-label" for="return_date">📅 Return Date</label>
              <input type="date" name="return_date" id="return_date" class="form-control" min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="return_time">⏰ Return Time</label>
              <input type="text" name="return_time" id="return_time" class="form-control" placeholder="e.g. 06:00 PM">
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="vehicle_id">🚘 Select Vehicle *</label>
              <select name="vehicle_id" id="vehicle_id" class="form-control" required>
                <?php foreach ($vehicles as $v): ?>
                  <option value="<?php echo $v['id']; ?>">
                    <?php echo htmlspecialchars($v['name']); ?> (₹<?php echo $v['per_km_rate']; ?>/km - <?php echo $v['seating_capacity']; ?> Seats)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="passengers">👤 Passengers</label>
              <select name="passengers" id="passengers" class="form-control">
                <option value="1">1 Passenger</option>
                <option value="2" selected>2 Passengers</option>
                <option value="3">3 Passengers</option>
                <option value="4">4 Passengers</option>
                <option value="5">5 Passengers</option>
                <option value="6">6 Passengers</option>
                <option value="7">7+ Passengers</option>
              </select>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label" for="customer_name">👤 Your Name *</label>
              <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="Full Name" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="customer_mobile">📱 Mobile Number *</label>
              <input type="tel" name="customer_mobile" id="customer_mobile" class="form-control" placeholder="10-digit Mobile" pattern="[0-9]{10}" required>
            </div>
          </div>

          <!-- Dynamic Live Fare Breakdown Preview (FR-008, FR-009) -->
          <div class="fare-estimate-box" id="live_fare_display" style="display: none;">
            <h4>
              <span>Estimated Fare Breakdown</span>
              <span class="badge-pill" style="font-size: 0.75rem; margin:0;">Transparent Pricing</span>
            </h4>
            <div class="fare-row">
              <span>Estimated Distance:</span>
              <strong id="est_distance_txt">230 KM</strong>
            </div>
            <div class="fare-row">
              <span>Per KM Rate:</span>
              <span id="est_rate_txt">₹11/KM</span>
            </div>
            <div class="fare-row">
              <span>Base Fare:</span>
              <span id="base_fare_txt">₹1,200</span>
            </div>
            <div class="fare-row">
              <span>Distance Charge:</span>
              <span id="distance_charge_txt">₹2,530</span>
            </div>
            <div class="fare-row" id="driver_allowance_row" style="display:none;">
              <span>Driver Allowance:</span>
              <span id="driver_allowance_txt">₹0</span>
            </div>
            <div class="fare-row" id="night_charge_row" style="display:none;">
              <span>Night Driving Charge:</span>
              <span id="night_charge_txt">₹250</span>
            </div>
            <div class="fare-row" id="toll_tax_row">
              <span>Toll & Border Tax (Est.):</span>
              <span id="toll_tax_txt">₹400</span>
            </div>
            <div class="fare-row total-row">
              <span>Total Estimated Fare:</span>
              <strong id="total_fare_txt">₹3,930</strong>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg btn-block">
            <span>Review Summary & Confirm Booking ➔</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- Services Showcase (FR-002) -->
<section class="section section-bg">
  <div class="container">
    <div class="section-header">
      <div class="badge-pill">Comprehensive Travel Solutions</div>
      <h2>Our Premium Cab Services</h2>
      <p>Whether you need a quick airport transfer or a multi-day Himalayan pilgrimage, Jambho Haridwar Travels has you covered 24 hours a day.</p>
    </div>

    <div class="services-grid">
      <div class="service-card">
        <div class="service-icon">🚗</div>
        <h3>One-Way & Outstation Cab</h3>
        <p>Door-to-door drops to Delhi, Noida, Gurgaon, Chandigarh, Agra, Jaipur, and all major cities at standard one-way rates.</p>
      </div>

      <div class="service-card">
        <div class="service-icon">✈️</div>
        <h3>Airport Pickup & Drop</h3>
        <p>Punctual transfers to IGI Airport New Delhi, Jolly Grant Airport Dehradun, and Chandigarh International Airport.</p>
      </div>

      <div class="service-card">
        <div class="service-icon">⛰️</div>
        <h3>Chardham & Himalayan Tours</h3>
        <p>Specialized luxury cab packages for Kedarnath, Badrinath, Gangotri, Yamunotri, Mussoorie, and Nainital with seasoned mountain drivers.</p>
      </div>

      <div class="service-card">
        <div class="service-icon">🔄</div>
        <h3>Round Trip Packages</h3>
        <p>Flexible multi-day round trips with dedicated AC vehicle and courteous driver for family vacations and corporate travel.</p>
      </div>

      <div class="service-card">
        <div class="service-icon">🏙️</div>
        <h3>Local Haridwar & Rishikesh Sightseeing</h3>
        <p>Full-day 8hr/80km local packages for Ganga Aarti, Mansa Devi, Chandi Devi, Ram Jhula, Laxman Jhula, and Neelkanth Mahadev.</p>
      </div>

      <div class="service-card">
        <div class="service-icon">🚐</div>
        <h3>Tempo Traveller Group Rentals</h3>
        <p>13, 17, and 26-seater luxury pushback Tempo Travellers for group pilgrimages, wedding parties, and corporate events.</p>
      </div>
    </div>
  </div>
</section>

<!-- Fleet Showcase (FR-003) -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="badge-pill">Sanitized & Maintained Fleet</div>
      <h2>Choose Your Preferred Cab</h2>
      <p>From economic sedans to executive luxury Innova Crysta and high-capacity Tempo Travellers.</p>
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
              <span class="spec-pill">👥 <?php echo $veh['seating_capacity']; ?> Seats</span>
              <span class="spec-pill">🧳 <?php echo $veh['luggage_capacity']; ?> Bags</span>
              <span class="spec-pill">❄️ <?php echo htmlspecialchars($veh['ac_type']); ?></span>
            </div>

            <p style="font-size: 0.88rem; margin-bottom: 20px;">
              <?php echo htmlspecialchars($veh['description']); ?>
            </p>

            <div class="fleet-price-row">
              <div class="fleet-rate-box">
                <span>Outstation Rate</span>
                <strong>₹<?php echo $veh['per_km_rate']; ?> / KM</strong>
              </div>
              <a href="<?php echo BASE_URL; ?>/booking.php?vehicle_id=<?php echo $veh['id']; ?>" class="btn btn-primary btn-sm">
                <span>Book Cab ➔</span>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Why Choose Us & Safety Features -->
<section class="section section-bg">
  <div class="container">
    <div class="section-header">
      <div class="badge-pill">Jambho Haridwar Travels Promise</div>
      <h2>Why Travel With Jambho Haridwar Travels?</h2>
      <p>We pride ourselves on 100% punctuality, hospitality, clean vehicles, and transparent pricing.</p>
    </div>

    <div class="features-grid">
      <div class="feature-box">
        <div class="feature-icon-circle">⏰</div>
        <h3>24×7 Instant Dispatch</h3>
        <p>Available day and night for emergency station drops, late night flights, and planned tours.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon-circle">🛡️</div>
        <h3>Verified & Skilled Drivers</h3>
        <p>Police-verified drivers with extensive experience on both high-speed expressways and challenging hill terrain.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon-circle">💬</div>
        <h3>WhatsApp Automation</h3>
        <p>Instant booking confirmation, driver details, invoice, and live trip reminders sent right to your WhatsApp.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon-circle">💳</div>
        <h3>Secure Online Payments</h3>
        <p>Multiple online payment options via Razorpay (UPI, Credit/Debit Cards, Net Banking) with instant confirmation.</p>
      </div>
    </div>
  </div>
</section>

<!-- Customer Reviews & Testimonials -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="badge-pill">Real Customer Experiences</div>
      <h2>What Our Travelers Say</h2>
      <p>Thousands of satisfied pilgrims, business travelers, and families trust Jambho Haridwar Travels.</p>
    </div>

    <div class="reviews-grid">
      <div class="review-card">
        <div>
          <div class="stars">★★★★★</div>
          <p class="review-text">
            "Booked an Innova Crysta for our family Chardham Yatra. The car was spotless, and the driver Rajesh was extremely polite and skilled on the mountain roads. Asheesh Bishnoi ji personally coordinated everything!"
          </p>
        </div>
        <div class="reviewer-info">
          <div class="reviewer-avatar">RK</div>
          <div>
            <h4 style="font-size: 0.95rem; color: #fff;">Rameshwar Kulkarni</h4>
            <small style="color: var(--text-dim);">Pune, Maharashtra (Chardham Tour)</small>
          </div>
        </div>
      </div>

      <div class="review-card">
        <div>
          <div class="stars">★★★★★</div>
          <p class="review-text">
            "Needed an early morning 4 AM cab from Haridwar to Delhi Airport. The driver was at our hotel 15 minutes before time. Received automated WhatsApp confirmation and payment receipt immediately. 10/10 service!"
          </p>
        </div>
        <div class="reviewer-info">
          <div class="reviewer-avatar">PS</div>
          <div>
            <h4 style="font-size: 0.95rem; color: #fff;">Priya Sharma</h4>
            <small style="color: var(--text-dim);">New Delhi (Airport Transfer)</small>
          </div>
        </div>
      </div>

      <div class="review-card">
        <div>
          <div class="stars">★★★★★</div>
          <p class="review-text">
            "Best taxi service in Haridwar! Transparent per-KM pricing with no surprise tolls or night charges at the end. The WhatsApp auto-reply feature made tracking and booking super convenient."
          </p>
        </div>
        <div class="reviewer-info">
          <div class="reviewer-avatar">AS</div>
          <div>
            <h4 style="font-size: 0.95rem; color: #fff;">Amit Singhal</h4>
            <small style="color: var(--text-dim);">Jaipur, Rajasthan (Haridwar-Rishikesh Trip)</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Call to Action Banner -->
<section class="section" style="padding-top: 0;">
  <div class="container">
    <div style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); border: 1px solid var(--border-color); border-radius: var(--radius-xl); padding: 50px 40px; text-align: center; position: relative; overflow: hidden;">
      <h2 style="font-size: 2.2rem; margin-bottom: 16px;">Need An Immediate Cab In Haridwar?</h2>
      <p style="max-width: 600px; margin: 0 auto 30px; font-size: 1.05rem;">
        Speak directly with <strong><?php echo OWNER_NAME; ?></strong> for custom tour itineraries, wedding fleet bookings, or instant cab dispatch.
      </p>
      <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
        <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="btn btn-call btn-lg">
          <span>📞 Call: <?php echo PHONE_PRIMARY; ?></span>
        </a>
        <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>" target="_blank" class="btn btn-whatsapp btn-lg">
          <span>💬 WhatsApp: <?php echo PHONE_PRIMARY; ?></span>
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
