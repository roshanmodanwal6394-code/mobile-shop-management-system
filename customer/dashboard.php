<?php
/**
 * Customer Dashboard
 * Mobile Shop Management System
 */
$pageTitle = 'My Dashboard';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireCustomer();

$db = getDB();
$user = currentUser();

// Fetch customer row
$custStmt = $db->prepare("SELECT * FROM `customers` WHERE `user_id` = ? LIMIT 1");
$custStmt->execute([$user['id']]);
$customer = $custStmt->fetch();

$customerId = $customer ? (int)$customer['id'] : 0;

// Calculate stats
$orderCount = 0;
$totalSpent = 0.00;
$recentOrders = [];

if ($customerId > 0) {
    $statsStmt = $db->prepare("
        SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_amount), 0) AS total_spent 
        FROM `sales` 
        WHERE `customer_id` = ? AND `status` = 'Completed'
    ");
    $statsStmt->execute([$customerId]);
    $stats = $statsStmt->fetch();
    $orderCount = (int)($stats['total_orders'] ?? 0);
    $totalSpent = (float)($stats['total_spent'] ?? 0);

    // Fetch recent 5 orders
    $ordersStmt = $db->prepare("
        SELECT s.*, 
               (SELECT GROUP_CONCAT(CONCAT(m.name, ' (x', si.quantity, ')') SEPARATOR ', ')
                FROM `sale_items` si 
                JOIN `mobiles` m ON si.mobile_id = m.id 
                WHERE si.sale_id = s.id) AS items_summary
        FROM `sales` s
        WHERE s.customer_id = ?
        ORDER BY s.sale_date DESC
        LIMIT 5
    ");
    $ordersStmt->execute([$customerId]);
    $recentOrders = $ordersStmt->fetchAll();
}

$flashLogin = getFlash('login_success');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5">
    <!-- Welcome Header -->
    <div class="d-flex justify-content-between flex-wrap align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h2 class="fw-bold mb-1">Hello, <?= htmlspecialchars($user['name']) ?>!</h2>
            <p class="text-muted mb-0">Manage your orders and view your purchase history</p>
        </div>
        <div>
            <a href="<?= url('customer/mobiles.php') ?>" class="btn btn-warning fw-semibold btn-sm">
                <i class="bi bi-cart-plus me-1"></i> Shop New Mobiles
            </a>
        </div>
    </div>

    <?php if ($flashLogin): ?>
        <div class="alert alert-<?= $flashLogin['type'] ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($flashLogin['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Summary Metrics -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 stat-card primary h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold small">Total Orders</span>
                            <h2 class="fw-bold my-1"><?= $orderCount ?></h2>
                            <small class="text-muted">Completed purchases</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                            <i class="bi bi-bag-check fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 stat-card success h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold small">Total Spent</span>
                            <h2 class="fw-bold my-1 text-success"><?= formatPrice($totalSpent) ?></h2>
                            <small class="text-muted">Lifetime transactions</small>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                            <i class="bi bi-currency-rupee fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 stat-card info h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted text-uppercase fw-semibold small">Contact Details</span>
                            <div class="fw-semibold text-dark mt-1"><?= htmlspecialchars($user['email']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars(($customer['phone'] ?? '') ?: (($user['phone'] ?? '') ?: 'No phone added')) ?></small>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                            <i class="bi bi-person fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i> Recent Purchases</h5>
            <?php if (!empty($recentOrders)): ?>
                <a href="<?= url('customer/purchases.php') ?>" class="text-decoration-none small fw-semibold">View Full History &rarr;</a>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recentOrders)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-cart-x text-muted" style="font-size: 3.5rem;"></i>
                    <h5 class="mt-3 text-muted">You have not placed any orders yet</h5>
                    <p class="text-muted small">Explore our collection and get your dream smartphone today!</p>
                    <a href="<?= url('customer/mobiles.php') ?>" class="btn btn-primary btn-sm">
                        Browse Mobiles
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Items Ordered</th>
                                <th>Date</th>
                                <th>Payment Mode</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>
                                        <span class="fw-semibold text-primary"><?= htmlspecialchars($order['invoice_no']) ?></span>
                                    </td>
                                    <td class="small" style="max-width: 250px;">
                                        <?= htmlspecialchars($order['items_summary']) ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?= date('d M Y, h:i A', strtotime($order['sale_date'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($order['payment_method']) ?></span>
                                    </td>
                                    <td class="fw-bold text-success">
                                        <?= formatPrice($order['total_amount']) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?= htmlspecialchars($order['status']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= url('admin/invoice.php?id=' . $order['id']) ?>" class="btn btn-outline-secondary btn-sm" title="View & Print Invoice">
                                            <i class="bi bi-file-earmark-text me-1"></i> Invoice
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
