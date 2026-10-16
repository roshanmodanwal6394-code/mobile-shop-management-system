<?php
/**
 * Sales Management
 * Mobile Shop Management System
 */
$pageTitle = 'Sales & Invoices';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$db = getDB();

// Fetch all sales with customer and item details
$query = "
    SELECT s.*, c.name AS customer_name, c.phone AS customer_phone,
           (SELECT GROUP_CONCAT(CONCAT(m.name, ' (x', si.quantity, ')') SEPARATOR ', ')
            FROM `sale_items` si 
            JOIN `mobiles` m ON si.mobile_id = m.id 
            WHERE si.sale_id = s.id) AS items_summary
    FROM `sales` s 
    JOIN `customers` c ON s.customer_id = c.id 
    ORDER BY s.sale_date DESC
";
$sales = $db->query($query)->fetchAll();

$flashMsg = getFlash('sales_msg');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
                <div>
                    <h1 class="h3 fw-bold mb-1">Sales & Invoices</h1>
                    <p class="text-muted mb-0">Track all customer orders, store sales, and generate receipts</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?= url('admin/create_sale.php') ?>" class="btn btn-success btn-sm">
                        <i class="bi bi-cart-plus me-1"></i> New Sale
                    </a>
                </div>
            </div>

            <?php if ($flashMsg): ?>
                <div class="alert alert-<?= $flashMsg['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($flashMsg['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center justify-content-between g-2">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="tableSearchInput" placeholder="Search invoice, customer, phone...">
                            </div>
                        </div>
                        <div class="col-auto text-muted small">
                            Total Invoices: <strong class="text-dark"><?= count($sales) ?></strong>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="filterableTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Items Ordered</th>
                                    <th>Date & Time</th>
                                    <th>Payment</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th class="text-end" style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sales)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            No sales recorded yet. Click "New Sale" to record an offline sale.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sales as $sale): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= url('admin/invoice.php?id=' . $sale['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                                    <?= htmlspecialchars($sale['invoice_no']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark"><?= htmlspecialchars($sale['customer_name']) ?></div>
                                                <small class="text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($sale['customer_phone']) ?></small>
                                            </td>
                                            <td class="small" style="max-width: 260px;">
                                                <?= htmlspecialchars($sale['items_summary'] ?: 'N/A') ?>
                                            </td>
                                            <td class="small text-muted">
                                                <?= date('d M Y, h:i A', strtotime($sale['sale_date'])) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($sale['payment_method']) ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold text-success">
                                                <?= formatPrice($sale['total_amount']) ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <?= htmlspecialchars($sale['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?= url('admin/invoice.php?id=' . $sale['id']) ?>" class="btn btn-outline-secondary btn-sm" title="View & Print Invoice">
                                                    <i class="bi bi-receipt me-1"></i> Invoice
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
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
