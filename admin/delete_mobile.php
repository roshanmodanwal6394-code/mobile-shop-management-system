<?php
/**
 * Delete Mobile Phone (Safe Action)
 * Mobile Shop Management System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlash('mobile_msg', 'Invalid mobile phone ID.', 'danger');
    redirect(url('admin/mobiles.php'));
}

try {
    // Check if mobile exists
    $stmt = $db->prepare("SELECT * FROM `mobiles` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$id]);
    $mobile = $stmt->fetch();

    if (!$mobile) {
        setFlash('mobile_msg', 'Mobile phone not found.', 'danger');
        redirect(url('admin/mobiles.php'));
    }

    // Check if mobile is used in any sales
    $checkSales = $db->prepare("SELECT COUNT(*) FROM `sale_items` WHERE `mobile_id` = ?");
    $checkSales->execute([$id]);
    $salesCount = (int)$checkSales->fetchColumn();

    if ($salesCount > 0) {
        setFlash('mobile_msg', 'Cannot delete "' . $mobile['name'] . '" because it is linked to ' . $salesCount . ' sales record(s). You can set its stock to 0 instead.', 'warning');
    } else {
        // Delete record
        $del = $db->prepare("DELETE FROM `mobiles` WHERE `id` = ?");
        $del->execute([$id]);

        // Remove custom uploaded image if applicable (only files generated via upload starting with mobile_)
        $image = $mobile['image'];
        if (!empty($image) && str_starts_with($image, 'mobile_')) {
            $imgPath = __DIR__ . '/../assets/images/' . $image;
            if (file_exists($imgPath)) {
                @unlink($imgPath);
            }
        }

        setFlash('mobile_msg', 'Mobile phone "' . $mobile['name'] . '" was deleted successfully.', 'success');
    }
} catch (PDOException $e) {
    setFlash('mobile_msg', 'Error deleting mobile phone: ' . $e->getMessage(), 'danger');
}

redirect(url('admin/mobiles.php'));
