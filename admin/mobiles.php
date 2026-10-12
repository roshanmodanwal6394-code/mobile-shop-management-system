<?php
/**
 * Mobile Phones Inventory Management
 * Mobile Shop Management System
 */
$pageTitle = 'Manage Mobile Phones';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$db = getDB();

// Fetch all brands for the filter dropdown
$brands = $db->query("SELECT * FROM `brands` ORDER BY `name` ASC")->fetchAll();

// Handle search and filtering parameters
$search = trim($_GET['search'] ?? '');
$brandFilter = isset($_GET['brand_id']) && is_numeric($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;
$stockFilter = trim($_GET['stock_status'] ?? '');

$whereConditions = [];
$params = [];

if (!empty($search)) {
    $whereConditions[] = "(m.name LIKE ? OR m.model LIKE ? OR m.color LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($brandFilter > 0) {
    $whereConditions[] = "m.brand_id = ?";
    $params[] = $brandFilter;
}

if ($stockFilter === 'in_stock') {
    $whereConditions[] = "m.stock > 5";
} elseif ($stockFilter === 'low_stock') {
    $whereConditions[] = "m.stock > 0 AND m.stock <= 5";
} elseif ($stockFilter === 'out_of_stock') {
    $whereConditions[] = "m.stock = 0";
}

$whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

$query = "
    SELECT m.*, b.name AS brand_name 
    FROM `mobiles` m 
    JOIN `brands` b ON m.brand_id = b.id 
    {$whereSql}
    ORDER BY m.id DESC
";

$stmt = $db->prepare($query);
$stmt->execute($params);
$mobiles = $stmt->fetchAll();

$flashMsg = getFlash('mobile_msg');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
                <div>
                    <h1 class="h3 fw-bold mb-1">Mobile Phones Inventory</h1>
                    <p class="text-muted mb-0">Manage smartphone catalog, specifications, and stock quantities</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?= url('admin/add_mobile.php') ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add New Mobile
                    </a>
                </div>
            </div>

            <?php if ($flashMsg): ?>
                <div class="alert alert-<?= $flashMsg['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($flashMsg['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Filter Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-3">
                    <form action="<?= url('admin/mobiles.php') ?>" method="GET" class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search name, model, color...">
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

                        <div class="col-md-3">
                            <select class="form-select form-select-sm" name="stock_status">
                                <option value="">All Stock Levels</option>
                                <option value="in_stock" <?= ($stockFilter === 'in_stock') ? 'selected' : '' ?>>In Stock (>5 units)</option>
                                <option value="low_stock" <?= ($stockFilter === 'low_stock') ? 'selected' : '' ?>>Low Stock (&le;5 units)</option>
                                <option value="out_of_stock" <?= ($stockFilter === 'out_of_stock') ? 'selected' : '' ?>>Out of Stock (0 units)</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <?php if (!empty($search) || $brandFilter > 0 || !empty($stockFilter)): ?>
                                <a href="<?= url('admin/mobiles.php') ?>" class="btn btn-sm btn-light border" title="Reset Filters">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Mobiles Table Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-phone me-2 text-primary"></i> Mobile Catalog (<?= count($mobiles) ?>)
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 70px;">Image</th>
                                    <th>Mobile Name & Model</th>
                                    <th>Brand</th>
                                    <th>Specs (RAM/ROM)</th>
                                    <th>Color</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th class="text-end" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($mobiles)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="bi bi-phone fs-2 d-block mb-1"></i>
                                            No mobile phones found matching your criteria.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($mobiles as $mobile): ?>
                                        <tr>
                                            <td>
                                                <img src="<?= asset('images/' . ($mobile['image'] ?: 'default_mobile.png')) ?>" alt="<?= htmlspecialchars($mobile['name']) ?>" class="table-img">
                                            </td>
                                            <td>
                                                <a href="<?= url('admin/view_mobile.php?id=' . $mobile['id']) ?>" class="fw-bold text-dark text-decoration-none">
                                                    <?= htmlspecialchars($mobile['name']) ?>
                                                </a>
                                                <div class="text-muted small"><?= htmlspecialchars($mobile['model']) ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($mobile['brand_name']) ?></span>
                                            </td>
                                            <td class="small">
                                                <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($mobile['ram']) ?></span>
                                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($mobile['storage']) ?></span>
                                            </td>
                                            <td class="small text-muted"><?= htmlspecialchars($mobile['color']) ?></td>
                                            <td class="fw-bold text-success">
                                                <?= formatPrice($mobile['price']) ?>
                                            </td>
                                            <td>
                                                <?php if ($mobile['stock'] == 0): ?>
                                                    <span class="badge bg-danger">Out of Stock (0)</span>
                                                <?php elseif ($mobile['stock'] <= 5): ?>
                                                    <span class="badge bg-warning text-dark">Low Stock (<?= $mobile['stock'] ?>)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">In Stock (<?= $mobile['stock'] ?>)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= url('admin/view_mobile.php?id=' . $mobile['id']) ?>" class="btn btn-outline-info" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="<?= url('admin/edit_mobile.php?id=' . $mobile['id']) ?>" class="btn btn-outline-primary" title="Edit Mobile">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="<?= url('admin/delete_mobile.php?id=' . $mobile['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete <?= addslashes(htmlspecialchars($mobile['name'])) ?>?');" title="Delete Mobile">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
