<?php
/**
 * Bishnoi Travels - Database Connection & Auto-Migration Handler
 * Supports both SQLite PDO (Zero-config) & MySQL PDO
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                if (DB_DRIVER === 'sqlite') {
                    $dbDir = dirname(DB_SQLITE_PATH);
                    if (!is_dir($dbDir)) {
                        mkdir($dbDir, 0777, true);
                    }
                    $isNew = !file_exists(DB_SQLITE_PATH) || filesize(DB_SQLITE_PATH) === 0;
                    self::$instance = new PDO('sqlite:' . DB_SQLITE_PATH);
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    self::$instance->exec("PRAGMA foreign_keys = ON;");

                    if ($isNew) {
                        self::migrateAndSeedSQLite(self::$instance);
                    }
                } else {
                    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                    self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                }
            } catch (PDOException $e) {
                die("Database Connection Error: " . htmlspecialchars($e->getMessage()));
            }
        }
        return self::$instance;
    }

    private static function migrateAndSeedSQLite(PDO $db): void {
        $sql = "
        -- Admins Table
        CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            name TEXT NOT NULL,
            email TEXT,
            role TEXT DEFAULT 'superadmin',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Vehicles / Fleet Table
        CREATE TABLE IF NOT EXISTS vehicles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            category TEXT NOT NULL, -- Sedan, SUV, Premium Sedan, Innova, Innova Crysta, Tempo Traveller
            model_example TEXT,
            image_url TEXT,
            seating_capacity INTEGER NOT NULL,
            luggage_capacity INTEGER NOT NULL,
            ac_type TEXT DEFAULT 'AC',
            per_km_rate REAL NOT NULL,
            base_fare REAL NOT NULL,
            min_km INTEGER DEFAULT 250,
            driver_allowance_per_day REAL DEFAULT 300,
            description TEXT,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Drivers Table
        CREATE TABLE IF NOT EXISTS drivers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            mobile TEXT NOT NULL,
            license_no TEXT NOT NULL,
            vehicle_number TEXT,
            assigned_vehicle_type TEXT,
            is_active INTEGER DEFAULT 1,
            current_status TEXT DEFAULT 'Available', -- Available, On Trip, Off Duty
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Customers Table
        CREATE TABLE IF NOT EXISTS customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            mobile TEXT NOT NULL UNIQUE,
            email TEXT,
            address TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Bookings Table (FRS Compliant)
        CREATE TABLE IF NOT EXISTS bookings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            booking_id TEXT UNIQUE NOT NULL, -- e.g. BT-20260815-001
            customer_id INTEGER,
            customer_name TEXT NOT NULL,
            customer_mobile TEXT NOT NULL,
            customer_email TEXT,
            trip_type TEXT NOT NULL, -- One Way, Round Trip, Local, Airport Transfer
            pickup_location TEXT NOT NULL,
            drop_location TEXT NOT NULL,
            journey_date DATE NOT NULL,
            pickup_time TEXT NOT NULL,
            return_date DATE,
            return_time TEXT,
            vehicle_id INTEGER,
            vehicle_name TEXT,
            passengers INTEGER DEFAULT 1,
            flight_train_no TEXT,
            special_requirements TEXT,
            estimated_distance REAL DEFAULT 0,
            base_fare REAL DEFAULT 0,
            distance_charge REAL DEFAULT 0,
            driver_allowance REAL DEFAULT 0,
            night_charge REAL DEFAULT 0,
            toll_tax_charge REAL DEFAULT 0,
            total_amount REAL NOT NULL,
            advance_paid REAL DEFAULT 0,
            balance_amount REAL DEFAULT 0,
            booking_status TEXT DEFAULT 'New', -- New, Payment Pending, Payment Failed, Confirmed, Driver Assigned, Driver On The Way, Trip Started, Trip Completed, Cancelled, Refund Initiated, Refund Completed
            driver_id INTEGER,
            assigned_driver_name TEXT,
            assigned_driver_mobile TEXT,
            assigned_vehicle_no TEXT,
            cancellation_reason TEXT,
            refund_amount REAL DEFAULT 0,
            refund_status TEXT, -- Refund Pending, Refund Initiated, Refund Completed, Refund Failed
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id),
            FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
            FOREIGN KEY (driver_id) REFERENCES drivers(id)
        );

        -- Payments Table
        CREATE TABLE IF NOT EXISTS payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            booking_id TEXT NOT NULL,
            gateway TEXT DEFAULT 'Razorpay',
            gateway_order_id TEXT,
            transaction_id TEXT,
            signature TEXT,
            amount REAL NOT NULL,
            currency TEXT DEFAULT 'INR',
            payment_status TEXT DEFAULT 'Pending', -- Pending, Paid, Failed, Refunded
            payment_method TEXT,
            gateway_response TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
        );

        -- WhatsApp Interaction Logs
        CREATE TABLE IF NOT EXISTS whatsapp_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            phone_number TEXT NOT NULL,
            message_direction TEXT NOT NULL, -- inbound, outbound
            message_type TEXT DEFAULT 'text',
            message_body TEXT NOT NULL,
            template_name TEXT,
            status TEXT DEFAULT 'sent', -- received, sent, delivered, failed
            booking_id TEXT,
            raw_payload TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Enquiries Table
        CREATE TABLE IF NOT EXISTS enquiries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            mobile TEXT NOT NULL,
            email TEXT,
            travel_from TEXT,
            travel_to TEXT,
            travel_date DATE,
            passengers INTEGER,
            message TEXT,
            status TEXT DEFAULT 'New', -- New, Contacted, Closed
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Fare Settings Table
        CREATE TABLE IF NOT EXISTS fare_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT UNIQUE NOT NULL,
            setting_value TEXT NOT NULL,
            description TEXT
        );
        ";

        $db->exec($sql);

        // Seed Default Admin: admin / admin123
        $adminPass = password_hash(ADMIN_DEFAULT_PASS, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT OR IGNORE INTO admins (username, password_hash, name, email, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', $adminPass, 'Asheesh Bishnoi (Admin)', 'admin@bishnoitravels.com', 'superadmin']);

        // Seed Fleet / Vehicles (FRS Compliant: Sedan, SUV, Premium Sedan, Innova, Innova Crysta, Tempo Traveller)
        $vehicles = [
            [
                'name' => 'Sedan (Dzire / Etios)',
                'category' => 'Sedan',
                'model_example' => 'Maruti Dzire, Toyota Etios',
                'image_url' => 'assets/images/sedan.svg',
                'seating_capacity' => 4,
                'luggage_capacity' => 2,
                'ac_type' => 'AC',
                'per_km_rate' => 11.0,
                'base_fare' => 1200.0,
                'min_km' => 250,
                'driver_allowance_per_day' => 300.0,
                'description' => 'Ideal for budget family trips, airport pickup and city commutes with top fuel efficiency & AC comfort.'
            ],
            [
                'name' => 'Premium Sedan (Verna / Ciaz / City)',
                'category' => 'Premium Sedan',
                'model_example' => 'Hyundai Verna, Honda City',
                'image_url' => 'assets/images/premium_sedan.svg',
                'seating_capacity' => 4,
                'luggage_capacity' => 3,
                'ac_type' => 'Dual AC',
                'per_km_rate' => 13.5,
                'base_fare' => 1600.0,
                'min_km' => 250,
                'driver_allowance_per_day' => 350.0,
                'description' => 'Executive comfort with plush interiors, extra legroom, and refined suspension for long outstation journeys.'
            ],
            [
                'name' => 'Compact SUV (Ertiga / Triber)',
                'category' => 'SUV',
                'model_example' => 'Maruti Ertiga, Renault Triber',
                'image_url' => 'assets/images/suv.svg',
                'seating_capacity' => 6,
                'luggage_capacity' => 3,
                'ac_type' => 'Dual AC',
                'per_km_rate' => 15.0,
                'base_fare' => 1800.0,
                'min_km' => 250,
                'driver_allowance_per_day' => 350.0,
                'description' => 'Spacious 6-seater MUV/SUV perfect for family tours across Haridwar, Rishikesh, Dehradun & Hill stations.'
            ],
            [
                'name' => 'Toyota Innova',
                'category' => 'Innova',
                'model_example' => 'Toyota Innova (7+1 Seater)',
                'image_url' => 'assets/images/innova.svg',
                'seating_capacity' => 7,
                'luggage_capacity' => 4,
                'ac_type' => 'Tri-Zone AC',
                'per_km_rate' => 17.5,
                'base_fare' => 2200.0,
                'min_km' => 250,
                'driver_allowance_per_day' => 400.0,
                'description' => 'The ultimate king of Indian highways. Reliable, ultra-comfortable seating for long distance outstation trips.'
            ],
            [
                'name' => 'Toyota Innova Crysta',
                'category' => 'Innova Crysta',
                'model_example' => 'Innova Crysta Luxury',
                'image_url' => 'assets/images/innova_crysta.svg',
                'seating_capacity' => 7,
                'luggage_capacity' => 5,
                'ac_type' => 'Automatic Climate Control',
                'per_km_rate' => 21.0,
                'base_fare' => 2800.0,
                'min_km' => 250,
                'driver_allowance_per_day' => 450.0,
                'description' => 'Top tier luxury and unmatched ride quality. Captain seats, ambient lighting, and elite highway performance.'
            ],
            [
                'name' => 'Tempo Traveller (13/17 Seater)',
                'category' => 'Tempo Traveller',
                'model_example' => 'Force Tempo Traveller Luxury',
                'image_url' => 'assets/images/tempo_traveller.svg',
                'seating_capacity' => 13,
                'luggage_capacity' => 10,
                'ac_type' => 'Roof Mounted AC',
                'per_km_rate' => 26.0,
                'base_fare' => 4000.0,
                'min_km' => 300,
                'driver_allowance_per_day' => 500.0,
                'description' => 'Best for group pilgrimages, Chardham Yatra, corporate outings, and large family weddings with pushback seats.'
            ]
        ];

        $vStmt = $db->prepare("INSERT INTO vehicles (name, category, model_example, image_url, seating_capacity, luggage_capacity, ac_type, per_km_rate, base_fare, min_km, driver_allowance_per_day, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($vehicles as $v) {
            $vStmt->execute([
                $v['name'], $v['category'], $v['model_example'], $v['image_url'],
                $v['seating_capacity'], $v['luggage_capacity'], $v['ac_type'],
                $v['per_km_rate'], $v['base_fare'], $v['min_km'],
                $v['driver_allowance_per_day'], $v['description']
            ]);
        }

        // Seed Sample Drivers
        $drivers = [
            ['Rajesh Sharma', '9876543210', 'UK08-2019-00342', 'UK08-AB-1234', 'Sedan', 1, 'Available'],
            ['Mukesh Bishnoi', '9536200261', 'UK08-2017-00912', 'UK08-TA-5678', 'Innova Crysta', 1, 'Available'],
            ['Suresh Rawat', '8449911315', 'UK07-2018-00451', 'UK07-PA-9988', 'SUV', 1, 'Available'],
            ['Virender Singh', '9412003344', 'UK08-2020-00876', 'UK08-TT-4455', 'Tempo Traveller', 1, 'Available']
        ];
        $dStmt = $db->prepare("INSERT INTO drivers (name, mobile, license_no, vehicle_number, assigned_vehicle_type, is_active, current_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($drivers as $d) {
            $dStmt->execute($d);
        }

        // Seed Default Dynamic Fare Settings
        $settings = [
            ['night_charge_rate', '250', 'Night driving allowance applied between 10:00 PM and 06:00 AM (INR)'],
            ['night_start_time', '22:00', 'Night charge starting time (24h format)'],
            ['night_end_time', '06:00', 'Night charge ending time (24h format)'],
            ['toll_tax_estimated_rate', '200', 'Estimated toll & state border tax per 100 KM for outstation trips'],
            ['advance_payment_percentage', '25', 'Required advance booking percentage (e.g. 25% or 100%)'],
            ['cancellation_free_hours', '12', 'Hours before journey when 100% refund is eligible'],
            ['cancellation_charge_percentage', '15', 'Deduction percentage if cancelled within free limit window']
        ];
        $sStmt = $db->prepare("INSERT INTO fare_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
        foreach ($settings as $s) {
            $sStmt->execute($s);
        }

        // Seed Sample Booking for Demonstration
        $sampleBookingId = 'BT-' . date('Ymd') . '-001';
        $bStmt = $db->prepare("INSERT INTO bookings (
            booking_id, customer_name, customer_mobile, customer_email, trip_type, 
            pickup_location, drop_location, journey_date, pickup_time, vehicle_id, 
            vehicle_name, passengers, estimated_distance, base_fare, distance_charge, 
            driver_allowance, night_charge, toll_tax_charge, total_amount, advance_paid, 
            balance_amount, booking_status, driver_id, assigned_driver_name, assigned_driver_mobile, assigned_vehicle_no
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $bStmt->execute([
            $sampleBookingId, 'Tushar Vishnoi', '9536200261', 'tushar@example.com', 'One Way',
            'Haridwar Railway Station', 'IGI Airport, New Delhi', date('Y-m-d', strtotime('+1 day')), '08:00 AM', 1,
            'Sedan (Dzire / Etios)', 2, 230, 1200, 2530, 0, 0, 400, 3930, 3930, 0,
            'Confirmed', 1, 'Rajesh Sharma', '9876543210', 'UK08-AB-1234'
        ]);

        // Seed Payment for Sample Booking
        $pStmt = $db->prepare("INSERT INTO payments (booking_id, gateway, gateway_order_id, transaction_id, signature, amount, currency, payment_status, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $pStmt->execute([
            $sampleBookingId, 'Razorpay', 'order_BT_' . time(), 'pay_demo_' . rand(100000, 999999),
            'sig_verified_sample', 3930.0, 'INR', 'Paid', 'UPI / NetBanking'
        ]);

        // Seed Sample WhatsApp Log
        $wStmt = $db->prepare("INSERT INTO whatsapp_logs (phone_number, message_direction, message_type, message_body, template_name, status, booking_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $wStmt->execute([
            '919536200261', 'outbound', 'template',
            "🚕 *Bishnoi Travels – Booking Confirmed!*\n\nBooking ID: *{$sampleBookingId}*\nPickup: Haridwar Railway Station\nDrop: IGI Airport, New Delhi\nDate: " . date('d-M-Y', strtotime('+1 day')) . "\nTime: 08:00 AM\nCab: Sedan (Dzire / Etios)\nDriver: Rajesh Sharma (9876543210)\nAmount: ₹3,930 (PAID)\n\nThank you for choosing Bishnoi Travels! 24x7 Helpline: 9536200261",
            'booking_confirmation', 'delivered', $sampleBookingId
        ]);
    }
}
