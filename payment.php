<?php
define('PAGE_TITLE', 'Complete Online Payment');
require_once __DIR__ . '/includes/header.php';

$bookingId = cleanInput($_GET['booking_id'] ?? '');

if (empty($bookingId)) {
    header('Location: ' . BASE_URL . '/booking.php');
    exit;
}

$db = Database::getConnection();
$stmt = $db->prepare("SELECT * FROM bookings WHERE booking_id = ?");
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if (!$booking) {
    echo "<div class='container' style='padding: 50px;'><div class='alert alert-error'>Booking not found.</div></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

if ($booking['booking_status'] === 'Confirmed') {
    header('Location: ' . BASE_URL . '/booking_confirmation.php?booking_id=' . urlencode($bookingId));
    exit;
}

$amountInPaise = (int)round($booking['total_amount'] * 100);
$currency = 'INR';
?>

<section class="section" style="padding-top: 40px;">
  <div class="container">
    <div class="section-header" style="margin-bottom: 30px;">
      <div class="badge-pill">Step 3 of 3 • Payment Gateway</div>
      <h2>Complete Your Booking Payment</h2>
      <p>Pay securely via Razorpay (UPI, Google Pay, PhonePe, Paytm, Debit/Credit Card, Net Banking).</p>
    </div>

    <div style="max-width: 650px; margin: 0 auto;">
      <div class="booking-widget-card" style="padding: 36px; text-align: center;">
        
        <div style="width: 72px; height: 72px; background: rgba(245, 158, 11, 0.15); border: 2px solid var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 20px;">
          💳
        </div>

        <h3 style="font-size: 1.6rem; margin-bottom: 8px;">Payable Amount: <span style="color: var(--primary);">₹<?php echo number_format($booking['total_amount'], 2); ?></span></h3>
        <p style="margin-bottom: 24px; color: var(--text-muted);">Booking Reference ID: <strong><?php echo htmlspecialchars($booking['booking_id']); ?></strong></p>

        <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; text-align: left; margin-bottom: 28px;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem;">
            <span style="color: var(--text-dim);">Customer:</span>
            <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
          </div>
          <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.9rem;">
            <span style="color: var(--text-dim);">Cab Type:</span>
            <strong><?php echo htmlspecialchars($booking['vehicle_name']); ?></strong>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
            <span style="color: var(--text-dim);">Travel Date:</span>
            <strong><?php echo date('d-M-Y', strtotime($booking['journey_date'])); ?> (<?php echo htmlspecialchars($booking['pickup_time']); ?>)</strong>
          </div>
        </div>

        <!-- Payment Action Buttons -->
        <button id="rzp_pay_btn" class="btn btn-primary btn-lg btn-block" style="margin-bottom: 16px;">
          <span>🔒 Pay ₹<?php echo number_format($booking['total_amount'], 2); ?> with Razorpay</span>
        </button>

        <!-- Sandbox Simulation for Instant QA & Offline Testing (FR-011, FR-012, FR-013) -->
        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color);">
          <span style="display: block; font-size: 0.8rem; color: var(--text-dim); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 1px;">
            🧪 Test Sandbox Simulator (Instant QA Verification)
          </span>
          <div style="display: flex; gap: 10px;">
            <button type="button" onclick="simulatePayment(true)" class="btn btn-secondary btn-sm" style="flex: 1; border-color: #10b981; color: #34d399;">
              <span>✅ Simulate Success (UPI)</span>
            </button>
            <button type="button" onclick="simulatePayment(false)" class="btn btn-secondary btn-sm" style="flex: 1; border-color: #ef4444; color: #f87171;">
              <span>❌ Simulate Failure</span>
            </button>
          </div>
        </div>

        <div id="payment_status_alert" style="margin-top: 20px; display: none;"></div>

      </div>
    </div>
  </div>
</section>

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const bookingData = {
  booking_id: "<?php echo $booking['booking_id']; ?>",
  amount: <?php echo $amountInPaise; ?>,
  currency: "INR",
  name: "<?php echo BUSINESS_NAME; ?>",
  customer_name: "<?php echo addslashes($booking['customer_name']); ?>",
  customer_email: "<?php echo addslashes($booking['customer_email']); ?>",
  customer_mobile: "<?php echo addslashes($booking['customer_mobile']); ?>"
};

document.getElementById('rzp_pay_btn').addEventListener('click', function(e) {
  e.preventDefault();

  // Initialize Order via API
  fetch('api/create_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ booking_id: bookingData.booking_id })
  })
  .then(res => res.json())
  .then(orderData => {
    if (!orderData.success) {
      alert("Failed to initialize payment order: " + (orderData.message || 'Error'));
      return;
    }

    const options = {
      key: orderData.key_id,
      amount: orderData.amount,
      currency: orderData.currency,
      name: bookingData.name,
      description: "Cab Booking - " + bookingData.booking_id,
      order_id: orderData.is_simulated ? null : orderData.order_id,
      prefill: {
        name: bookingData.customer_name,
        email: bookingData.customer_email,
        contact: bookingData.customer_mobile
      },
      theme: { color: "#f59e0b" },
      handler: function(response) {
        verifyPayment(response.razorpay_payment_id, response.razorpay_order_id, response.razorpay_signature, 'Razorpay Modal');
      }
    };

    // If sandbox simulator fallback is enabled
    if (orderData.is_simulated) {
      simulatePayment(true);
      return;
    }

    const rzp = new Razorpay(options);
    rzp.on('payment.failed', function(resp) {
      alert("Payment Failed: " + resp.error.description);
    });
    rzp.open();
  })
  .catch(err => {
    console.error(err);
    simulatePayment(true);
  });
});

function simulatePayment(isSuccess) {
  const alertBox = document.getElementById('payment_status_alert');
  alertBox.style.display = 'block';
  alertBox.className = 'alert alert-warning';
  alertBox.textContent = 'Processing transaction with payment gateway...';

  if (isSuccess) {
    const mockPaymentId = "pay_test_" + Math.random().toString(36).substr(2, 9);
    const mockOrderId = "order_test_" + Math.random().toString(36).substr(2, 9);
    const mockSig = "test_sig_" + Math.random().toString(36).substr(2, 9);
    verifyPayment(mockPaymentId, mockOrderId, mockSig, 'UPI Simulation');
  } else {
    setTimeout(() => {
      alertBox.className = 'alert alert-error';
      alertBox.textContent = '❌ Payment Failed or declined by bank. Please retry.';
    }, 800);
  }
}

function verifyPayment(paymentId, orderId, signature, method) {
  fetch('api/verify_payment.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      booking_id: bookingData.booking_id,
      payment_id: paymentId,
      order_id: orderId,
      signature: signature,
      payment_method: method
    })
  })
  .then(res => res.json())
  .then(res => {
    if (res.success) {
      window.location.href = res.redirect_url;
    } else {
      const alertBox = document.getElementById('payment_status_alert');
      alertBox.style.display = 'block';
      alertBox.className = 'alert alert-error';
      alertBox.textContent = res.message || 'Payment verification failed.';
    }
  });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
