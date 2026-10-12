<?php
/**
 * View Mobile Phone Details
 * Mobile Shop Management System
 */
$pageTitle = 'Mobile Details';
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

$stmt = $db->prepare("
    SELECT m.*, b.name AS brand_name 
    FROM `mobiles` m 
    JOIN `brands` b ON m.brand_id = b.id 
    WHERE m.id = ? 
    LIMIT 1
");
$stmt->execute([$id]);
$mobile = $stmt->fetch();

if (!$mobile) {
    setFlash('mobile_msg', 'Mobile phone not found.', 'danger');
    redirect(url('admin/mobiles.php'));
}

// Check sales count for this mobile
$salesStmt = $db->prepare("SELECT COALESCE(SUM(quantity), 0) FROM `sale_items` WHERE `mobile_id` = ?");
$salesStmt->execute([$id]);
$unitsSold = (int)$salesStmt->fetchColumn();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
                <div>
                    <h1 class="h3 fw-bold mb-1"><?= htmlspecialchars($mobile['name']) ?></h1>
                    <p class="text-muted mb-0"><?= htmlspecialchars($mobile['brand_name']) ?> &bull; <?= htmlspecialchars($mobile['model']) ?></p>
                </div>
                <div class="btn-toolbar gap-2">
                    <a href="<?= url('admin/mobiles.php') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                    <a href="<?= url('admin/edit_mobile.php?id=' . $mobile['id']) ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit Mobile
                    </a>
                </div>
            </div>

            <div class="row g-4">
                <!-- Mobile Image Card -->
                <div class="col-md-5 col-lg-4">
                    <div class="card shadow-sm border-0 text-center p-3">
                        <div class="product-img-container rounded bg-light mb-3">
                            <img src="<?= asset('images/' . ($mobile['image'] ?: 'default_mobile.png')) ?>" alt="<?= htmlspecialchars($mobile['name']) ?>" class="product-img">
                        </div>
                        <h4 class="fw-bold text-success mb-1"><?= formatPrice($mobile['price']) ?></h4>
                        <div class="my-2">
                            <?php if ($mobile['stock'] == 0): ?>
                                <span class="badge bg-danger fs-6">Out of Stock (0)</span>
                            <?php elseif ($mobile['stock'] <= 5): ?>
                                <span class="badge bg-warning text-dark fs-6">Low Stock (<?= $mobile['stock'] ?> units)</span>
                            <?php else: ?>
                                <span class="badge bg-success fs-6">In Stock (<?= $mobile['stock'] ?> units)</span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">Total Units Sold to date: <strong><?= $unitsSold ?></strong></small>
                    </div>
                </div>

                <!-- Specifications & Details -->
                <div class="col-md-7 col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="fw-bold mb-0">Hardware Specifications</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="bg-light w-25">Brand</th>
                                            <td><?= htmlspecialchars($mobile['brand_name']) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Model Number</th>
                                            <td><?= htmlspecialchars($mobile['model']) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">RAM</th>
                                            <td><?= htmlspecialchars($mobile['ram']) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Internal Storage</th>
                                            <td><?= htmlspecialchars($mobile['storage']) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Color</th>
                                            <td><?= htmlspecialchars($mobile['color']) ?></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Stock Units</th>
                                            <td><?= $mobile['stock'] ?> units</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Added Date</th>
                                            <td><?= date('d M Y, h:i A', strtotime($mobile['created_at'])) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3">
                            <h5 class="fw-bold mb-0">Description & Features</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0" style="line-height: 1.7;">
                                <?= nl2br(htmlspecialchars($mobile['description'] ?: 'No detailed description provided.')) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
