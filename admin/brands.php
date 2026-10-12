<?php
/**
 * Brand Management
 * Mobile Shop Management System
 */
$pageTitle = 'Manage Brands';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$db = getDB();

// Fetch all brands along with count of mobile models linked to each
$stmt = $db->query("
    SELECT b.*, COUNT(m.id) AS mobile_count 
    FROM `brands` b 
    LEFT JOIN `mobiles` m ON b.id = m.brand_id 
    GROUP BY b.id 
    ORDER BY b.name ASC
");
$brands = $stmt->fetchAll();

$flashMsg = getFlash('brand_msg');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
                <div>
                    <h1 class="h3 fw-bold mb-1">Brand Management</h1>
                    <p class="text-muted mb-0">Manage smartphone manufacturers and product brands</p>
                </div>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="<?= url('admin/add_brand.php') ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Add New Brand
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
                                <input type="text" class="form-control" id="tableSearchInput" placeholder="Search brands...">
                            </div>
                        </div>
                        <div class="col-auto text-muted small">
                            Total Brands: <strong class="text-dark"><?= count($brands) ?></strong>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="filterableTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 70px;">#ID</th>
                                    <th>Brand Name</th>
                                    <th>Description</th>
                                    <th>Associated Mobiles</th>
                                    <th>Created Date</th>
                                    <th class="text-end" style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($brands)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No brands found. Click "Add New Brand" to add one.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($brands as $brand): ?>
                                        <tr>
                                            <td class="text-muted">#<?= $brand['id'] ?></td>
                                            <td>
                                                <span class="fw-bold text-dark"><?= htmlspecialchars($brand['name']) ?></span>
                                            </td>
                                            <td class="text-muted small">
                                                <?= htmlspecialchars($brand['description'] ?: 'No description provided.') ?>
                                            </td>
                                            <td>
                                                <a href="<?= url('admin/mobiles.php?brand_id=' . $brand['id']) ?>" class="badge bg-light text-primary text-decoration-none border">
                                                    <i class="bi bi-phone me-1"></i> <?= $brand['mobile_count'] ?> models
                                                </a>
                                            </td>
                                            <td class="text-muted small">
                                                <?= date('d M Y', strtotime($brand['created_at'])) ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?= url('admin/edit_brand.php?id=' . $brand['id']) ?>" class="btn btn-outline-primary" title="Edit Brand">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="<?= url('admin/delete_brand.php?id=' . $brand['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete brand: <?= addslashes(htmlspecialchars($brand['name'])) ?>?');" title="Delete Brand">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
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
