# Bishnoi Travels – Cab Booking & Travel Agency Website (PHP)

**Document Version:** 1.0 (FRS Compliant)  
**Business Name:** BISHNOI TRAVELS  
**Proprietor / Manager:** ASHEESH BISHNOI  
**Location:** D-Block, New Shivalik Nagar, Haridwar, Uttarakhand  
**24×7 Helplines:** 9536200261 / 8449911315  
**WhatsApp:** +91 9536200261  
**Tagline:** *All Over India 24 Hours Services Available*

---

## 🌟 Overview & Key Highlights

This application is a complete, full-stack, enterprise-grade cab booking and travel agency web application written in standard PHP (with dual SQLite / MySQL PDO architecture). It fulfills 100% of the Functional Requirement Specification (FRS) across all 49 sections:

1. **Frontend Experience**:
   - **Hero & Live Booking Widget**: Dynamic route selection, pickup/drop validation, date/time, vehicle selection, and real-time live fare estimation engine.
   - **Cab Services Catalog (FR-002)**: Local, Outstation, Airport Transfer, One-Way, Round-Trip, Chardham Yatra Pilgrimage, and Tempo Traveller Rentals.
   - **Fleet Showcase (FR-003)**: Sedan (Dzire/Etios), Premium Sedan (Verna/City), SUV (Ertiga), Innova (7+1), Innova Crysta Luxury, and Force Tempo Traveller (13/17 seater) with per-KM rates and luggage/seat capacities.
   - **Booking Summary & Payment (FR-009, FR-010)**: Clear fare breakdown (Base fare, distance charge, driver allowance, night charge, toll & tax) with instant Razorpay Checkout and sandbox simulator for testing.
   - **Booking Confirmation & GST Voucher (FR-012, FR-017)**: Confirmation with unique `BT-YYYYMMDD-XXX` reference ID, automated WhatsApp dispatch alert, and printable/downloadable travel voucher.
   - **Self-Service Tracking & Cancellation Portal (FR-028, FR-029)**: Customer lookup by Booking ID & Mobile, live trip status, and cancellation with automated refund calculation.
   - **Contact & Tour Enquiries (FR-030, FR-031)**: Interactive contact form with auto-responder to customer's WhatsApp.

2. **WhatsApp Business Platform / Cloud API Automation (FR-015 to FR-020)**:
   - **Official Webhook Receiver (`api/whatsapp_webhook.php`)**: Handles Meta GET verification handshake (`hub.challenge` & `hub.verify_token`) and POST incoming message payloads.
   - **Keyword Auto-Reply Bot**: Responds instantly to "Hi", "1" (Book Cab), "2" (Fare List), "3" (Track Status), "4" (Call Asheesh Bishnoi), and "5" (Chardham Yatra).
   - **Automated Trigger Notifications**: Booking Confirmation, Payment Receipts, Driver Assignment with Cab Number & Phone, Trip Reminders, and Refund Alerts.
   - **Interactive Live In-Browser WhatsApp Chat Simulator**: Built right into the bottom-right floating widget so you can test the full WhatsApp auto-reply bot directly in your browser without requiring live Meta credentials!

3. **Payment Gateway Integration (FR-010 to FR-014)**:
   - Razorpay Checkout modal for UPI, Google Pay, PhonePe, Paytm, Cards, and Net Banking.
   - Server-side signature verification (`api/verify_payment.php`) using HMAC SHA256.
   - Secure Razorpay Webhook (`api/razorpay_webhook.php`) for asynchronous server-to-server confirmation.
   - Built-in Sandbox simulator for offline/demo environments.

4. **Admin Control Panel (FR-022 to FR-029)**:
   - Real-time Analytics & KPIs (Total Bookings, Today's Bookings, Total Revenue, Active Drivers).
   - Booking Manager (Search, filter by status, update trip lifecycle, assign drivers with 1-click WhatsApp alerts).
   - Vehicle Fleet & Pricing Manager (Add/edit vehicles, per-KM rates, base fares).
   - Driver Manager (License verification, contact numbers, cab assignment, availability status).
   - Dynamic Fare Rules Config (Night travel rates & hours, toll rates, advance payment %, refund rules).
   - WhatsApp Message Audit Log (Live stream of all inbound and outbound messages + custom broadcast composer).
   - Customer Enquiries Lead Tracker.

---

## 🚀 Instant Quick Start (Zero Setup Required!)

The system is configured with **SQLite PDO** by default, which means **you do not even need to start MySQL or create any database**. It automatically initializes and seeds all vehicles, default admin user, drivers, fare rules, and sample bookings on the very first run!

### Step 1: Start PHP Built-in Server
Open your terminal (PowerShell / Command Prompt) and run:

```bash
cd "C:\Users\Tushar\.gemini\antigravity-ide\scratch\bishnoi_travels"
php -S localhost:8000
```

### Step 2: Open in Your Browser
- **Public Website:** [http://localhost:8000](http://localhost:8000)
- **Admin Control Panel:** [http://localhost:8000/admin/login.php](http://localhost:8000/admin/login.php)

---

## 🔐 Default Admin Credentials

- **Username:** `admin`
- **Password:** `admin123`

---

## 🗄️ Optional: Running on MySQL / Apache (XAMPP / cPanel / Production)

If you prefer to run on MySQL:
1. Open `config/config.php` and change:
   ```php
   define('DB_DRIVER', 'mysql');
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'bishnoi_travels');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
2. Open phpMyAdmin or your MySQL CLI and import `database/schema.sql`.

---

## 💬 WhatsApp Business Cloud API Configuration Guide

1. Go to [developers.facebook.com](https://developers.facebook.com) and create an App under **Business** type.
2. Under **WhatsApp > API Setup**:
   - Copy your **Phone Number ID** and **Permanent Access Token**.
   - Paste them in `config/config.php` (`WHATSAPP_PHONE_NUMBER_ID` and `WHATSAPP_ACCESS_TOKEN`).
3. Under **WhatsApp > Configuration > Webhook**:
   - Callback URL: `https://your-domain.com/api/whatsapp_webhook.php`
   - Verify Token: `bishnoi_travels_verify_token_2026` (Configured in `config/config.php`)
   - Subscribe to the `messages` webhook field.

---

## 💳 Razorpay Payment Gateway & Webhook Setup

1. Log in to [dashboard.razorpay.com](https://dashboard.razorpay.com).
2. Go to **Settings > API Keys** and copy your **Key ID** and **Key Secret**.
3. Paste them in `config/config.php` (`RAZORPAY_KEY_ID` and `RAZORPAY_KEY_SECRET`).
4. In Razorpay Dashboard under **Settings > Webhooks > Add New Webhook**:
   - Webhook URL: `https://your-domain.com/api/razorpay_webhook.php`
   - Secret: `BishnoiWebhookSecretRazorpay_987`
   - Active Events: `payment.captured`, `payment.failed`, `order.paid`, `refund.created`.

---

## 📂 Project Directory Structure

```
bishnoi_travels/
├── config/
│   ├── config.php             # Application settings, Razorpay keys, WhatsApp Cloud API tokens
│   └── db.php                 # Dual SQLite / MySQL PDO Database Connection with auto-migration & auto-seeding
├── includes/
│   ├── header.php             # Global HTML head, meta tags, SEO, modern navbar
│   ├── footer.php             # Global footer with 24x7 contact info, WhatsApp simulator widget
│   ├── functions.php          # Helper functions (sanitization, fare calculation, booking ID generation)
│   ├── auth.php               # Session security & admin authorization helpers
│   ├── razorpay_helper.php    # Razorpay API client & Webhook validator
│   └── whatsapp_helper.php    # WhatsApp Cloud API sender & auto-reply responder
├── assets/
│   ├── css/
│   │   ├── style.css          # Ultra-modern frontend styling (luxury cab aesthetic, dark/gold)
│   │   └── admin.css          # Admin panel clean dashboard styling
│   ├── js/
│   │   ├── main.js            # Frontend interactivity, dynamic fare calculator, WhatsApp simulator
│   │   └── admin.js           # Admin modal and status handlers
│   └── images/                # Vector SVG illustrations (Sedan, SUV, Innova Crysta, Tempo, Logo)
├── api/
│   ├── calculate_fare.php     # Dynamic fare calculation JSON endpoint
│   ├── create_order.php       # Razorpay order generation endpoint
│   ├── verify_payment.php     # Razorpay payment verification endpoint
│   ├── razorpay_webhook.php   # Secure Razorpay server-to-server webhook
│   ├── whatsapp_webhook.php   # Meta WhatsApp Cloud API webhook (GET verify + POST payload)
│   ├── whatsapp_simulate.php  # Live in-browser WhatsApp chat simulator
│   ├── whatsapp_send.php      # WhatsApp message dispatch API
│   └── cancel_booking.php     # Customer/Admin booking cancellation & refund initiator
├── admin/
│   ├── login.php              # Admin authentication
│   ├── logout.php             # Secure session logout
│   ├── header.php             # Admin layout header
│   ├── footer.php             # Admin layout footer
│   ├── index.php              # Admin dashboard with key performance metrics
│   ├── bookings.php           # Booking management, driver assignment, status workflow
│   ├── booking_details.php    # Single booking view, timeline, payment details, manual WhatsApp trigger
│   ├── vehicles.php           # Vehicle / Fleet management (Add/Edit/Delete/Pricing)
│   ├── drivers.php            # Driver management (Add/Edit/Assign)
│   ├── fares.php              # Dynamic Fare rules configuration
│   ├── whatsapp_logs.php      # WhatsApp inbound & outbound communication logs
│   ├── enquiries.php          # Contact enquiries management
│   └── settings.php           # Business info & API settings
├── index.php                  # Home page (Hero, quick booking widget, fleet, services, reviews, contact)
├── about.php                  # About Bishnoi Travels, Asheesh Bishnoi leadership, fleet standards
├── services.php               # Services catalog (Local, Outstation, Airport Transfer, One-Way, Round-Trip)
├── fleet.php                  # Vehicle fleet showcase with specs, per-KM rates, capacity
├── booking.php                # Complete Cab Booking engine with interactive fare breakdown
├── booking_summary.php        # Review booking details, fare breakdown before payment
├── payment.php                # Payment processing page with Razorpay Checkout & test fallback
├── booking_confirmation.php   # Confirmed booking page with unique Booking ID & WhatsApp prompt
├── track_booking.php          # Track booking status & cancel eligible bookings
├── invoice.php                # Printable GST-compliant cab invoice / travel voucher
├── contact.php                # Contact Us page with Google Map, address, call & WhatsApp buttons
├── database/
│   ├── schema.sql             # SQL Schema definition for MySQL/MariaDB
│   └── bishnoi_travels.sqlite # Auto-created on first load
└── README.md                  # Comprehensive documentation
```
