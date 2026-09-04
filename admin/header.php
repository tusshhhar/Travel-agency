<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdminLogin();
$adminUser = getAdminUser();
$currentAdminPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo defined('ADMIN_PAGE_TITLE') ? ADMIN_PAGE_TITLE . ' - Admin' : 'Admin Panel'; ?> | <?php echo BUSINESS_NAME; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
</head>
<body class="admin-body">

  <!-- Admin Sidebar -->
  <aside class="admin-sidebar">
    <div class="sidebar-header">
      <h2>JAMBHO HARIDWAR <span>TRAVELS</span></h2>
      <small>Admin Control Center</small>
    </div>

    <ul class="sidebar-nav">
      <li class="nav-section-title">Core Modules</li>
      <li>
        <a href="<?php echo BASE_URL; ?>/admin/index.php" class="sidebar-link <?php echo $currentAdminPage === 'index.php' ? 'active' : ''; ?>">
          <span>📊 Dashboard</span>
        </a>
      </li>
      <li>
        <a href="<?php echo BASE_URL; ?>/admin/bookings.php" class="sidebar-link <?php echo in_array($currentAdminPage, ['bookings.php', 'booking_details.php']) ? 'active' : ''; ?>">
          <span>🚖 Cab Bookings</span>
        </a>
      </li>
      <li>
        <a href="<?php echo BASE_URL; ?>/admin/vehicles.php" class="sidebar-link <?php echo $currentAdminPage === 'vehicles.php' ? 'active' : ''; ?>">
          <span>🚘 Fleet & Vehicles</span>
        </a>
      </li>
      <li>
        <a href="<?php echo BASE_URL; ?>/admin/drivers.php" class="sidebar-link <?php echo $currentAdminPage === 'drivers.php' ? 'active' : ''; ?>">
          <span>👤 Drivers & Cabs</span>
        </a>
      </li>
      <li>
        <a href="<?php echo BASE_URL; ?>/admin/fares.php" class="sidebar-link <?php echo $currentAdminPage === 'fares.php' ? 'active' : ''; ?>">
          <span>🏷️ Fare Rules Config</span>
        </a>
      </li>

      <li class="nav-section-title" style="margin-top: 16px;">Communications & Leads</li>
      <li>
        <a href="<?php echo BASE_URL; ?>/admin/whatsapp_logs.php" class="sidebar-link <?php echo $currentAdminPage === 'whatsapp_logs.php' ? 'active' : ''; ?>">
          <span>💬 WhatsApp Logs</span>
        </a>
      </li>
      <li>
        <a href="<?php echo BASE_URL; ?>/admin/enquiries.php" class="sidebar-link <?php echo $currentAdminPage === 'enquiries.php' ? 'active' : ''; ?>">
          <span>✉️ Customer Enquiries</span>
        </a>
      </li>
      <li>
        <a href="<?php echo BASE_URL; ?>/admin/settings.php" class="sidebar-link <?php echo $currentAdminPage === 'settings.php' ? 'active' : ''; ?>">
          <span>⚙️ System Settings</span>
        </a>
      </li>

      <li class="nav-section-title" style="margin-top: 16px;">Quick Links</li>
      <li>
        <a href="<?php echo BASE_URL; ?>/index.php" target="_blank" class="sidebar-link">
          <span>🌐 View Live Website ↗</span>
        </a>
      </li>
      <li>
        <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="sidebar-link" style="color: #f87171;">
          <span>🚪 Logout</span>
        </a>
      </li>
    </ul>

    <div class="sidebar-footer">
      <div style="font-size: 0.8rem; color: var(--text-dim);">
        Logged in as:<br>
        <strong style="color: #fff;"><?php echo htmlspecialchars($adminUser['name']); ?></strong>
      </div>
    </div>
  </aside>

  <!-- Admin Main Body Area -->
  <main class="admin-main">
    <header class="admin-topbar">
      <div style="display: flex; align-items: center; gap: 12px;">
        <button type="button" class="admin-mobile-toggle" onclick="document.querySelector('.admin-sidebar').classList.toggle('active')" aria-label="Toggle Navigation">☰</button>
        <div class="admin-title">
          <?php echo defined('ADMIN_PAGE_TITLE') ? ADMIN_PAGE_TITLE : 'Dashboard'; ?>
        </div>
      </div>
      <div class="admin-user-menu">
        <span class="pulse-dot"></span>
        <span style="font-size: 0.85rem; color: var(--text-muted);">24×7 System Online</span>
        <a href="<?php echo BASE_URL; ?>/admin/logout.php" class="btn btn-secondary btn-sm" style="margin-left: 14px;">Logout</a>
      </div>
    </header>

    <div class="admin-content">
      <!-- Flash Alert Banner if any -->
      <?php 
      $flash = getFlashMessage();
      if ($flash): 
      ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
          <span><?php echo $flash['text']; ?></span>
        </div>
      <?php endif; ?>
