# Mobile Shop Management System

A clean, responsive, and easy-to-demonstrate **Mobile Shop Management System** built specifically for college mini/major projects and academic viva presentations.

Developed using **PHP, MySQL, HTML5, CSS3, JavaScript, and Bootstrap 5**, designed to run smoothly on **XAMPP** without any external frameworks, Node.js, or Composer dependencies.

---

## 📱 Project Overview

The **Mobile Shop Management System** is a full-stack web application designed for mobile phone retail shops. It provides an intuitive management interface for the shop administrator and an online catalog and ordering portal for customers.

### Key Capabilities:
- **Inventory & Stock Tracking:** Manage phone models, specifications (RAM, Storage, Color), prices, and real-time stock levels with automated low-stock warnings.
- **Brand Management:** Organize phones under recognized manufacturers (Samsung, OnePlus, Xiaomi, Realme, Vivo, Oppo, Motorola, iQOO, Nothing) with referential integrity checks.
- **Sales & Billing:** Process in-store walk-in sales or online orders, verify stock availability, deduct stock atomically, and generate print-ready tax invoices.
- **Customer Portal:** Allows users to register, browse smartphones with brand filters and sorting, inspect detailed specifications, place orders, and access order history and receipts.

---

## 🛠️ Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla), Bootstrap 5.3 (via CDN), Bootstrap Icons
- **Backend:** PHP 8.x (Procedural + Reusable Functions, PDO for MySQL)
- **Database:** MySQL 8.x / MariaDB (InnoDB engine with Foreign Keys)
- **Environment:** XAMPP (Apache + MySQL + phpMyAdmin)
- **Design:** Modern, clean, college-appropriate UI without excessive animations or unnecessary abstractions.

---

## 👥 User Roles & Features

### 1. Administrator (`admin`)
- **Secure Authentication:** Password hashing using `password_hash()` (Bcrypt).
- **Dashboard Overview:**
  - Real-time KPI cards: Total Mobiles, In-Stock Units, Total Revenue, Registered Customers, Active Brands.
  - Low stock warning banner (&le; 5 units) with quick restock action.
  - Recent sales transactions with direct invoice links.
- **Brand Management (CRUD):**
  - View all brands with associated phone count.
  - Add new brand with validation and uniqueness check.
  - Edit existing brand details.
  - Safe deletion: blocks deletion if mobiles are currently assigned to the brand.
- **Mobile Inventory Management (CRUD):**
  - View mobile phones with thumbnail, model code, specs, price, and color-coded stock badges.
  - Search by phone name, model, or color.
  - Filter by brand and stock level (In stock, Low stock, Out of stock).
  - Add/Edit mobile phones with specification dropdowns and safe image upload validation (JPG, JPEG, PNG, WEBP &le; 2MB).
  - Safe deletion: prevents deletion of phones with existing sales history.
- **Customer Management:**
  - View registered customer records, contact info, total orders placed, and total spend.
- **Sales & Invoices:**
  - Direct offline sales creation (select existing customer or enter walk-in details).
  - Real-time stock verification: prevents negative inventory.
  - Server-side recalculation of subtotal and grand total.
  - Printable tax invoice with shop details, customer info, itemized specs, and `window.print()` trigger.

### 2. Customer (`customer`)
- **Registration & Login:** Register with name, email, phone, delivery address, and password.
- **Storefront / Catalog:**
  - Search phones by keyword.
  - Filter by brand.
  - Sort by Newest, Price (Low to High), Price (High to Low), or Name (A to Z).
  - Real-time stock indicators ("In Stock", "Only X Left", "Out of Stock").
- **Product Details:** High-resolution image view, full hardware specifications table, and description.
- **Simple Order Flow:**
  - Select quantity (bounded by available stock).
  - Pre-filled delivery address (editable).
  - Choose payment mode: Cash on Delivery (COD), UPI / QR Code on Delivery, or Card on Delivery.
  - Atomic transaction: deducts stock and immediately generates customer invoice.
- **Dashboard & Order History:**
  - View personal order statistics (total orders, total amount spent).
  - Re-print or view invoices for all previous purchases.

---

## 🔑 Default Credentials (Demo / Viva)

| Role | Name | Email | Password | Access Level |
| :--- | :--- | :--- | :--- | :--- |
| **Administrator** | Rahul Patil | `admin@mobileshop.com` | `admin123` | Full Admin Panel (`/admin/dashboard.php`) |
| **Customer 1** | Aditya Sharma | `aditya.sharma@example.com` | `aditya123` | Customer Portal (`/customer/dashboard.php`) |
| **Customer 2** | Sneha Joshi | `sneha.joshi@example.com` | `sneha123` | Customer Portal (`/customer/dashboard.php`) |
| **Customer 3** | Rohan Kulkarni | `rohan.kulkarni@example.com` | `rohan123` | Customer Portal (`/customer/dashboard.php`) |

> You can also register a new customer account anytime using the **Register** link on the navbar!

---

## ⚙️ Installation & Setup on XAMPP (Windows)

### Step 1: Copy Project to XAMPP `htdocs`
Copy the `mobile-shop-management-system` folder into your XAMPP web root:
```
C:\xampp\htdocs\mobile-shop-management-system
```

### Step 2: Start Apache & MySQL
1. Open the **XAMPP Control Panel**.
2. Click **Start** for both **Apache** and **MySQL**.
3. Confirm both modules show green status.

### Step 3: Import the Database
1. Open your web browser and navigate to:
   ```
   http://localhost/phpmyadmin
   ```
2. Click on the **Import** tab at the top.
3. Click **Choose File** and select the file:
   ```
   C:\xampp\htdocs\mobile-shop-management-system\database\database.sql
   ```
4. Click **Import** (or **Go**) at the bottom.
5. The database `mobile_shop_management` will be automatically created with all tables and sample records.

### Step 4: Open the Application
In your browser, visit:
```
http://localhost/mobile-shop-management-system/
```

---

## 📁 Project Structure

```
mobile-shop-management-system/
│
├── admin/                     # Administrator Pages
│   ├── dashboard.php          # Admin KPI dashboard & recent sales
│   ├── brands.php             # Brands list & model counts
│   ├── add_brand.php          # Form to add manufacturer
│   ├── edit_brand.php         # Form to update manufacturer
│   ├── delete_brand.php       # Safe deletion handler
│   ├── mobiles.php            # Inventory list with search/filter
│   ├── add_mobile.php         # Add mobile phone with image upload
│   ├── edit_mobile.php        # Edit mobile specifications & stock
│   ├── delete_mobile.php      # Safe mobile deletion handler
│   ├── view_mobile.php        # Detailed specs card
│   ├── customers.php          # Registered customer list & stats
│   ├── sales.php              # All sales transactions
│   ├── create_sale.php        # Direct in-store sale billing
│   └── invoice.php            # Print-friendly tax receipt
│
├── customer/                  # Customer Pages
│   ├── dashboard.php          # Customer overview & stats
│   ├── mobiles.php            # Public smartphone catalog
│   ├── mobile_details.php     # Product specifications page
│   ├── order.php              # Checkout / order confirmation
│   └── purchases.php          # Purchase history & invoices
│
├── auth/                      # Authentication
│   ├── login.php              # Login page with demo credentials
│   ├── register.php           # Customer registration
│   └── logout.php             # Session destruction & redirect
│
├── config/                    # Configurations
│   ├── config.php             # Base URL detection, currency, helpers
│   └── database.php           # PDO MySQL connection & error handling
│
├── includes/                  # Reusable Components
│   ├── header.php             # HTML head & Bootstrap CSS
│   ├── navbar.php             # Top navigation bar
│   ├── sidebar.php            # Admin sidebar navigation
│   ├── auth.php               # Session & role-based access helpers
│   └── footer.php             # HTML footer & Bootstrap JS
│
├── assets/                    # Static Assets
│   ├── css/style.css          # Custom styling & print invoice CSS
│   ├── js/script.js           # Client-side filters & calculators
│   └── images/                # Sample mobile images & uploads
│
├── database/
│   └── database.sql           # Database schema & sample data
│
├── tests/
│   ├── test_system.php        # Core system test suite (9 automated tests)
│   ├── test_audit_suite.php   # Full-spectrum audit suite (15 verification tests)
│   ├── test_http_endpoints.php# Live HTTP endpoint response verification
│   ├── test_http_session_workflow.php # Live HTTP session, RBAC & IDOR audit
│   └── test_e2e_workflow.php  # End-to-end transaction & inventory rollback test
│
├── index.php                  # Storefront Homepage
└── README.md                  # Project documentation
```

---

## 🔒 Security & Code Quality Measures

1. **Prepared SQL Statements:** All database queries involving user input use PDO prepared statements to protect against SQL Injection.
2. **Password Security:** Passwords are encrypted using PHP's native `password_hash()` with Bcrypt algorithm. Plaintext passwords are never stored.
3. **Cross-Site Scripting (XSS) Prevention:** Output is escaped using `htmlspecialchars()` before rendering in HTML.
4. **Role-Based Access Control (RBAC):** Direct page requests to admin endpoints (`admin/*.php`) verify that the user is logged in with `role === 'admin'`. Unauthenticated users or customers are redirected.
5. **Session Fixation Defense:** Session identifiers are regenerated upon login with `session_regenerate_id(true)`.
6. **Server-Side Calculations:** Sales amounts and sub-totals are computed strictly on the backend to avoid client-side tampering.
7. **Atomic Stock Updates:** Sales operations use database transactions (`beginTransaction()`, `commit()`, `rollBack()`) and lock rows (`FOR UPDATE`) to ensure stock never drops below zero.
8. **Safe Image Uploads:** Validates file MIME types, file extensions, and file sizes (&le; 2MB) with uniquely randomized filenames.

---

## 🎓 Viva Questions & Answers (Exam Preparation)

**Q1: What database engine is used and why?**
> **Answer:** MySQL with the **InnoDB** storage engine is used because it supports ACID-compliant transactions, foreign key constraints (`ON DELETE RESTRICT` / `CASCADE`), and row-level locking for inventory management.

**Q2: How is stock prevented from becoming negative?**
> **Answer:** During the sale/order process, the application checks if `stock >= quantity` inside a database transaction. If stock is insufficient, an exception is thrown, the transaction rolls back, and an error message is displayed to the user.

**Q3: How are passwords stored securely?**
> **Answer:** Passwords are never stored in plaintext. They are hashed using `password_hash($password, PASSWORD_DEFAULT)`, which uses the Bcrypt hashing algorithm with automatic salt generation. Authentication is validated using `password_verify()`.

**Q4: How does the invoice print feature work without external libraries?**
> **Answer:** Standard CSS `@media print` rules hide non-printable elements (navbar, buttons, sidebar) and format the invoice container cleanly for any standard A4 printer or "Save as PDF" browser dialog.

---

## 📄 License
This project is open-source and intended for academic, college demonstration, and learning purposes.
