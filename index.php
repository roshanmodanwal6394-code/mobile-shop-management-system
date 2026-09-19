<?php
/**
 * Homepage
 * Mobile Shop Management System
 */
$pageTitle = 'Home';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$db = getDB();

// Fetch brands for brand bar
$brands = $db->query("SELECT * FROM `brands` ORDER BY `name` ASC")->fetchAll();

// Fetch featured mobiles (latest 6 mobiles)
$featuredMobiles = $db->query("
    SELECT m.*, b.name AS brand_name 
    FROM `mobiles` m 
    JOIN `brands` b ON m.brand_id = b.id 
    ORDER BY m.id DESC 
    LIMIT 6
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- Hero Banner Section -->
<div class="container py-4">
    <div class="hero-banner shadow-sm mb-5">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark px-3 py-2 text-uppercase fw-bold mb-3">
                    <i class="bi bi-stars me-1"></i> Authorized Smartphone Retailer
                </span>
                <h1 class="display-5 fw-bold mb-3">Explore Latest Smartphones at Best Prices</h1>
                <p class="lead text-light text-opacity-75 mb-4">
                    Welcome to <?= SITE_NAME ?> — your premier destination for genuine Android smartphones with manufacturer warranty, easy ordering, and transparent inventory management.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= url('customer/mobiles.php') ?>" class="btn btn-warning btn-lg px-4 fw-bold">
                        <i class="bi bi-grid me-1"></i> Browse Mobiles
                    </a>
                    <?php if (!isLoggedIn()): ?>
                        <a href="<?= url('auth/register.php') ?>" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                            <i class="bi bi-person-plus me-1"></i> Create Account
                        </a>
                    <?php elseif (isCustomer()): ?>
                        <a href="<?= url('customer/purchases.php') ?>" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                            <i class="bi bi-bag-check me-1"></i> My Orders
                        </a>
                    <?php else: ?>
                        <a href="<?= url('admin/dashboard.php') ?>" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                            <i class="bi bi-speedometer2 me-1"></i> Admin Panel
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 text-center mt-4 mt-lg-0">
                <div class="p-4 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-25">
                    <i class="bi bi-phone-vibrate text-warning" style="font-size: 6rem;"></i>
                    <h5 class="fw-bold mt-2">Genuine Smartphones</h5>
                    <p class="small text-light text-opacity-75 mb-0">Samsung &bull; OnePlus &bull; Xiaomi &bull; Realme &bull; Vivo &bull; Motorola &bull; Oppo &bull; iQOO</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop By Brand Row -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0"><i class="bi bi-tags me-2 text-primary"></i> Top Brands</h4>
            <a href="<?= url('customer/mobiles.php') ?>" class="text-decoration-none fw-semibold small">View All Mobiles &rarr;</a>
        </div>
        <div class="row g-2">
            <?php foreach ($brands as $brand): ?>
                <div class="col-6 col-sm-4 col-md-2">
                    <a href="<?= url('customer/mobiles.php?brand_id=' . $brand['id']) ?>" class="card text-center text-decoration-none shadow-sm border-0 py-3 px-2 h-100 bg-white hover-shadow transition">
                        <div class="fw-bold text-dark mb-1"><?= htmlspecialchars($brand['name']) ?></div>
                        <small class="text-muted" style="font-size: 0.75rem;">View Models</small>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Featured Mobiles Grid -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-fire me-2 text-danger"></i> Featured Mobiles</h4>
                <p class="text-muted small mb-0">Discover our most popular smartphones in stock</p>
            </div>
            <a href="<?= url('customer/mobiles.php') ?>" class="btn btn-outline-primary btn-sm">
                View Full Catalog <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredMobiles as $mobile): ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 product-card rounded-3">
                        <!-- Product Image Container -->
                        <div class="product-img-container position-relative">
                            <img src="<?= asset('images/' . ($mobile['image'] ?: 'default_mobile.png')) ?>" alt="<?= htmlspecialchars($mobile['name']) ?>" class="product-img">
                            <span class="position-absolute top-0 start-0 m-3 badge bg-secondary">
                                <?= htmlspecialchars($mobile['brand_name']) ?>
                            </span>
                            <?php if ($mobile['stock'] == 0): ?>
                                <span class="position-absolute top-0 end-0 m-3 badge bg-danger">Out of Stock</span>
                            <?php elseif ($mobile['stock'] <= 5): ?>
                                <span class="position-absolute top-0 end-0 m-3 badge bg-warning text-dark">Only <?= $mobile['stock'] ?> Left</span>
                            <?php else: ?>
                                <span class="position-absolute top-0 end-0 m-3 badge bg-success">In Stock</span>
                            <?php endif; ?>
                        </div>

                        <!-- Card Details -->
                        <div class="card-body p-4 d-flex flex-column">
                            <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($mobile['name']) ?></h5>
                            <small class="text-muted mb-2"><?= htmlspecialchars($mobile['model']) ?> &bull; <?= htmlspecialchars($mobile['color']) ?></small>

                            <!-- Specs Badges -->
                            <div class="d-flex gap-2 my-2">
                                <span class="badge bg-light text-dark border"><i class="bi bi-cpu me-1"></i> <?= htmlspecialchars($mobile['ram']) ?></span>
                                <span class="badge bg-light text-dark border"><i class="bi bi-device-hdd me-1"></i> <?= htmlspecialchars($mobile['storage']) ?></span>
                            </div>

                            <p class="text-muted small my-2 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= htmlspecialchars($mobile['description'] ?: 'High performance smartphone with premium design.') ?>
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <div>
                                    <span class="text-muted small d-block">Price:</span>
                                    <span class="fs-5 fw-bold text-success"><?= formatPrice($mobile['price']) ?></span>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="<?= url('customer/mobile_details.php?id=' . $mobile['id']) ?>" class="btn btn-outline-primary btn-sm">
                                        Details
                                    </a>
                                    <?php if ($mobile['stock'] > 0): ?>
                                        <a href="<?= url('customer/order.php?id=' . $mobile['id']) ?>" class="btn btn-warning btn-sm fw-semibold">
                                            Buy Now
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled>Sold Out</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Why Choose Us Features Section -->
    <div class="py-4 border-top">
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="p-3">
                    <div class="bg-primary bg-opacity-10 text-primary d-inline-flex p-3 rounded-circle mb-3">
                        <i class="bi bi-shield-check fs-2"></i>
                    </div>
                    <h6 class="fw-bold">100% Genuine</h6>
                    <p class="text-muted small mb-0">Original brand devices packed with official manufacturer warranty.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3">
                    <div class="bg-success bg-opacity-10 text-success d-inline-flex p-3 rounded-circle mb-3">
                        <i class="bi bi-tag fs-2"></i>
                    </div>
                    <h6 class="fw-bold">Affordable Pricing</h6>
                    <p class="text-muted small mb-0">Transparent pricing with no hidden charges or inflated fees.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3">
                    <div class="bg-warning bg-opacity-10 text-warning d-inline-flex p-3 rounded-circle mb-3">
                        <i class="bi bi-truck fs-2"></i>
                    </div>
                    <h6 class="fw-bold">Fast Delivery</h6>
                    <p class="text-muted small mb-0">Quick dispatch with Cash on Delivery and UPI payment options.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3">
                    <div class="bg-info bg-opacity-10 text-info d-inline-flex p-3 rounded-circle mb-3">
                        <i class="bi bi-headset fs-2"></i>
                    </div>
                    <h6 class="fw-bold">Dedicated Support</h6>
                    <p class="text-muted small mb-0">Expert customer guidance, warranty assistance, and prompt support.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
