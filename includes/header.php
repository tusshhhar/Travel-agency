<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo defined('PAGE_TITLE') ? PAGE_TITLE . ' | ' . BUSINESS_NAME : BUSINESS_NAME . ' - 24x7 Cab & Travel Services Haridwar'; ?></title>
  <meta name="description" content="Bishnoi Travels Haridwar - Best 24x7 Outstation & Local Cab Service in Haridwar, Rishikesh, Dehradun, Delhi, Chardham Yatra. Clean AC Cabs & Experienced Drivers. Call: 9536200261">
  <meta name="keywords" content="Bishnoi Travels, Cab in Haridwar, Taxi Service Haridwar, Chardham Yatra Cab, Haridwar to Delhi Cab, Innova Crysta Rental, Asheesh Bishnoi">
  
  <!-- CSS Stylesheets -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
  
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>/assets/images/logo.svg">
</head>
<body>

  <!-- Top Emergency / 24x7 Contact Bar -->
  <div class="top-bar">
    <div class="container top-bar-inner">
      <div class="top-bar-tagline">
        <span class="pulse-dot"></span>
        <span><strong><?php echo BUSINESS_NAME; ?></strong> • <?php echo BUSINESS_TAGLINE; ?></span>
      </div>
      <div class="top-bar-contacts">
        <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="top-bar-link">
          <span>📞 <?php echo PHONE_PRIMARY; ?></span>
        </a>
        <a href="tel:<?php echo PHONE_SECONDARY; ?>" class="top-bar-link">
          <span>📱 <?php echo PHONE_SECONDARY; ?></span>
        </a>
        <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>" target="_blank" class="top-bar-link">
          <span>💬 WhatsApp Support</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Main Header & Navigation -->
  <header class="site-header">
    <div class="container navbar">
      <a href="<?php echo BASE_URL; ?>/index.php" class="brand-logo">
        <div class="brand-text">
          <h1>BISHNOI <span>TRAVELS</span></h1>
          <small>24×7 ALL INDIA CAB SERVICES</small>
        </div>
      </a>

      <button class="mobile-nav-toggle" aria-label="Toggle navigation">☰</button>

      <ul class="nav-menu">
        <li><a href="<?php echo BASE_URL; ?>/index.php" class="nav-link <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Home</a></li>
        <li><a href="<?php echo BASE_URL; ?>/services.php" class="nav-link <?php echo $currentPage === 'services.php' ? 'active' : ''; ?>">Cab Services</a></li>
        <li><a href="<?php echo BASE_URL; ?>/fleet.php" class="nav-link <?php echo $currentPage === 'fleet.php' ? 'active' : ''; ?>">Our Fleet</a></li>
        <li><a href="<?php echo BASE_URL; ?>/about.php" class="nav-link <?php echo $currentPage === 'about.php' ? 'active' : ''; ?>">About Us</a></li>
        <li><a href="<?php echo BASE_URL; ?>/track_booking.php" class="nav-link <?php echo $currentPage === 'track_booking.php' ? 'active' : ''; ?>">Track / Cancel</a></li>
        <li><a href="<?php echo BASE_URL; ?>/contact.php" class="nav-link <?php echo $currentPage === 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
        <li><a href="<?php echo BASE_URL; ?>/admin/login.php" class="nav-link">Admin</a></li>
      </ul>

      <div class="nav-actions">
        <a href="tel:<?php echo PHONE_PRIMARY; ?>" class="btn btn-call btn-sm">
          <span>📞 Call Now</span>
        </a>
        <a href="<?php echo BASE_URL; ?>/booking.php" class="btn btn-primary btn-sm">
          <span>🚖 Book a Cab</span>
        </a>
      </div>
    </div>
  </header>

  <!-- Flash Message Banner if any -->
  <div class="container" style="margin-top: 15px;">
    <?php 
    $flash = getFlashMessage();
    if ($flash): 
    ?>
      <div class="alert alert-<?php echo $flash['type']; ?>">
        <span><?php echo $flash['text']; ?></span>
      </div>
    <?php endif; ?>
  </div>
