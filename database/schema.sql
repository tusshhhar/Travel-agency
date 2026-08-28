-- ==========================================================
-- Bishnoi Travels - Database Schema (MySQL / MariaDB)
-- Document Version: 1.0 (FRS Compliant)
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `bishnoi_travels` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bishnoi_travels`;

-- 1. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(60) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(120),
    `role` VARCHAR(30) DEFAULT 'superadmin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Vehicles / Fleet Table
CREATE TABLE IF NOT EXISTS `vehicles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `model_example` VARCHAR(120),
    `image_url` VARCHAR(255),
    `seating_capacity` INT NOT NULL,
    `luggage_capacity` INT NOT NULL,
    `ac_type` VARCHAR(40) DEFAULT 'AC',
    `per_km_rate` DECIMAL(10,2) NOT NULL,
    `base_fare` DECIMAL(10,2) NOT NULL,
    `min_km` INT DEFAULT 250,
    `driver_allowance_per_day` DECIMAL(10,2) DEFAULT 300.00,
    `description` TEXT,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Drivers Table
CREATE TABLE IF NOT EXISTS `drivers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `mobile` VARCHAR(20) NOT NULL,
    `license_no` VARCHAR(50) NOT NULL,
    `vehicle_number` VARCHAR(30),
    `assigned_vehicle_type` VARCHAR(50),
    `is_active` TINYINT(1) DEFAULT 1,
    `current_status` VARCHAR(30) DEFAULT 'Available',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Customers Table
CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `mobile` VARCHAR(20) NOT NULL UNIQUE,
    `email` VARCHAR(120),
    `address` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Bookings Table (FRS Compliant)
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` VARCHAR(50) UNIQUE NOT NULL,
    `customer_id` INT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_mobile` VARCHAR(20) NOT NULL,
    `customer_email` VARCHAR(120),
    `trip_type` VARCHAR(40) NOT NULL,
    `pickup_location` VARCHAR(255) NOT NULL,
    `drop_location` VARCHAR(255) NOT NULL,
    `journey_date` DATE NOT NULL,
    `pickup_time` VARCHAR(30) NOT NULL,
    `return_date` DATE NULL,
    `return_time` VARCHAR(30) NULL,
    `vehicle_id` INT NULL,
    `vehicle_name` VARCHAR(120),
    `passengers` INT DEFAULT 1,
    `flight_train_no` VARCHAR(50) NULL,
    `special_requirements` TEXT NULL,
    `estimated_distance` DECIMAL(10,2) DEFAULT 0,
    `base_fare` DECIMAL(10,2) DEFAULT 0,
    `distance_charge` DECIMAL(10,2) DEFAULT 0,
    `driver_allowance` DECIMAL(10,2) DEFAULT 0,
    `night_charge` DECIMAL(10,2) DEFAULT 0,
    `toll_tax_charge` DECIMAL(10,2) DEFAULT 0,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `advance_paid` DECIMAL(10,2) DEFAULT 0,
    `balance_amount` DECIMAL(10,2) DEFAULT 0,
    `booking_status` VARCHAR(40) DEFAULT 'New',
    `driver_id` INT NULL,
    `assigned_driver_name` VARCHAR(100) NULL,
    `assigned_driver_mobile` VARCHAR(20) NULL,
    `assigned_vehicle_no` VARCHAR(30) NULL,
    `cancellation_reason` TEXT NULL,
    `refund_amount` DECIMAL(10,2) DEFAULT 0,
    `refund_status` VARCHAR(40) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`booking_id`),
    INDEX (`customer_mobile`),
    INDEX (`journey_date`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`driver_id`) REFERENCES `drivers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 6. Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` VARCHAR(50) NOT NULL,
    `gateway` VARCHAR(50) DEFAULT 'Razorpay',
    `gateway_order_id` VARCHAR(100),
    `transaction_id` VARCHAR(100),
    `signature` VARCHAR(255),
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(10) DEFAULT 'INR',
    `payment_status` VARCHAR(30) DEFAULT 'Pending',
    `payment_method` VARCHAR(50),
    `gateway_response` LONGTEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`booking_id`),
    INDEX (`transaction_id`)
) ENGINE=InnoDB;

-- 7. WhatsApp Interaction Logs
CREATE TABLE IF NOT EXISTS `whatsapp_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `phone_number` VARCHAR(30) NOT NULL,
    `message_direction` ENUM('inbound', 'outbound') NOT NULL,
    `message_type` VARCHAR(30) DEFAULT 'text',
    `message_body` TEXT NOT NULL,
    `template_name` VARCHAR(80),
    `status` VARCHAR(30) DEFAULT 'sent',
    `booking_id` VARCHAR(50),
    `raw_payload` LONGTEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 8. Enquiries Table
CREATE TABLE IF NOT EXISTS `enquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `mobile` VARCHAR(20) NOT NULL,
    `email` VARCHAR(120),
    `travel_from` VARCHAR(255),
    `travel_to` VARCHAR(255),
    `travel_date` DATE,
    `passengers` INT,
    `message` TEXT,
    `status` VARCHAR(30) DEFAULT 'New',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 9. Fare Settings Table
CREATE TABLE IF NOT EXISTS `fare_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(80) UNIQUE NOT NULL,
    `setting_value` TEXT NOT NULL,
    `description` VARCHAR(255)
) ENGINE=InnoDB;
