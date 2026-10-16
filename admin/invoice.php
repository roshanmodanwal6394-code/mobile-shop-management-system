<?php
/**
 * Printable Invoice / Receipt
 * Mobile Shop Management System
 */
$pageTitle = 'Sale Invoice';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Must be logged in (either Admin or Customer)
requireLogin();

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlash('sales_msg', 'Invalid invoice ID.', 'danger');
    if (isAdmin()) {
        redirect(url('admin/sales.php'));
    } else {
        redirect(url('customer/purchases.php'));
    }
}

// Fetch sale details with customer information
$saleStmt = $db->prepare("
    SELECT s.*, c.name AS customer_name, c.email AS customer_email, 
           c.phone AS customer_phone, c.address AS customer_address, c.user_id
    FROM `sales` s
    JOIN `customers` c ON s.customer_id = c.id
    WHERE s.id = ?
    LIMIT 1
");
$saleStmt->execute([$id]);
$sale = $saleStmt->fetch();

if (!$sale) {
    setFlash('sales_msg', 'Invoice record not found.', 'danger');
    if (isAdmin()) {
        redirect(url('admin/sales.php'));
    } else {
        redirect(url('customer/purchases.php'));
    }
}

// Authorization check: If not admin, ensure this sale belongs to their customer user ID
if (!isAdmin() && (!isCustomer() || (int)$sale['user_id'] !== (int)currentUser()['id'])) {
    setFlash('auth_error', 'Access denied! You can only view your own invoices.', 'danger');
    redirect(url('customer/purchases.php'));
}

// Fetch sale items
$itemsStmt = $db->prepare("
    SELECT si.*, m.name AS mobile_name, m.model AS mobile_model, 
           m.ram, m.storage, m.color, b.name AS brand_name
    FROM `sale_items` si
    JOIN `mobiles` m ON si.mobile_id = m.id
    JOIN `brands` b ON m.brand_id = b.id
    WHERE si.sale_id = ?
    ORDER BY si.id ASC
");
$itemsStmt->execute([$id]);
$saleItems = $itemsStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4 my-3">
    <!-- Action Bar (Hidden when printing) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <?php if (isAdmin()): ?>
                <a href="<?= url('admin/sales.php') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Sales
                </a>
            <?php else: ?>
                <a href="<?= url('customer/purchases.php') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Orders
                </a>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="bi bi-printer me-1"></i> Print Invoice
            </button>
        </div>
    </div>

    <!-- Printable Invoice Card -->
    <div class="card shadow-sm border invoice-card p-4 p-md-5 bg-white">
        <!-- Store & Invoice Header -->
        <div class="row border-bottom pb-4 mb-4">
            <div class="col-sm-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-phone-fill text-warning fs-2"></i>
                    <h3 class="fw-bold mb-0 text-dark"><?= SITE_NAME ?></h3>
                </div>
                <p class="text-muted small mb-1">Premier Smartphone Retailer & Service Center</p>
                <p class="text-muted small mb-0">Shop 14, Tech Point, FC Road, Pune - 411005</p>
                <p class="text-muted small mb-0">Email: support@mobileshop.com | Tel: +91 98221 14477</p>
            </div>
            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                <h4 class="text-uppercase fw-bold text-primary mb-1">Tax Invoice</h4>
                <div class="text-muted small mb-1">Invoice #: <strong class="text-dark"><?= htmlspecialchars($sale['invoice_no']) ?></strong></div>
                <div class="text-muted small mb-1">Date: <strong class="text-dark"><?= date('d F Y, h:i A', strtotime($sale['sale_date'])) ?></strong></div>
                <div class="text-muted small">Payment Mode: <strong class="text-dark"><?= htmlspecialchars($sale['payment_method']) ?></strong></div>
            </div>
        </div>

        <!-- Billed To / Customer Details -->
        <div class="row mb-4">
            <div class="col-sm-6">
                <h6 class="text-uppercase text-muted fw-bold small mb-2">Billed To (Customer):</h6>
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($sale['customer_name']) ?></h5>
                <div class="text-muted small mb-1"><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($sale['customer_phone']) ?></div>
                <?php if (!empty($sale['customer_email']) && !str_ends_with($sale['customer_email'], '@walkin.local')): ?>
                    <div class="text-muted small mb-1"><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($sale['customer_email']) ?></div>
                <?php endif; ?>
                <?php if (!empty($sale['customer_address'])): ?>
                    <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($sale['customer_address']) ?></div>
                <?php endif; ?>
            </div>
            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                <h6 class="text-uppercase text-muted fw-bold small mb-2">Order Status:</h6>
                <span class="badge bg-success fs-6 px-3 py-2"><?= htmlspecialchars($sale['status']) ?></span>
                <?php if (!empty($sale['notes'])): ?>
                    <div class="text-muted small mt-2"><strong>Notes:</strong> <?= htmlspecialchars($sale['notes']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>Item Description</th>
                        <th class="text-center">Specs (RAM/ROM)</th>
                        <th class="text-end" style="width: 140px;">Unit Price</th>
                        <th class="text-center" style="width: 90px;">Qty</th>
                        <th class="text-end" style="width: 150px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; foreach ($saleItems as $item): ?>
                        <tr>
                            <td class="text-center text-muted"><?= $count++ ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($item['mobile_name']) ?></div>
                                <small class="text-muted">
                                    Brand: <?= htmlspecialchars($item['brand_name']) ?> &bull; Model: <?= htmlspecialchars($item['mobile_model']) ?> &bull; Color: <?= htmlspecialchars($item['color']) ?>
                                </small>
                            </td>
                            <td class="text-center small">
                                <?= htmlspecialchars($item['ram']) ?> / <?= htmlspecialchars($item['storage']) ?>
                            </td>
                            <td class="text-end"><?= formatPrice($item['unit_price']) ?></td>
                            <td class="text-center fw-semibold"><?= $item['quantity'] ?></td>
                            <td class="text-end fw-bold text-dark"><?= formatPrice($item['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end bg-light">Subtotal:</th>
                        <th class="text-end bg-light"><?= formatPrice($sale['total_amount']) ?></th>
                    </tr>
                    <tr>
                        <th colspan="5" class="text-end bg-light">GST / Taxes (Inclusive):</th>
                        <th class="text-end bg-light"><?= CURRENCY_SYMBOL ?> 0.00</th>
                    </tr>
                    <tr>
                        <th colspan="5" class="text-end bg-light fs-5 text-dark">Grand Total:</th>
                        <th class="text-end bg-light fs-5 text-success fw-bold"><?= formatPrice($sale['total_amount']) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Terms & Stamp -->
        <div class="row pt-3 border-top mt-4">
            <div class="col-sm-8 text-muted small">
                <h6 class="fw-bold text-dark mb-1">Terms & Conditions:</h6>
                <ul class="ps-3 mb-0" style="line-height: 1.5;">
                    <li>1 Year Official Manufacturer Warranty applicable from invoice date.</li>
                    <li>Items once sold cannot be returned without original box and serial matching.</li>
                    <li>For warranty claims, please present this invoice at authorized service centers.</li>
                </ul>
            </div>
            <div class="col-sm-4 text-sm-end text-muted small mt-4 mt-sm-0">
                <div style="height: 50px;"></div>
                <p class="mb-0 border-top pt-1 d-inline-block text-center" style="min-width: 150px;">
                    <strong>Authorized Signatory</strong><br>
                    <?= SITE_NAME ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
