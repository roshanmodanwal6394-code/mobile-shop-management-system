<?php
/**
 * Customer Management
 * Mobile Shop Management System
 */
$pageTitle = 'Manage Customers';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$db = getDB();

// Fetch all customers with total order statistics
$query = "
    SELECT c.*, 
           COUNT(s.id) AS total_orders, 
           COALESCE(SUM(s.total_amount), 0) AS total_spent
    FROM `customers` c 
    LEFT JOIN `sales` s ON c.id = s.customer_id AND s.status = 'Completed'
    GROUP BY c.id 
    ORDER BY c.id DESC
";
$customers = $db->query($query)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-4 border-bottom">
                <div>
                    <h1 class="h3 fw-bold mb-1">Customer Management</h1>
                    <p class="text-muted mb-0">View registered customers, purchase records, and contact details</p>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center justify-content-between g-2">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="tableSearchInput" placeholder="Search customer by name, email, phone...">
                            </div>
                        </div>
                        <div class="col-auto text-muted small">
                            Total Registered: <strong class="text-dark"><?= count($customers) ?></strong>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="filterableTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 70px;">#ID</th>
                                    <th>Customer Name</th>
                                    <th>Contact Info</th>
                                    <th>Delivery Address</th>
                                    <th>Total Orders</th>
                                    <th>Total Spent</th>
                                    <th>Registered Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($customers)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No customers found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($customers as $c): ?>
                                        <tr>
                                            <td class="text-muted">#<?= $c['id'] ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($c['name']) ?></div>
                                                <?php if ($c['user_id']): ?>
                                                    <span class="badge bg-light text-primary border" style="font-size: 0.7rem;">Registered User</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">Walk-in Guest</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div><i class="bi bi-envelope me-1 text-muted"></i> <?= htmlspecialchars($c['email']) ?></div>
                                                <div class="small"><i class="bi bi-telephone me-1 text-muted"></i> <?= htmlspecialchars($c['phone']) ?></div>
                                            </td>
                                            <td class="small text-muted" style="max-width: 250px;">
                                                <?= htmlspecialchars($c['address'] ?: 'Not provided') ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-dark">
                                                    <?= $c['total_orders'] ?> orders
                                                </span>
                                            </td>
                                            <td class="fw-bold text-success">
                                                <?= formatPrice($c['total_spent']) ?>
                                            </td>
                                            <td class="small text-muted">
                                                <?= date('d M Y', strtotime($c['created_at'])) ?>
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
