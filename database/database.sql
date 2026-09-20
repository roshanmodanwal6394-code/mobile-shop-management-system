-- =======================================================
-- MOBILE SHOP MANAGEMENT SYSTEM - DATABASE SCHEMA
-- Database: mobile_shop_management
-- Suitable for XAMPP & phpMyAdmin (India Market Data)
-- =======================================================

CREATE DATABASE IF NOT EXISTS `mobile_shop_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mobile_shop_management`;

-- --------------------------------------------------------
-- Table clean up in foreign-key dependency order
-- --------------------------------------------------------
DROP TABLE IF EXISTS `sale_items`;
DROP TABLE IF EXISTS `sales`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `mobiles`;
DROP TABLE IF EXISTS `brands`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------
-- 1. Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
  `phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Table structure for table `brands`
-- --------------------------------------------------------
CREATE TABLE `brands` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Table structure for table `mobiles`
-- --------------------------------------------------------
CREATE TABLE `mobiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `ram` VARCHAR(50) NOT NULL,
  `storage` VARCHAR(50) NOT NULL,
  `color` VARCHAR(50) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `description` TEXT DEFAULT NULL,
  `image` VARCHAR(255) DEFAULT 'default_mobile.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_mobiles_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. Table structure for table `customers`
-- --------------------------------------------------------
CREATE TABLE `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_customers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. Table structure for table `sales`
-- --------------------------------------------------------
CREATE TABLE `sales` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_no` VARCHAR(50) NOT NULL UNIQUE,
  `customer_id` INT NOT NULL,
  `sale_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
  `status` ENUM('Completed', 'Pending', 'Cancelled') NOT NULL DEFAULT 'Completed',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_sales_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. Table structure for table `sale_items`
-- --------------------------------------------------------
CREATE TABLE `sale_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sale_id` INT NOT NULL,
  `mobile_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10, 2) NOT NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL,
  CONSTRAINT `fk_sale_items_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_sale_items_mobile` FOREIGN KEY (`mobile_id`) REFERENCES `mobiles` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- =======================================================
-- REALISTIC INDIAN DEMO DATA
-- =======================================================

-- 1. Insert Users (Admin & Customers)
-- Passwords:
-- admin@mobileshop.com      -> admin123
-- aditya.sharma@example.com -> aditya123
-- sneha.joshi@example.com   -> sneha123
-- rohan.kulkarni@example.com-> rohan123
-- neha.patil@example.com    -> neha123
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`) VALUES
(1, 'Rahul Patil', 'admin@mobileshop.com', '$2y$10$.oL9GX2.nUmVfQh6VXA9f.VkS.n5fybZtGtKDcuA7pNExAxZwIaYy', 'admin', '9822114477', 'Shop 14, Tech Point, FC Road, Pune'),
(2, 'Aditya Sharma', 'aditya.sharma@example.com', '$2y$10$r8/1wtYz1qnpaxwdLEFoU.s6RFu2My9RXY4M13/Ylbcd.4o.e/YwK', 'customer', '9820123456', 'B-302, Gokul Heights, Baner, Pune'),
(3, 'Sneha Joshi', 'sneha.joshi@example.com', '$2y$10$Qm.XImmMqIXhzBE0yDrnlu8lD1wxguW1iPeXAAiFIzxnG572RT5eq', 'customer', '9765432109', 'Flat 12, Shivneri Society, Kothrud, Pune'),
(4, 'Rohan Kulkarni', 'rohan.kulkarni@example.com', '$2y$10$Ew6aDEZ8dYojpdtp3okS0.eXbWPF0Jeq1FxnFLBMK1GnpBSAZs4Bu', 'customer', '9881234567', 'Plot 45, Viman Nagar, Pune'),
(5, 'Neha Patil', 'neha.patil@example.com', '$2y$10$8DcFPDQOEKQdFtXh5Qwa5.KFTWxPxx4qUmrmYb.8bnI9gMDKN3WAq', 'customer', '9922334455', 'A-101, Green Acres, Wakad, Pune');

-- 2. Insert Popular Smartphone Brands in India
INSERT INTO `brands` (`id`, `name`, `description`) VALUES
(1, 'Samsung', 'Global Android leader offering Galaxy M, A, and flagship S series with Super AMOLED displays.'),
(2, 'OnePlus', 'Premium speed-focused smartphones with OxygenOS, Alert Slider, and SUPERVOOC fast charging.'),
(3, 'Xiaomi', 'High-value feature-packed phones featuring Redmi Note series and Leica-engineered flagship devices.'),
(4, 'Realme', 'Trendsetting smartphones with youth-centric designs, ultra-fast charging, and periscope cameras.'),
(5, 'Vivo', 'Pioneering camera innovation featuring Aura Light portraits and Zeiss optics integration.'),
(6, 'Oppo', 'Sleek design pioneers known for Reno and F series portrait photography and durable build quality.'),
(7, 'Motorola', 'Clean Android experience with durable military-grade builds, curved pOLED displays, and IP68 ratings.'),
(8, 'iQOO', 'High-performance gaming-centric smartphones featuring segment-leading benchmark scores and cooling.'),
(9, 'Nothing', 'London-based tech innovator renowned for transparent aesthetics, Glyph Interface, and clean Nothing OS.');

-- 3. Insert 20 Realistic Indian-Market Smartphone Products
INSERT INTO `mobiles` (`id`, `brand_id`, `name`, `model`, `ram`, `storage`, `color`, `price`, `stock`, `description`, `image`) VALUES
(1, 1, 'Samsung Galaxy M35 5G', 'SM-M356B', '6 GB', '128 GB', 'Daybreak Blue', 16999.00, 14, 'Exynos 1380 processor, 50MP No Shake OIS camera, massive 6000mAh battery, 120Hz sAMOLED display with Gorilla Glass Victus+.', 'samsung_galaxy_m35.png'),
(2, 1, 'Samsung Galaxy A35 5G', 'SM-A356E', '8 GB', '128 GB', 'Awesome Lilac', 27999.00, 9, 'Premium glass back design, IP67 water and dust resistance, 50MP triple camera setup with Nightography, Knox Vault security.', 'samsung_galaxy_a35.png'),
(3, 1, 'Samsung Galaxy S24 Ultra 5G', 'SM-S928B', '12 GB', '256 GB', 'Titanium Gray', 129999.00, 4, 'Snapdragon 8 Gen 3 for Galaxy, Quad Telephoto camera with 200MP sensor, Titanium frame, built-in S-Pen, Galaxy AI suite.', 'samsung_s24_ultra.png'),
(4, 2, 'OnePlus Nord CE 4', 'CPH2613', '8 GB', '128 GB', 'Celadon Marble', 24999.00, 12, 'Snapdragon 7 Gen 3, 100W SUPERVOOC charging, 5500mAh battery, 50MP Sony LYT-600 sensor with OIS, 120Hz Fluid AMOLED.', 'oneplus_nord_ce4.png'),
(5, 2, 'OnePlus Nord 4 5G', 'CPH2661', '8 GB', '256 GB', 'Oasis Green', 32999.00, 8, 'All-metal unibody design, Snapdragon 7+ Gen 3 chipset, 5500mAh battery with 100W charging, 6 years of guaranteed software support.', 'oneplus_nord_4.png'),
(6, 2, 'OnePlus 12R 5G', 'CPH2585', '16 GB', '256 GB', 'Cool Blue', 42999.00, 6, 'Snapdragon 8 Gen 2 processor, 4th Gen LTPO 120Hz ProXDR display, 5500mAh battery, Cryo-velocity VC cooling system.', 'oneplus_12r.png'),
(7, 3, 'Redmi Note 13 Pro 5G', '2312DRA50I', '8 GB', '128 GB', 'Arctic White', 21999.00, 11, '200MP ultra-clear camera with OIS, Snapdragon 7s Gen 2, 1.5K 120Hz AMOLED display, 67W turbo charge with 5100mAh battery.', 'redmi_note_13_pro.png'),
(8, 3, 'Redmi Note 14 5G', '24090RA29I', '6 GB', '128 GB', 'Phantom Purple', 14999.00, 15, 'MediaTek Dimensity 7025 Ultra 5G, 50MP Sony LYT-600 camera, 5110mAh battery with 45W fast charging, 120Hz FHD+ OLED display.', 'redmi_note_14.png'),
(9, 3, 'Xiaomi 14 Civi', '24053PY09I', '8 GB', '256 GB', 'Matcha Green', 42999.00, 5, 'Leica professional triple camera system, Dual 32MP selfie cameras, Snapdragon 8s Gen 3, quad-curved cinematic AMOLED display.', 'xiaomi_14_civi.png'),
(10, 4, 'Realme P1 5G', 'RMX3870', '6 GB', '128 GB', 'Phoenix Red', 14999.00, 16, 'MediaTek Dimensity 7050 5G chipset, 120Hz AMOLED display with in-display fingerprint, 45W SUPERVOOC charge, 50MP AI camera.', 'realme_p1_5g.png'),
(11, 4, 'Realme Narzo 70 Pro 5G', 'RMX3868', '8 GB', '128 GB', 'Glass Green', 18999.00, 10, 'Flagship Sony IMX890 OIS camera, Air Gesture controls, Horizon glass design, 67W SUPERVOOC charging with 5000mAh battery.', 'realme_narzo_70_pro.png'),
(12, 4, 'Realme 12 Pro+ 5G', 'RMX3840', '8 GB', '256 GB', 'Submarine Blue', 29999.00, 2, '64MP Periscope portrait camera with 3x optical zoom, Snapdragon 7s Gen 2, luxury watch dial design, 120Hz curved vision display.', 'realme_12_pro_plus.png'),
(13, 5, 'Vivo T3 5G', 'V2334', '8 GB', '128 GB', 'Crystal Flake', 19999.00, 13, 'MediaTek Dimensity 7200 processor (734K+ AnTuTu score), 50MP Sony IMX882 OIS camera, Dual stereo speakers, 44W FlashCharge.', 'vivo_t3_5g.png'),
(14, 5, 'Vivo V30 5G', 'V2318', '8 GB', '256 GB', 'Andaman Blue', 33999.00, 7, 'Studio-quality Smart Aura Light portrait, 50MP Eye AF selfie camera, Snapdragon 7 Gen 3, ultra-slim 3D curved 1.5K display.', 'vivo_v30.png'),
(15, 6, 'Oppo F27 5G', 'CPH2637', '8 GB', '128 GB', 'Emerald Green', 22999.00, 9, 'Halo Light notifications, AI Studio portrait, MediaTek Dimensity 6300, 300% ultra volume mode, 45W SUPERVOOC flash charge.', 'oppo_f27_5g.png'),
(16, 6, 'Oppo Reno 12 Pro 5G', 'CPH2629', '12 GB', '256 GB', 'Sunset Gold', 36999.00, 4, 'AI Eraser 2.0, Quad-curved infinite view screen, MediaTek Dimensity 7300-Energy, 50MP telephoto portrait camera, 80W SUPERVOOC.', 'oppo_reno_12_pro.png'),
(17, 7, 'Motorola Edge 50 Fusion', 'XT2429-1', '8 GB', '128 GB', 'Marshmallow Blue', 22999.00, 10, 'IP68 underwater protection, 50MP Sony LYT-700C OIS camera, 144Hz 3D curved pOLED display, Snapdragon 7s Gen 2, vegan leather finish.', 'motorola_edge_50_fusion.png'),
(18, 7, 'Motorola G85 5G', 'XT2427-3', '8 GB', '128 GB', 'Cobalt Blue', 17999.00, 12, 'Endless edge 3D curved 120Hz pOLED display, 50MP Sony LYT-600 OIS camera, Snapdragon 6s Gen 3, Dolby Atmos stereo speakers.', 'motorola_g85_5g.png'),
(19, 8, 'iQOO Z9 5G', 'I2302', '8 GB', '128 GB', 'Brushed Green', 19999.00, 8, 'Segment fastest MediaTek Dimensity 7200 5G, 50MP Sony IMX882 with OIS, 120Hz ultra-bright AMOLED display, 44W FlashCharge.', 'iqoo_z9_5g.png'),
(20, 9, 'Nothing Phone (2a)', 'A142', '8 GB', '128 GB', 'Black', 23999.00, 7, 'Iconic Glyph Interface, custom Dimensity 7200 Pro chipset, dual 50MP rear cameras, Nothing OS 2.5 powered by clean Android 14.', 'nothing_phone_2a.png');

-- 4. Insert Customers with Realistic Indian Details
INSERT INTO `customers` (`id`, `user_id`, `name`, `email`, `phone`, `address`) VALUES
(1, 2, 'Aditya Sharma', 'aditya.sharma@example.com', '9820123456', 'B-302, Gokul Heights, Baner, Pune'),
(2, 3, 'Sneha Joshi', 'sneha.joshi@example.com', '9765432109', 'Flat 12, Shivneri Society, Kothrud, Pune'),
(3, 4, 'Rohan Kulkarni', 'rohan.kulkarni@example.com', '9881234567', 'Plot 45, Viman Nagar, Pune'),
(4, 5, 'Neha Patil', 'neha.patil@example.com', '9922334455', 'A-101, Green Acres, Wakad, Pune'),
(5, NULL, 'Akash Jadhav (Walk-in)', 'akash.jadhav@example.com', '9850112233', '142 Sadashiv Peth, Near Tilak Road, Pune');

-- 5. Insert Realistic Sample Sales
INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `sale_date`, `total_amount`, `payment_method`, `status`, `notes`) VALUES
(1, 'INV-2026-0001', 1, '2026-09-10 11:30:00', 24999.00, 'UPI on Delivery', 'Completed', 'Online customer order for OnePlus Nord CE 4.'),
(2, 'INV-2026-0002', 2, '2026-09-14 15:45:00', 27999.00, 'Card on Delivery', 'Completed', 'Purchased Samsung Galaxy A35 5G.'),
(3, 'INV-2026-0003', 3, '2026-09-17 17:10:00', 23999.00, 'Cash on Delivery', 'Completed', 'Nothing Phone (2a) online order.'),
(4, 'INV-2026-0004', 5, '2026-09-18 18:25:00', 22999.00, 'UPI', 'Completed', 'In-store walk-in sale for Moto Edge 50 Fusion.');

-- 6. Insert Sale Items
INSERT INTO `sale_items` (`id`, `sale_id`, `mobile_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 4, 1, 24999.00, 24999.00),
(2, 2, 2, 1, 27999.00, 27999.00),
(3, 3, 20, 1, 23999.00, 23999.00),
(4, 4, 17, 1, 22999.00, 22999.00);
