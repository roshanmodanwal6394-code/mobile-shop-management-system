<?php
/**
 * Add New Brand
 * Mobile Shop Management System
 */
$pageTitle = 'Add New Brand';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$errors = [];
$name = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $errors[] = 'Brand name is required.';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Brand name must be at least 2 characters long.';
    }

    if (empty($errors)) {
        try {
            $db = getDB();
            // Check if brand already exists
            $stmt = $db->prepare("SELECT `id` FROM `brands` WHERE LOWER(`name`) = LOWER(?) LIMIT 1");
            $stmt->execute([$name]);
            if ($stmt->fetch()) {
                $errors[] = 'A brand with this name already exists.';
            } else {
                $insert = $db->prepare("INSERT INTO `brands` (`name`, `description`) VALUES (?, ?)");
                $insert->execute([$name, $description]);

                setFlash('brand_msg', 'Brand "' . $name . '" added successfully!', 'success');
                redirect(url('admin/brands.php'));
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
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
                    <h1 class="h3 fw-bold mb-1">Add New Brand</h1>
                    <p class="text-muted mb-0">Register a new smartphone manufacturer</p>
                </div>
                <div>
                    <a href="<?= url('admin/brands.php') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Brands
                    </a>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
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

                            <form action="<?= url('admin/add_brand.php') ?>" method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-semibold">Brand Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="e.g. Motorola, OnePlus" required autofocus>
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="form-label fw-semibold">Description / Notes</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief details about this brand or market presence"><?= htmlspecialchars($description) ?></textarea>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="<?= url('admin/brands.php') ?>" class="btn btn-light border">Cancel</a>
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-check-circle me-1"></i> Save Brand
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
