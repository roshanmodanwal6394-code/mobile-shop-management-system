<?php
/**
 * Admin Dashboard
 * Mobile Shop Management System
 */
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Enforce admin access
requireAdmin();

$db = getDB();

// Fetch summary metrics
$totalMobiles = (int)$db->query("SELECT COUNT(*) FROM `mobiles`")->fetchColumn();
$totalStock = (int)$db->query("SELECT COALESCE(SUM(`stock`), 0) FROM `mobiles`")->fetchColumn();
$totalBrands = (int)$db->query("SELECT COUNT(*) FROM `brands`")->fetchColumn();
$totalCustomers = (int)$db->query("SELECT COUNT(*) FROM `customers`")->fetchColumn();
$totalSalesCount = (int)$db->query("SELECT COUNT(*) FROM `sales`")->fetchColumn();
$totalRevenue = (float)$db->query("SELECT COALESCE(SUM(`total_amount`), 0) FROM `sales` WHERE `status` = 'Completed'")->fetchColumn();
$lowStockCount = (int)$db->query("SELECT COUNT(*) FROM `mobiles` WHERE `stock` <= 5")->fetchColumn();

// Fetch Low Stock Mobiles (stock <= 5)
$lowStockStmt = $db->query("
    SELECT m.*, b.name AS brand_name 
    FROM `mobiles` m 
    JOIN `brands` b ON m.brand_id = b.id 
    WHERE m.stock <= 5 
    ORDER BY m.stock ASC 
    LIMIT 5
");
$lowStockMobiles = $lowStockStmt->fetchAll();

// Fetch Recent Sales
$recentSalesStmt = $db->query("
    SELECT s.*, c.name AS customer_name, c.phone AS customer_phone,
           (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count
    FROM `sales` s 
    JOIN `customers` c ON s.customer_id = c.id 
    ORDER BY s.sale_date DESC 
    LIMIT 5
");
$recentSales = $recentSalesStmt->fetchAll();

$flashLogin = getFlash('login_success');
$flashGeneral = getFlash('general_msg');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Admin Sidebar -->
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Header & Welcome -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
                <div>
                    <h1 class="h3 fw-bold mb-1">Dashboard Overview</h1>
                    <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars(currentUser()['name']) ?>. Here is your store summary.</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0 gap-2">
                    <a href="<?= url('admin/add_mobile.php') ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Add Mobile
                    </a>
                    <a href="<?= url('admin/create_sale.php') ?>" class="btn btn-success btn-sm">
                        <i class="bi bi-cart-plus me-1"></i> New Sale
                    </a>
                </div>
            </div>

            <!-- Flash Alerts -->
            <?php if ($flashLogin): ?>
                <div class="alert alert-<?= $flashLogin['type'] ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($flashLogin['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($flashGeneral): ?>
                <div class="alert alert-<?= $flashGeneral['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($flashGeneral['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Metric Cards Row -->
            <div class="row g-3 mb-4">
                <!-- Total Mobiles -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card shadow-sm border-0 stat-card primary h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-semibold small">Total Mobiles</span>
                                    <h3 class="fw-bold my-1"><?= number_format($totalMobiles) ?></h3>
                                    <small class="text-muted"><?= number_format($totalStock) ?> units in stock</small>
                                </div>
                                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                    <i class="bi bi-phone fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card shadow-sm border-0 stat-card success h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-semibold small">Total Revenue</span>
                                    <h3 class="fw-bold my-1 text-success"><?= formatPrice($totalRevenue) ?></h3>
                                    <small class="text-muted"><?= number_format($totalSalesCount) ?> completed sales</small>
                                </div>
                                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                                    <i class="bi bi-currency-rupee fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Customers -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card shadow-sm border-0 stat-card info h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-semibold small">Customers</span>
                                    <h3 class="fw-bold my-1"><?= number_format($totalCustomers) ?></h3>
                                    <small class="text-muted"><?= number_format($totalBrands) ?> brands active</small>
                                </div>
                                <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                                    <i class="bi bi-people fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card shadow-sm border-0 stat-card <?= ($lowStockCount > 0) ? 'danger' : 'warning' ?> h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted text-uppercase fw-semibold small">Low Stock Alert</span>
                                    <h3 class="fw-bold my-1 <?= ($lowStockCount > 0) ? 'text-danger' : 'text-warning' ?>">
                                        <?= number_format($lowStockCount) ?>
                                    </h3>
                                    <small class="text-muted">&le; 5 units remaining</small>
                                </div>
                                <div class="<?= ($lowStockCount > 0) ? 'bg-danger text-danger' : 'bg-warning text-warning' ?> bg-opacity-10 p-3 rounded-circle">
                                    <i class="bi bi-exclamation-triangle fs-3"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Row: Low Stock Alert & Recent Sales -->
            <div class="row g-4">
                <!-- Low Stock Products List -->
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-danger">
                                <i class="bi bi-exclamation-octagon me-2"></i> Low Stock Mobiles (&le; 5)
                            </h6>
                            <a href="<?= url('admin/mobiles.php?stock_status=low_stock') ?>" class="text-decoration-none small fw-semibold">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($lowStockMobiles)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-check-circle-fill text-success fs-1 mb-2 d-block"></i>
                                    All mobile phones have healthy stock levels!
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Mobile</th>
                                                <th>Brand</th>
                                                <th>Stock</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($lowStockMobiles as $item): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold"><?= htmlspecialchars($item['name']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($item['model']) ?></small>
                                                    </td>
                                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($item['brand_name']) ?></span></td>
                                                    <td>
                                                        <span class="badge <?= ($item['stock'] == 0) ? 'bg-danger' : 'bg-warning text-dark' ?>">
                                                            <?= $item['stock'] ?> left
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="<?= url('admin/edit_mobile.php?id=' . $item['id']) ?>" class="btn btn-outline-primary btn-sm py-0 px-2" title="Restock">
                                                            Restock
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Sales List -->
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-receipt me-2 text-primary"></i> Recent Sales
                            </h6>
                            <a href="<?= url('admin/sales.php') ?>" class="text-decoration-none small fw-semibold">View All Sales</a>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($recentSales)): ?>
                                <div class="p-4 text-center text-muted">
                                    <i class="bi bi-receipt fs-1 mb-2 d-block"></i>
                                    No sales records found.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Invoice</th>
                                                <th>Customer</th>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentSales as $sale): ?>
                                                <tr>
                                                    <td>
                                                        <span class="fw-semibold text-primary"><?= htmlspecialchars($sale['invoice_no']) ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="fw-semibold"><?= htmlspecialchars($sale['customer_name']) ?></div>
                                                        <small class="text-muted"><?= htmlspecialchars($sale['customer_phone']) ?></small>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?= date('d M Y, h:i A', strtotime($sale['sale_date'])) ?></small>
                                                    </td>
                                                    <td class="fw-bold text-success">
                                                        <?= formatPrice($sale['total_amount']) ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success"><?= htmlspecialchars($sale['status']) ?></span>
                                                    </td>
                                                    <td class="text-end">
                                                        <a href="<?= url('admin/invoice.php?id=' . $sale['id']) ?>" class="btn btn-sm btn-outline-secondary py-0 px-2" title="View Invoice">
                                                            <i class="bi bi-file-earmark-text"></i> Invoice
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
