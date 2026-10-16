<?php
/**
 * Customer Order / Purchase Flow
 * Mobile Shop Management System
 */
$pageTitle = 'Place Order';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Enforce customer login
requireLogin();

$db = getDB();
$mobileId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($mobileId <= 0) {
    redirect(url('customer/mobiles.php'));
}

// Fetch mobile details
$stmt = $db->prepare("
    SELECT m.*, b.name AS brand_name 
    FROM `mobiles` m 
    JOIN `brands` b ON m.brand_id = b.id 
    WHERE m.id = ? 
    LIMIT 1
");
$stmt->execute([$mobileId]);
$mobile = $stmt->fetch();

if (!$mobile) {
    setFlash('general_msg', 'Mobile phone not found.', 'danger');
    redirect(url('customer/mobiles.php'));
}

if ($mobile['stock'] <= 0) {
    setFlash('general_msg', 'Sorry, this mobile phone is currently out of stock.', 'warning');
    redirect(url('customer/mobile_details.php?id=' . $mobileId));
}

// Fetch customer information
$user = currentUser();
$custStmt = $db->prepare("SELECT * FROM `customers` WHERE `user_id` = ? LIMIT 1");
$custStmt->execute([$user['id']]);
$customer = $custStmt->fetch();

// Pre-fill delivery info
$custName = $customer['name'] ?? ($user['name'] ?? '');
$custPhone = $customer['phone'] ?? ($user['phone'] ?? '');
$custEmail = $customer['email'] ?? ($user['email'] ?? '');
$custAddress = $customer['address'] ?? ($user['address'] ?? '');

$errors = [];
$quantity = 1;
$paymentMethod = 'Cash on Delivery';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = (int)($_POST['quantity'] ?? 1);
    $custAddress = trim($_POST['address'] ?? '');
    $custPhone = trim($_POST['phone'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'Cash on Delivery');

    if ($quantity <= 0) {
        $errors[] = 'Please select at least 1 unit.';
    }

    if (empty($custPhone)) {
        $errors[] = 'Contact phone number is required.';
    }

    if (empty($custAddress)) {
        $errors[] = 'Please provide a complete delivery address.';
    }

    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Lock mobile record to prevent race conditions & re-verify stock
            $checkMobile = $db->prepare("SELECT * FROM `mobiles` WHERE `id` = ? FOR UPDATE");
            $checkMobile->execute([$mobileId]);
            $currentMobile = $checkMobile->fetch();

            if (!$currentMobile || $currentMobile['stock'] < $quantity) {
                throw new Exception("Sorry, only {$currentMobile['stock']} unit(s) are available right now.");
            }

            // Ensure customer record exists in customers table
            if (!$customer) {
                $insertCustomer = $db->prepare("INSERT INTO `customers` (`user_id`, `name`, `email`, `phone`, `address`) VALUES (?, ?, ?, ?, ?)");
                $insertCustomer->execute([$user['id'], $user['name'], $user['email'], $custPhone, $custAddress]);
                $customerId = (int)$db->lastInsertId();
            } else {
                $customerId = (int)$customer['id'];
                // Update phone & address if changed
                $updateCust = $db->prepare("UPDATE `customers` SET `phone` = ?, `address` = ? WHERE `id` = ?");
                $updateCust->execute([$custPhone, $custAddress, $customerId]);
            }

            // Server-side calculation
            $unitPrice = (float)$currentMobile['price'];
            $totalAmount = $unitPrice * $quantity;

            // Generate unique Invoice Number
            $invoiceNo = 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            // 1. Insert into sales
            $saleStmt = $db->prepare("
                INSERT INTO `sales` 
                (`invoice_no`, `customer_id`, `total_amount`, `payment_method`, `status`, `notes`) 
                VALUES (?, ?, ?, ?, 'Completed', 'Customer online order')
            ");
            $saleStmt->execute([$invoiceNo, $customerId, $totalAmount, $paymentMethod]);
            $saleId = (int)$db->lastInsertId();

            // 2. Insert into sale_items
            $itemStmt = $db->prepare("
                INSERT INTO `sale_items` 
                (`sale_id`, `mobile_id`, `quantity`, `unit_price`, `subtotal`) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $itemStmt->execute([$saleId, $mobileId, $quantity, $unitPrice, $totalAmount]);

            // 3. Atomically decrease stock
            $stockStmt = $db->prepare("UPDATE `mobiles` SET `stock` = `stock` - ? WHERE `id` = ?");
            $stockStmt->execute([$quantity, $mobileId]);

            $db->commit();

            setFlash('order_success', "Thank you! Your order has been placed successfully. Invoice #{$invoiceNo}", 'success');
            redirect(url('admin/invoice.php?id=' . $saleId));

        } catch (Exception $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            $errors[] = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5">
    <div class="mb-4">
        <a href="<?= url('customer/mobile_details.php?id=' . $mobile['id']) ?>" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left me-1"></i> Back to Product
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex align-items-center mb-4">
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3">
                    <i class="bi bi-cart-check fs-2"></i>
                </div>
                <div>
                    <h2 class="fw-bold mb-0">Confirm Your Order</h2>
                    <p class="text-muted mb-0">Review product details, select quantity, and specify delivery address</p>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?= url('customer/order.php?id=' . $mobile['id']) ?>" method="POST" id="orderForm">
                <div class="row g-4">
                    <!-- Left Column: Delivery & Payment Details -->
                    <div class="col-md-7">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2 text-primary"></i> Delivery Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Customer Name</label>
                                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($custName) ?>" readonly>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email Address</label>
                                        <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($custEmail) ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fw-semibold">Contact Phone <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($custPhone) ?>" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label fw-semibold">Delivery Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="House/Flat No, Street, Landmark, City, Pincode" required><?= htmlspecialchars($custAddress) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white py-3">
                                <h5 class="fw-bold mb-0"><i class="bi bi-wallet2 me-2 text-primary"></i> Payment Method</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payCOD" value="Cash on Delivery" <?= ($paymentMethod === 'Cash on Delivery') ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="payCOD">
                                        <i class="bi bi-cash-stack text-success me-1"></i> Cash on Delivery (COD)
                                    </label>
                                    <div class="text-muted small ms-4">Pay cash when your phone is delivered to your doorstep.</div>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payUPI" value="UPI on Delivery" <?= ($paymentMethod === 'UPI on Delivery') ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="payUPI">
                                        <i class="bi bi-qr-code-scan text-primary me-1"></i> UPI / QR Code on Delivery
                                    </label>
                                    <div class="text-muted small ms-4">Scan QR code via Google Pay, PhonePe, or Paytm upon delivery.</div>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payCard" value="Card on Delivery" <?= ($paymentMethod === 'Card on Delivery') ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="payCard">
                                        <i class="bi bi-credit-card text-info me-1"></i> Card on Delivery
                                    </label>
                                    <div class="text-muted small ms-4">Swipe your Debit or Credit card on the courier POS machine.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Order Summary -->
                    <div class="col-md-5">
                        <div class="card shadow-sm border-0 sticky-top" style="top: 80px;">
                            <div class="card-header bg-white py-3">
                                <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2 text-primary"></i> Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                                    <img src="<?= asset('images/' . ($mobile['image'] ?: 'default_mobile.png')) ?>" alt="<?= htmlspecialchars($mobile['name']) ?>" class="table-img" style="width: 60px; height: 60px;" onerror="this.onerror=null; this.src='<?= asset('images/default_mobile.png') ?>';">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($mobile['name']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($mobile['brand_name']) ?> &bull; <?= htmlspecialchars($mobile['ram']) ?> / <?= htmlspecialchars($mobile['storage']) ?></small>
                                        <div class="fw-bold text-success mt-1"><?= formatPrice($mobile['price']) ?></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="orderQty" class="form-label fw-semibold">Quantity</label>
                                    <input type="number" class="form-control" id="orderQty" name="quantity" min="1" max="<?= $mobile['stock'] ?>" value="<?= $quantity ?>" required>
                                    <small class="text-muted mt-1 d-block">Available stock: <strong class="text-dark"><?= $mobile['stock'] ?> units</strong></small>
                                </div>

                                <div class="py-2 border-top">
                                    <div class="d-flex justify-content-between text-muted mb-2">
                                        <span>Unit Price:</span>
                                        <span id="summaryUnitPrice"><?= formatPrice($mobile['price']) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted mb-2">
                                        <span>Delivery Charges:</span>
                                        <span class="text-success fw-semibold">FREE</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="fs-5 fw-bold text-dark">Total Amount:</span>
                                        <span class="fs-4 fw-bold text-success" id="summaryTotal"><?= formatPrice($mobile['price'] * $quantity) ?></span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-warning btn-lg w-100 py-3 fw-bold shadow-sm" id="confirmOrderBtn">
                                    <i class="bi bi-check2-circle me-1"></i> Place Order Now
                                </button>
                                <small class="text-muted text-center d-block mt-2">
                                    <i class="bi bi-shield-lock me-1"></i> Safe & Secure Order Placement &bull; 100% Genuine Device
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qtyInput = document.getElementById('orderQty');
    const summaryTotal = document.getElementById('summaryTotal');
    const unitPrice = <?= (float)$mobile['price'] ?>;
    const maxStock = <?= (int)$mobile['stock'] ?>;
    const confirmBtn = document.getElementById('confirmOrderBtn');

    qtyInput.addEventListener('input', function() {
        let qty = parseInt(this.value) || 1;
        if (qty < 1) {
            qty = 1;
            this.value = 1;
        }
        if (qty > maxStock) {
            qty = maxStock;
            this.value = maxStock;
        }

        const total = unitPrice * qty;
        summaryTotal.textContent = '<?= CURRENCY_SYMBOL ?> ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
