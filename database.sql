-- =======================================================
-- Online Auditorium Booking System - Complete Database Schema
-- Database: online_auditorium_booking
-- Includes: Customers, Owners, Admin, Auditoriums, Bookings, Payments, 
--           Catering Module, Event Services Module, and Notification Module
-- =======================================================

CREATE DATABASE IF NOT EXISTS `online_auditorium_booking`;
USE `online_auditorium_booking`;

-- 1. Users Table (Customers)
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(15) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Owners Table (Auditorium Owners with ID Proof Verification)
CREATE TABLE IF NOT EXISTS `owners` (
  `owner_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(15) DEFAULT NULL,
  `id_proof_type` VARCHAR(50) DEFAULT 'Aadhaar Card',
  `id_proof_number` VARCHAR(100) DEFAULT NULL,
  `id_proof_file` VARCHAR(255) DEFAULT NULL,
  `is_verified` TINYINT(1) DEFAULT 0, -- 0 = Pending, 1 = Verified, 2 = Rejected
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Admin Table
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
);

-- 4. Auditoriums Table
CREATE TABLE IF NOT EXISTS `auditoriums` (
  `auditorium_id` INT AUTO_INCREMENT PRIMARY KEY,
  `owner_id` INT NOT NULL,
  `auditorium_name` VARCHAR(150) NOT NULL,
  `location` VARCHAR(150) NOT NULL,
  `capacity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `facilities` VARCHAR(255) DEFAULT 'AC, Sound System, Stage, Parking',
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT 'auditorium1.jpg',
  `status` VARCHAR(20) DEFAULT 'Available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Services Table (Catering & Event Services)
CREATE TABLE IF NOT EXISTS `services` (
  `service_id` INT AUTO_INCREMENT PRIMARY KEY,
  `auditorium_id` INT NOT NULL,
  `service_name` VARCHAR(150) NOT NULL,
  `service_category` VARCHAR(50) NOT NULL, -- 'Catering', 'Decoration', 'Photography', 'DJ/Sound', 'Lighting', 'Security', 'Cleaning', 'Stage'
  `price` DECIMAL(10,2) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Bookings Table (With Advance & Balance amounts)
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `auditorium_id` INT NOT NULL,
  `booking_date` DATE NOT NULL,
  `event_name` VARCHAR(150) NOT NULL,
  `guest_count` INT DEFAULT 100,
  `base_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `services_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_price` DECIMAL(10,2) NOT NULL,
  `advance_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `balance_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_type` VARCHAR(20) DEFAULT 'Full', -- 'Full' or 'Advance'
  `status` VARCHAR(20) DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Booking Services Table (Selected Add-ons for a Booking)
CREATE TABLE IF NOT EXISTS `booking_services` (
  `booking_service_id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `service_name` VARCHAR(150) NOT NULL,
  `service_price` DECIMAL(10,2) NOT NULL
);

-- 8. Payments Table (Supports Advance, Full, and Balance payments)
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `payment_type` VARCHAR(20) DEFAULT 'Full', -- 'Full', 'Advance', 'Balance'
  `payment_status` VARCHAR(20) DEFAULT 'Paid',
  `transaction_id` VARCHAR(100) DEFAULT 'TXN_DEMO',
  `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. Notifications Table (Booking Confirmation, Payment Receipt, Event Reminders)
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `user_type` VARCHAR(20) DEFAULT 'customer', -- 'customer' or 'owner' or 'admin'
  `booking_id` INT DEFAULT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(50) DEFAULT 'confirmation', -- 'confirmation', 'payment', 'reminder'
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample Data
INSERT INTO `admin` (`admin_id`, `email`, `password`) VALUES (1, 'admin@gmail.com', 'admin123') ON DUPLICATE KEY UPDATE `email` = 'admin@gmail.com';
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`) VALUES (1, 'Rahul Sharma', 'rahul@gmail.com', 'rahul123', '9876543210') ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);
INSERT INTO `owners` (`owner_id`, `name`, `email`, `password`, `phone`) VALUES (1, 'John Fernandez', 'john@gmail.com', 'owner123', '9845012345') ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);