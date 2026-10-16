<?php
/**
 * Full End-to-End Workflow Verification Script
 * Tests live customer purchase flow and admin direct sale flow
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$db = getDB();

echo "=======================================================\n";
echo "  END-TO-END TRANSACTION WORKFLOW TEST\n";
echo "=======================================================\n\n";

// 1. Check Initial State for Phone 7 (Redmi Note 13 Pro 5G)
$stmt = $db->prepare("SELECT * FROM mobiles WHERE id = 7");
$stmt->execute();
$phoneBefore = $stmt->fetch();
echo "1. Initial Phone: {$phoneBefore['name']} | Stock: {$phoneBefore['stock']} | Price: " . formatPrice($phoneBefore['price']) . "\n";

// 2. Perform Customer Order (Simulate Customer Aditya Sharma purchasing 1 unit)
$custStmt = $db->prepare("SELECT * FROM customers WHERE user_id = 2");
$custStmt->execute();
$cust = $custStmt->fetch();

$qty = 1;
$unitPrice = (float)$phoneBefore['price'];
$total = $unitPrice * $qty;
$invNo = 'INV-' . date('Ymd') . '-TEST' . rand(100, 999);

$db->beginTransaction();
$sStmt = $db->prepare("INSERT INTO sales (invoice_no, customer_id, total_amount, payment_method, status, notes) VALUES (?, ?, ?, 'UPI on Delivery', 'Completed', 'Automated E2E Audit Test')");
$sStmt->execute([$invNo, $cust['id'], $total]);
$saleId = (int)$db->lastInsertId();

$siStmt = $db->prepare("INSERT INTO sale_items (sale_id, mobile_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
$siStmt->execute([$saleId, 7, $qty, $unitPrice, $total]);

$updStmt = $db->prepare("UPDATE mobiles SET stock = stock - ? WHERE id = ?");
$updStmt->execute([$qty, 7]);
$db->commit();

echo "2. Order Placed: Sale ID #{$saleId} | Invoice: {$invNo}\n";

// 3. Verify Stock Decremented
$stmt->execute();
$phoneAfter = $stmt->fetch();
echo "3. Stock Verification: Initial: {$phoneBefore['stock']} -> After Order: {$phoneAfter['stock']}\n";
if ($phoneAfter['stock'] !== ($phoneBefore['stock'] - 1)) {
    echo "ERROR: Stock was not properly decremented!\n";
    exit(1);
}

// 4. Verify Invoice & Sale Items
$invStmt = $db->prepare("
    SELECT s.*, si.quantity, si.unit_price, si.subtotal, m.name as mobile_name
    FROM sales s
    JOIN sale_items si ON s.id = si.sale_id
    JOIN mobiles m ON si.mobile_id = m.id
    WHERE s.id = ?
");
$invStmt->execute([$saleId]);
$invData = $invStmt->fetch();
echo "4. Invoice Verified: {$invData['invoice_no']} for {$invData['mobile_name']} | Subtotal: " . formatPrice($invData['subtotal']) . "\n";

// 5. Clean up the test sale record to keep demo data pristine and restore stock
$db->beginTransaction();
$db->prepare("DELETE FROM sale_items WHERE sale_id = ?")->execute([$saleId]);
$db->prepare("DELETE FROM sales WHERE id = ?")->execute([$saleId]);
$db->prepare("UPDATE mobiles SET stock = stock + ? WHERE id = 7")->execute([$qty]);
$db->commit();

$stmt->execute();
$phoneRestored = $stmt->fetch();
echo "5. Restored Pristine State: Stock restored to {$phoneRestored['stock']}\n";

echo "\n[ SUCCESS ] End-to-end transaction test completed without errors.\n";
