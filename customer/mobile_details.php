<?php
/**
 * Customer Mobile Details Page
 * Mobile Shop Management System
 */
$pageTitle = 'Mobile Details';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    redirect(url('customer/mobiles.php'));
}

$stmt = $db->prepare("
    SELECT m.*, b.name AS brand_name 
    FROM `mobiles` m 
    JOIN `brands` b ON m.brand_id = b.id 
    WHERE m.id = ? 
    LIMIT 1
");
$stmt->execute([$id]);
$mobile = $stmt->fetch();

if (!$mobile) {
    setFlash('general_msg', 'Mobile phone not found.', 'danger');
    redirect(url('customer/mobiles.php'));
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5">
    <!-- Breadcrumb & Back -->
    <div class="mb-4">
        <a href="<?= url('customer/mobiles.php') ?>" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Back to All Mobiles
        </a>
    </div>

    <div class="row g-5">
        <!-- Mobile Image -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0 p-4 text-center bg-white rounded-3">
                <div class="product-img-container mb-3" style="height: 320px;">
                    <img src="<?= asset('images/' . ($mobile['image'] ?: 'default_mobile.png')) ?>" alt="<?= htmlspecialchars($mobile['name']) ?>" class="product-img" onerror="this.onerror=null; this.src='<?= asset('images/default_mobile.png') ?>';">
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <span class="badge bg-secondary"><?= htmlspecialchars($mobile['brand_name']) ?></span>
                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($mobile['color']) ?></span>
                </div>
            </div>
        </div>

        <!-- Mobile Information & Purchase Box -->
        <div class="col-md-7">
            <span class="badge bg-primary-subtle text-primary mb-2 text-uppercase fw-semibold">Smartphone</span>
            <h2 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($mobile['name']) ?></h2>
            <p class="text-muted mb-3">Model: <strong><?= htmlspecialchars($mobile['model']) ?></strong></p>

            <!-- Price & Stock Status -->
            <div class="p-3 bg-light rounded-3 border mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">Special Offer Price</span>
                        <h2 class="fw-bold text-success mb-0"><?= formatPrice($mobile['price']) ?></h2>
                    </div>
                    <div>
                        <?php if ($mobile['stock'] == 0): ?>
                            <span class="badge bg-danger fs-6 px-3 py-2">Out of Stock</span>
                        <?php elseif ($mobile['stock'] <= 5): ?>
                            <span class="badge bg-warning text-dark fs-6 px-3 py-2">Only <?= $mobile['stock'] ?> Left in Stock</span>
                        <?php else: ?>
                            <span class="badge bg-success fs-6 px-3 py-2">In Stock (<?= $mobile['stock'] ?> Available)</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Order / Buy Button -->
            <div class="mb-4">
                <?php if ($mobile['stock'] > 0): ?>
                    <a href="<?= url('customer/order.php?id=' . $mobile['id']) ?>" class="btn btn-warning btn-lg w-100 py-3 fw-bold shadow-sm">
                        <i class="bi bi-cart-check me-2"></i> Buy Now / Place Order
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg w-100 py-3" disabled>
                        <i class="bi bi-x-circle me-2"></i> Currently Unavailable (Out of Stock)
                    </button>
                <?php endif; ?>
            </div>

            <!-- Key Specs Table -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Hardware Specifications</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th class="bg-light w-35">Brand</th>
                                <td><?= htmlspecialchars($mobile['brand_name']) ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Model Name</th>
                                <td><?= htmlspecialchars($mobile['name']) ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Model Number</th>
                                <td><?= htmlspecialchars($mobile['model']) ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">RAM</th>
                                <td><?= htmlspecialchars($mobile['ram']) ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Internal Storage</th>
                                <td><?= htmlspecialchars($mobile['storage']) ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Color</th>
                                <td><?= htmlspecialchars($mobile['color']) ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Warranty</th>
                                <td>1 Year Brand Warranty</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Product Description -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Product Overview</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 text-muted" style="line-height: 1.8;">
                        <?= nl2br(htmlspecialchars($mobile['description'] ?: 'High performance smartphone with advanced camera, fast charging, and vibrant display.')) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
