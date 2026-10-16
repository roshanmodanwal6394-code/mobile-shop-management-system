<?php
/**
 * Customer Purchase History
 * Mobile Shop Management System
 */
$pageTitle = 'My Purchases';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireCustomer();

$db = getDB();
$user = currentUser();

// Fetch customer ID
$custStmt = $db->prepare("SELECT `id` FROM `customers` WHERE `user_id` = ? LIMIT 1");
$custStmt->execute([$user['id']]);
$customer = $custStmt->fetch();
$customerId = $customer ? (int)$customer['id'] : 0;

$purchases = [];
if ($customerId > 0) {
    $ordersStmt = $db->prepare("
        SELECT s.*, 
               (SELECT GROUP_CONCAT(CONCAT(m.name, ' (x', si.quantity, ')') SEPARATOR ', ')
                FROM `sale_items` si 
                JOIN `mobiles` m ON si.mobile_id = m.id 
                WHERE si.sale_id = s.id) AS items_summary
        FROM `sales` s
        WHERE s.customer_id = ?
        ORDER BY s.sale_date DESC
    ");
    $ordersStmt->execute([$customerId]);
    $purchases = $ordersStmt->fetchAll();
}

$flashAuth = getFlash('auth_error');
$flashMsg = getFlash('general_msg');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5">
    <?php if ($flashAuth): ?>
        <div class="alert alert-<?= $flashAuth['type'] ?> alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-shield-exclamation me-2"></i><?= htmlspecialchars($flashAuth['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($flashMsg): ?>
        <div class="alert alert-<?= $flashMsg['type'] ?> alert-dismissible fade show mb-4" role="alert">
            <?= htmlspecialchars($flashMsg['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between flex-wrap align-items-center mb-4 pb-3 border-bottom">
        <div>
            <h2 class="fw-bold mb-1">My Purchase History</h2>
            <p class="text-muted mb-0">Review all your previous smartphone orders and print receipts</p>
        </div>
        <div>
            <a href="<?= url('customer/mobiles.php') ?>" class="btn btn-warning btn-sm fw-semibold">
                <i class="bi bi-cart-plus me-1"></i> Shop More
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center justify-content-between g-2">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="tableSearchInput" placeholder="Search orders by invoice or phone name...">
                    </div>
                </div>
                <div class="col-auto text-muted small">
                    Total Orders: <strong class="text-dark"><?= count($purchases) ?></strong>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="filterableTable">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Items Ordered</th>
                            <th>Date & Time</th>
                            <th>Payment Mode</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th class="text-end" style="width: 140px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($purchases)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-bag-x fs-1 d-block mb-2"></i>
                                    No purchase records found. Explore our catalog to place your first order!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($purchases as $order): ?>
                                <tr>
                                    <td>
                                        <a href="<?= url('admin/invoice.php?id=' . $order['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                            <?= htmlspecialchars($order['invoice_no']) ?>
                                        </a>
                                    </td>
                                    <td class="small" style="max-width: 280px;">
                                        <?= htmlspecialchars($order['items_summary']) ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?= date('d M Y, h:i A', strtotime($order['sale_date'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= htmlspecialchars($order['payment_method']) ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-success">
                                        <?= formatPrice($order['total_amount']) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?= htmlspecialchars($order['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= url('admin/invoice.php?id=' . $order['id']) ?>" class="btn btn-outline-secondary btn-sm" title="View & Print Invoice">
                                            <i class="bi bi-file-earmark-text me-1"></i> Invoice
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
