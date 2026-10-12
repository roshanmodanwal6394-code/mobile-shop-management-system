<?php
/**
 * Delete Brand (Safe Action)
 * Mobile Shop Management System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlash('brand_msg', 'Invalid brand ID specified.', 'danger');
    redirect(url('admin/brands.php'));
}

try {
    // Check if brand exists
    $stmt = $db->prepare("SELECT `name` FROM `brands` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$id]);
    $brand = $stmt->fetch();

    if (!$brand) {
        setFlash('brand_msg', 'Brand not found.', 'danger');
        redirect(url('admin/brands.php'));
    }

    // Check if any mobiles are linked to this brand
    $checkMobiles = $db->prepare("SELECT COUNT(*) FROM `mobiles` WHERE `brand_id` = ?");
    $checkMobiles->execute([$id]);
    $mobileCount = (int)$checkMobiles->fetchColumn();

    if ($mobileCount > 0) {
        setFlash('brand_msg', 'Cannot delete brand "' . $brand['name'] . '" because ' . $mobileCount . ' mobile model(s) are linked to it. Please reassign or delete the mobiles first.', 'warning');
    } else {
        $delStmt = $db->prepare("DELETE FROM `brands` WHERE `id` = ?");
        $delStmt->execute([$id]);
        setFlash('brand_msg', 'Brand "' . $brand['name'] . '" was deleted successfully.', 'success');
    }
} catch (PDOException $e) {
    setFlash('brand_msg', 'Error deleting brand: ' . $e->getMessage(), 'danger');
}

redirect(url('admin/brands.php'));
