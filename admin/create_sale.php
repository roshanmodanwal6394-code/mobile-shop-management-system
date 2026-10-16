<?php
/**
 * Create New Sale (Admin)
 * Mobile Shop Management System
 */
$pageTitle = 'Create New Sale';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$db = getDB();

// Fetch customers
$customers = $db->query("SELECT * FROM `customers` ORDER BY `name` ASC")->fetchAll();

// Fetch available mobiles in stock
$mobiles = $db->query("
    SELECT m.*, b.name AS brand_name 
    FROM `mobiles` m 
    JOIN `brands` b ON m.brand_id = b.id 
    WHERE m.stock > 0 
    ORDER BY b.name ASC, m.name ASC
")->fetchAll();

$errors = [];
$customerType = 'existing'; // 'existing' or 'new'
$customerId = 0;
$newCustName = '';
$newCustPhone = '';
$newCustEmail = '';
$newCustAddress = '';

$selectedMobileId = 0;
$quantity = 1;
$paymentMethod = 'Cash';
$notes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerType = $_POST['customer_type'] ?? 'existing';
    $selectedMobileId = (int)($_POST['mobile_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $paymentMethod = trim($_POST['payment_method'] ?? 'Cash');
    $notes = trim($_POST['notes'] ?? '');

    // 1. Customer Validation
    if ($customerType === 'existing') {
        $customerId = (int)($_POST['customer_id'] ?? 0);
        if ($customerId <= 0) {
            $errors[] = 'Please select an existing customer.';
        } else {
            $checkCust = $db->prepare("SELECT `id` FROM `customers` WHERE `id` = ?");
            $checkCust->execute([$customerId]);
            if (!$checkCust->fetch()) {
                $errors[] = 'Selected customer does not exist in the database.';
            }
        }
    } else {
        $newCustName = trim($_POST['new_cust_name'] ?? '');
        $newCustPhone = trim($_POST['new_cust_phone'] ?? '');
        $newCustEmail = trim($_POST['new_cust_email'] ?? '');
        $newCustAddress = trim($_POST['new_cust_address'] ?? '');

        if (empty($newCustName)) {
            $errors[] = 'Customer full name is required.';
        }
        if (empty($newCustPhone)) {
            $errors[] = 'Customer phone number is required.';
        }
        if (!empty($newCustEmail) && !filter_var($newCustEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
    }

    // 2. Mobile & Quantity Validation
    if ($selectedMobileId <= 0) {
        $errors[] = 'Please select a mobile phone to purchase.';
    }

    if ($quantity <= 0) {
        $errors[] = 'Quantity must be at least 1 unit.';
    }

    // Process Transaction
    if (empty($errors)) {
        try {
            $db->beginTransaction();

            // Create new customer record if applicable
            if ($customerType === 'new') {
                $insertCust = $db->prepare("INSERT INTO `customers` (`name`, `email`, `phone`, `address`) VALUES (?, ?, ?, ?)");
                $insertCust->execute([$newCustName, $newCustEmail ?: ($newCustPhone . '@walkin.local'), $newCustPhone, $newCustAddress]);
                $customerId = (int)$db->lastInsertId();
            }

            // Lock and fetch mobile record to ensure fresh stock verification
            $mobileStmt = $db->prepare("SELECT * FROM `mobiles` WHERE `id` = ? FOR UPDATE");
            $mobileStmt->execute([$selectedMobileId]);
            $mobile = $mobileStmt->fetch();

            if (!$mobile) {
                throw new Exception('Selected mobile phone was not found.');
            }

            if ($mobile['stock'] < $quantity) {
                throw new Exception("Insufficient stock! Only {$mobile['stock']} unit(s) available for {$mobile['name']}.");
            }

            // Server-side calculation of price and totals
            $unitPrice = (float)$mobile['price'];
            $totalAmount = $unitPrice * $quantity;

            // Generate unique Invoice Number
            $invoiceNo = 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            // 1. Insert into sales table
            $saleStmt = $db->prepare("
                INSERT INTO `sales` 
                (`invoice_no`, `customer_id`, `total_amount`, `payment_method`, `status`, `notes`) 
                VALUES (?, ?, ?, ?, 'Completed', ?)
            ");
            $saleStmt->execute([$invoiceNo, $customerId, $totalAmount, $paymentMethod, $notes]);
            $saleId = (int)$db->lastInsertId();

            // 2. Insert into sale_items table
            $itemStmt = $db->prepare("
                INSERT INTO `sale_items` 
                (`sale_id`, `mobile_id`, `quantity`, `unit_price`, `subtotal`) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $itemStmt->execute([$saleId, $selectedMobileId, $quantity, $unitPrice, $totalAmount]);

            // 3. Atomically decrease stock
            $stockStmt = $db->prepare("UPDATE `mobiles` SET `stock` = `stock` - ? WHERE `id` = ?");
            $stockStmt->execute([$quantity, $selectedMobileId]);

            $db->commit();

            setFlash('sales_msg', "Sale recorded successfully! Invoice #{$invoiceNo} generated.", 'success');
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

<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
                <div>
                    <h1 class="h3 fw-bold mb-1">Create Direct Sale</h1>
                    <p class="text-muted mb-0">Record an in-store customer purchase and generate invoice</p>
                </div>
                <div>
                    <a href="<?= url('admin/sales.php') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Sales
                    </a>
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

            <form action="<?= url('admin/create_sale.php') ?>" method="POST" id="saleForm">
                <div class="row g-4">
                    <!-- Customer Information Section -->
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="fw-bold mb-0"><i class="bi bi-person me-2 text-primary"></i> Customer Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Customer Type</label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="customer_type" id="typeExisting" value="existing" <?= ($customerType === 'existing') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="typeExisting">Select Existing Customer</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="customer_type" id="typeNew" value="new" <?= ($customerType === 'new') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="typeNew">New / Walk-in Customer</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Existing Customer Dropdown -->
                                <div id="existingCustomerSection" class="<?= ($customerType === 'existing') ? '' : 'd-none' ?>">
                                    <div class="mb-3">
                                        <label for="customer_id" class="form-label fw-semibold">Select Customer <span class="text-danger">*</span></label>
                                        <select class="form-select" id="customer_id" name="customer_id">
                                            <option value="">Choose registered customer</option>
                                            <?php foreach ($customers as $c): ?>
                                                <option value="<?= $c['id'] ?>" <?= ($customerId == $c['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- New Customer Form Fields -->
                                <div id="newCustomerSection" class="<?= ($customerType === 'new') ? '' : 'd-none' ?>">
                                    <div class="mb-3">
                                        <label for="new_cust_name" class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="new_cust_name" name="new_cust_name" value="<?= htmlspecialchars($newCustName) ?>" placeholder="Full Name">
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-6">
                                            <label for="new_cust_phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" id="new_cust_phone" name="new_cust_phone" value="<?= htmlspecialchars($newCustPhone) ?>" placeholder="10-digit Phone">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="new_cust_email" class="form-label fw-semibold">Email (Optional)</label>
                                            <input type="email" class="form-control" id="new_cust_email" name="new_cust_email" value="<?= htmlspecialchars($newCustEmail) ?>" placeholder="customer@example.com">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_cust_address" class="form-label fw-semibold">Address (Optional)</label>
                                        <textarea class="form-control" id="new_cust_address" name="new_cust_address" rows="2" placeholder="City or Street Address"><?= htmlspecialchars($newCustAddress) ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product & Payment Section -->
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white py-3">
                                <h5 class="fw-bold mb-0"><i class="bi bi-phone me-2 text-primary"></i> Product & Billing</h5>
                            </div>
                            <div class="card-body">
                                <!-- Mobile Phone Selection -->
                                <div class="mb-3">
                                    <label for="mobileSelect" class="form-label fw-semibold">Select Mobile Phone <span class="text-danger">*</span></label>
                                    <select class="form-select" id="mobileSelect" name="mobile_id" required>
                                        <option value="" data-price="0" data-stock="0">Choose mobile from inventory</option>
                                        <?php foreach ($mobiles as $m): ?>
                                            <option value="<?= $m['id'] ?>" 
                                                    data-price="<?= $m['price'] ?>" 
                                                    data-stock="<?= $m['stock'] ?>"
                                                    <?= ($selectedMobileId == $m['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($m['brand_name']) ?> - <?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['ram']) ?>/<?= htmlspecialchars($m['storage']) ?>) &bull; <?= formatPrice($m['price']) ?> [Stock: <?= $m['stock'] ?>]
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">Unit Price</label>
                                        <div class="form-control bg-light fw-bold text-dark" id="displayUnitPrice">
                                            <?= CURRENCY_SYMBOL ?> 0.00
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label for="quantityInput" class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="quantityInput" name="quantity" min="1" max="1" value="<?= $quantity ?>" required>
                                        <small class="text-muted d-block mt-1" id="stockNotice">Select a mobile to view stock</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_method" class="form-label fw-semibold">Payment Mode</label>
                                    <select class="form-select" id="payment_method" name="payment_method">
                                        <option value="Cash" <?= ($paymentMethod === 'Cash') ? 'selected' : '' ?>>Cash</option>
                                        <option value="UPI" <?= ($paymentMethod === 'UPI') ? 'selected' : '' ?>>UPI / QR Code</option>
                                        <option value="Card" <?= ($paymentMethod === 'Card') ? 'selected' : '' ?>>Credit / Debit Card</option>
                                        <option value="Net Banking" <?= ($paymentMethod === 'Net Banking') ? 'selected' : '' ?>>Net Banking</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label fw-semibold">Order Notes (Optional)</label>
                                    <input type="text" class="form-control" id="notes" name="notes" value="<?= htmlspecialchars($notes) ?>" placeholder="e.g. Free tempered glass included">
                                </div>

                                <!-- Live Total Display -->
                                <div class="p-3 bg-light rounded border mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fs-5 fw-semibold text-muted">Total Amount:</span>
                                        <span class="fs-3 fw-bold text-success" id="displayTotalAmount"><?= CURRENCY_SYMBOL ?> 0.00</span>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm" id="submitSaleBtn">
                                    <i class="bi bi-check2-circle me-1"></i> Complete Sale & Generate Invoice
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeExisting = document.getElementById('typeExisting');
    const typeNew = document.getElementById('typeNew');
    const existingSection = document.getElementById('existingCustomerSection');
    const newSection = document.getElementById('newCustomerSection');

    typeExisting.addEventListener('change', function() {
        existingSection.classList.remove('d-none');
        newSection.classList.add('d-none');
    });

    typeNew.addEventListener('change', function() {
        existingSection.classList.add('d-none');
        newSection.classList.remove('d-none');
    });

    const mobileSelect = document.getElementById('mobileSelect');
    const quantityInput = document.getElementById('quantityInput');
    const displayUnitPrice = document.getElementById('displayUnitPrice');
    const displayTotalAmount = document.getElementById('displayTotalAmount');
    const stockNotice = document.getElementById('stockNotice');
    const submitBtn = document.getElementById('submitSaleBtn');

    function updateCalculations() {
        const selectedOption = mobileSelect.options[mobileSelect.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
        let qty = parseInt(quantityInput.value);
        if (isNaN(qty) || qty < 1) {
            qty = 1;
        }

        if (stock > 0) {
            quantityInput.max = stock;
            if (qty > stock) {
                qty = stock;
                quantityInput.value = stock;
            }
            stockNotice.innerHTML = '<span class="text-success fw-semibold">' + stock + ' units available</span>';
            submitBtn.disabled = false;
        } else if (selectedOption.value) {
            stockNotice.innerHTML = '<span class="text-danger fw-semibold">Out of Stock!</span>';
            submitBtn.disabled = true;
        } else {
            stockNotice.innerHTML = '<span class="text-muted">Select a mobile to view stock</span>';
            submitBtn.disabled = false;
        }

        displayUnitPrice.textContent = '<?= CURRENCY_SYMBOL ?> ' + price.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        const total = price * qty;
        displayTotalAmount.textContent = '<?= CURRENCY_SYMBOL ?> ' + total.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    mobileSelect.addEventListener('change', updateCalculations);
    quantityInput.addEventListener('input', updateCalculations);
    updateCalculations();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
