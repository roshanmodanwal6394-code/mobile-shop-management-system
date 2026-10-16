<?php
/**
 * Customer Mobiles Catalog
 * Mobile Shop Management System
 */
$pageTitle = 'Browse Mobile Phones';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$db = getDB();

// Fetch brands for filtering
$brands = $db->query("SELECT * FROM `brands` ORDER BY `name` ASC")->fetchAll();

// Handle search, brand filter, and sorting
$search = trim($_GET['search'] ?? '');
$brandFilter = isset($_GET['brand_id']) && is_numeric($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;
$sortBy = trim($_GET['sort'] ?? 'newest');

$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(m.name LIKE ? OR m.model LIKE ? OR m.color LIKE ? OR m.description LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($brandFilter > 0) {
    $whereConditions[] = "m.brand_id = ?";
    $params[] = $brandFilter;
}

$whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Sorting logic
$orderSql = "ORDER BY m.id DESC"; // default newest
if ($sortBy === 'price_asc') {
    $orderSql = "ORDER BY m.price ASC";
} elseif ($sortBy === 'price_desc') {
    $orderSql = "ORDER BY m.price DESC";
} elseif ($sortBy === 'name_asc') {
    $orderSql = "ORDER BY m.name ASC";
}

$query = "
    SELECT m.*, b.name AS brand_name 
    FROM `mobiles` m 
    JOIN `brands` b ON m.brand_id = b.id 
    {$whereSql}
    {$orderSql}
";

$stmt = $db->prepare($query);
$stmt->execute($params);
$mobiles = $stmt->fetchAll();

$flashMsg = getFlash('general_msg');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">
    <?php if ($flashMsg): ?>
        <div class="alert alert-<?= $flashMsg['type'] ?> alert-dismissible fade show mb-4" role="alert">
            <?= htmlspecialchars($flashMsg['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="d-flex justify-content-between flex-wrap align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Smartphone Catalog</h2>
            <p class="text-muted mb-0">Browse and order from our wide selection of mobile phones</p>
        </div>
        <div class="text-muted small">
            Showing <strong class="text-dark"><?= count($mobiles) ?></strong> mobile phone(s)
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="<?= url('customer/mobiles.php') ?>" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search phone name, model, color...">
                    </div>
                </div>

                <div class="col-md-3">
                    <select class="form-select form-select-sm" name="brand_id">
                        <option value="0">All Brands</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?= $brand['id'] ?>" <?= ($brandFilter == $brand['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($brand['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="sort">
                        <option value="newest" <?= ($sortBy === 'newest') ? 'selected' : '' ?>>Newest First</option>
                        <option value="price_asc" <?= ($sortBy === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= ($sortBy === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="name_asc" <?= ($sortBy === 'name_asc') ? 'selected' : '' ?>>Name: A to Z</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <?php if (!empty($search) || $brandFilter > 0 || $sortBy !== 'newest'): ?>
                        <a href="<?= url('customer/mobiles.php') ?>" class="btn btn-sm btn-light border" title="Reset Filters">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Mobiles Grid -->
    <div class="row g-4">
        <?php if (empty($mobiles)): ?>
            <div class="col-12 py-5 text-center">
                <i class="bi bi-phone-slash text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3 text-muted">No mobile phones found</h4>
                <p class="text-muted small">Try searching with a different keyword or resetting your brand filter.</p>
                <a href="<?= url('customer/mobiles.php') ?>" class="btn btn-outline-primary btn-sm mt-2">
                    Reset All Filters
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($mobiles as $mobile): ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 product-card rounded-3">
                        <!-- Product Image Container -->
                        <div class="product-img-container position-relative">
                            <img src="<?= asset('images/' . ($mobile['image'] ?: 'default_mobile.png')) ?>" alt="<?= htmlspecialchars($mobile['name']) ?>" class="product-img" onerror="this.onerror=null; this.src='<?= asset('images/default_mobile.png') ?>';">
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
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
