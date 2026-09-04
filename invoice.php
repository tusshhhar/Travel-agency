<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$bookingId = cleanInput($_GET['booking_id'] ?? '');

if (empty($bookingId)) {
    die("Invalid Booking Reference ID.");
}

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if (!$booking) {
    die("Booking not found.");
}

// Fetch payment details
$pStmt = $db->prepare("SELECT * FROM payments WHERE booking_id = ? ORDER BY id DESC LIMIT 1");
$pStmt->execute([$bookingId]);
$payment = $pStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Travel Voucher & Receipt - <?php echo htmlspecialchars($booking['booking_id']); ?> | <?php echo BUSINESS_NAME; ?></title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap');
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background: #f1f5f9;
      color: #1e293b;
      padding: 40px 20px;
    }
    .invoice-card {
      max-width: 800px;
      margin: 0 auto;
      background: #fff;
      border-radius: 12px;
      padding: 40px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: 1px solid #e2e8f0;
    }
    .invoice-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 2px solid #e2e8f0;
      padding-bottom: 24px;
      margin-bottom: 24px;
    }
    .brand-title {
      font-family: 'Outfit', sans-serif;
      font-size: 24px;
      font-weight: 800;
      color: #0f172a;
    }
    .brand-title span { color: #d97706; }
    .brand-meta {
      font-size: 12px;
      color: #64748b;
      margin-top: 4px;
      line-height: 1.5;
    }
    .invoice-ref {
      text-align: right;
    }
    .invoice-ref h2 {
      font-family: 'Outfit', sans-serif;
      font-size: 20px;
      color: #0f172a;
    }
    .grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin-bottom: 24px;
    }
    .info-box {
      background: #f8fafc;
      padding: 16px;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      font-size: 13px;
      line-height: 1.6;
    }
    .info-box h4 {
      font-size: 14px;
      color: #0f172a;
      margin-bottom: 8px;
      border-bottom: 1px solid #cbd5e1;
      padding-bottom: 4px;
    }
    table.invoice-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 24px;
      font-size: 13px;
    }
    table.invoice-table th {
      background: #0f172a;
      color: #fff;
      padding: 10px 14px;
      text-align: left;
    }
    table.invoice-table td {
      padding: 12px 14px;
      border-bottom: 1px solid #e2e8f0;
    }
    .total-section {
      text-align: right;
      font-size: 14px;
      margin-bottom: 30px;
    }
    .total-row {
      display: flex;
      justify-content: flex-end;
      gap: 30px;
      padding: 6px 0;
    }
    .grand-total {
      font-size: 18px;
      font-weight: 800;
      color: #0f172a;
      border-top: 2px solid #0f172a;
      padding-top: 8px;
      margin-top: 4px;
    }
    .terms-box {
      border-top: 1px solid #e2e8f0;
      padding-top: 16px;
      font-size: 11px;
      color: #64748b;
      line-height: 1.5;
    }
    .print-btn-bar {
      max-width: 800px;
      margin: 0 auto 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .btn-print {
      background: #d97706;
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
    }
    @media (max-width: 640px) {
      body { padding: 12px; }
      .invoice-card { padding: 20px 14px; border-radius: 8px; }
      .invoice-header { flex-direction: column; gap: 16px; align-items: flex-start; }
      .invoice-ref { text-align: left; }
      .grid-2 { grid-template-columns: 1fr; gap: 14px; }
      .invoice-table th, .invoice-table td { padding: 8px 6px; font-size: 11px; }
      .print-btn-bar { flex-direction: column; gap: 10px; align-items: stretch; text-align: center; }
      .print-btn-bar .btn-print { width: 100%; }
    }
    @media print {
      body { background: #fff; padding: 0; }
      .invoice-card { box-shadow: none; border: none; padding: 0; }
      .print-btn-bar { display: none; }
    }
  </style>
</head>
<body>

  <div class="print-btn-bar">
    <a href="<?php echo BASE_URL; ?>/index.php" style="color: #64748b; text-decoration: none; font-size: 14px;">← Back to Jambho Haridwar Travels</a>
    <button class="btn-print" onclick="window.print()">🖨️ Print / Save as PDF</button>
  </div>

  <div class="invoice-card">
    
    <div class="invoice-header">
      <div>
        <div class="brand-title">JAMBHO HARIDWAR <span>TRAVELS</span></div>
        <div class="brand-meta">
          <strong>Proprietor:</strong> <?php echo OWNER_NAME; ?><br>
          <strong>Address:</strong> <?php echo BUSINESS_ADDRESS; ?><br>
          <strong>24x7 Helplines:</strong> <?php echo PHONE_PRIMARY; ?> / <?php echo PHONE_SECONDARY; ?><br>
          <strong>WhatsApp:</strong> +<?php echo WHATSAPP_NUMBER; ?>
        </div>
      </div>
      <div class="invoice-ref">
        <h2>CAB VOUCHER & INVOICE</h2>
        <div style="margin-top: 6px; font-size: 13px;">
          <strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['booking_id']); ?><br>
          <strong>Invoice Date:</strong> <?php echo date('d-M-Y', strtotime($booking['created_at'])); ?><br>
          <strong>Status:</strong> <span style="color: #059669; font-weight: bold;"><?php echo strtoupper($booking['booking_status']); ?></span>
        </div>
      </div>
    </div>

    <div class="grid-2">
      <div class="info-box">
        <h4>Passenger Details</h4>
        <strong>Name:</strong> <?php echo htmlspecialchars($booking['customer_name']); ?><br>
        <strong>Mobile:</strong> <?php echo htmlspecialchars($booking['customer_mobile']); ?><br>
        <strong>Email:</strong> <?php echo htmlspecialchars($booking['customer_email'] ?: 'N/A'); ?><br>
        <strong>Passengers:</strong> <?php echo $booking['passengers']; ?> Person(s)
      </div>

      <div class="info-box">
        <h4>Journey Details</h4>
        <strong>Trip Type:</strong> <?php echo htmlspecialchars($booking['trip_type']); ?><br>
        <strong>Pickup:</strong> <?php echo htmlspecialchars($booking['pickup_location']); ?><br>
        <strong>Destination:</strong> <?php echo htmlspecialchars($booking['drop_location']); ?><br>
        <strong>Travel Date:</strong> <?php echo date('d-M-Y', strtotime($booking['journey_date'])); ?> (<?php echo htmlspecialchars($booking['pickup_time']); ?>)
      </div>
    </div>

    <div class="info-box" style="margin-bottom: 24px;">
      <h4>Assigned Vehicle & Driver</h4>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <div><strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['vehicle_name']); ?></div>
        <div><strong>Vehicle No:</strong> <?php echo htmlspecialchars($booking['assigned_vehicle_no'] ?: 'UK08-AB-1234'); ?></div>
        <div><strong>Driver Name:</strong> <?php echo htmlspecialchars($booking['assigned_driver_name'] ?: 'Rajesh Sharma'); ?></div>
        <div><strong>Driver Contact:</strong> <?php echo htmlspecialchars($booking['assigned_driver_mobile'] ?: PHONE_PRIMARY); ?></div>
      </div>
    </div>

    <!-- Charges Table -->
    <table class="invoice-table">
      <thead>
        <tr>
          <th>Description</th>
          <th>Rate / Unit</th>
          <th style="text-align: right;">Amount (INR)</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($booking['base_fare'] > 0): ?>
          <tr>
            <td>Base Cab Fare</td>
            <td>Standard</td>
            <td style="text-align: right;">₹<?php echo number_format($booking['base_fare'], 2); ?></td>
          </tr>
        <?php endif; ?>
        <tr>
          <td>Distance Charges (~<?php echo $booking['estimated_distance']; ?> KM)</td>
          <td>Distance Based</td>
          <td style="text-align: right;">₹<?php echo number_format($booking['distance_charge'], 2); ?></td>
        </tr>
        <?php if ($booking['driver_allowance'] > 0): ?>
          <tr>
            <td>Driver Day Allowance</td>
            <td>Per Day</td>
            <td style="text-align: right;">₹<?php echo number_format($booking['driver_allowance'], 2); ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($booking['night_charge'] > 0): ?>
          <tr>
            <td>Night Driving Allowance (10 PM - 6 AM)</td>
            <td>Night Shift</td>
            <td style="text-align: right;">₹<?php echo number_format($booking['night_charge'], 2); ?></td>
          </tr>
        <?php endif; ?>
        <?php if ($booking['toll_tax_charge'] > 0): ?>
          <tr>
            <td>Estimated Toll, Parking & State Border Taxes</td>
            <td>Govt. Taxes</td>
            <td style="text-align: right;">₹<?php echo number_format($booking['toll_tax_charge'], 2); ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div class="total-section">
      <div class="total-row">
        <span>Total Fare Amount:</span>
        <span>₹<?php echo number_format($booking['total_amount'], 2); ?></span>
      </div>
      <div class="total-row">
        <span>Payment Method:</span>
        <span style="color: #059669; font-weight: bold;">Pay to Driver (Cash / UPI on Trip)</span>
      </div>
      <div class="total-row grand-total">
        <span>Total Payable on Trip:</span>
        <span>₹<?php echo number_format($booking['total_amount'], 2); ?></span>
      </div>
    </div>

    <div class="terms-box">
      <strong>Terms & Conditions:</strong><br>
      1. Extra KM beyond billed package will be charged at vehicle standard rate. Toll taxes and state permits are included as estimated.<br>
      2. 100% refund on cancellations made at least 12 hours prior to journey time.<br>
      3. For any assistance during your journey, call <strong><?php echo OWNER_NAME; ?></strong> at <?php echo PHONE_PRIMARY; ?> / <?php echo PHONE_SECONDARY; ?>.
    </div>

    <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: flex-end; font-size: 12px;">
      <div>
        <strong>Customer Signature:</strong> _______________________
      </div>
      <div style="text-align: right;">
        <strong>For JAMBHO HARIDWAR TRAVELS</strong><br><br>
        <em>Authorized Signatory</em>
      </div>
    </div>

  </div>

</body>
</html>
