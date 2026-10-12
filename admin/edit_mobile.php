<?php
/**
 * Edit Mobile Phone
 * Mobile Shop Management System
 */
$pageTitle = 'Edit Mobile Phone';
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

// Fetch mobile details
$stmt = $db->prepare("SELECT * FROM `mobiles` WHERE `id` = ? LIMIT 1");
$stmt->execute([$id]);
$mobile = $stmt->fetch();

if (!$mobile) {
    setFlash('mobile_msg', 'Mobile phone not found.', 'danger');
    redirect(url('admin/mobiles.php'));
}

// Fetch brands
$brands = $db->query("SELECT * FROM `brands` ORDER BY `name` ASC")->fetchAll();

$errors = [];
$brandId = $mobile['brand_id'];
$name = $mobile['name'];
$model = $mobile['model'];
$ram = $mobile['ram'];
$storage = $mobile['storage'];
$color = $mobile['color'];
$price = $mobile['price'];
$stock = $mobile['stock'];
$description = $mobile['description'];
$currentImage = $mobile['image'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brandId = (int)($_POST['brand_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $ram = trim($_POST['ram'] ?? '');
    $storage = trim($_POST['storage'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '0');
    $description = trim($_POST['description'] ?? '');

    // Form Validations
    if ($brandId <= 0) {
        $errors[] = 'Please select a brand.';
    }

    if (empty($name)) {
        $errors[] = 'Mobile name is required.';
    }

    if (empty($model)) {
        $errors[] = 'Model number/code is required.';
    }

    if (empty($ram)) {
        $errors[] = 'RAM specification is required.';
    }

    if (empty($storage)) {
        $errors[] = 'Storage specification is required.';
    }

    if (empty($color)) {
        $errors[] = 'Color is required.';
    }

    if (empty($price) || !is_numeric($price) || (float)$price <= 0) {
        $errors[] = 'Please enter a valid price greater than 0.';
    }

    if (!is_numeric($stock) || (int)$stock < 0) {
        $errors[] = 'Stock quantity must be a non-negative number.';
    }

    // Handle new image upload if provided
    $imageFilename = $currentImage;

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'An error occurred during image upload (Error code: ' . $file['error'] . ').';
        } else {
            $maxFileSize = 2 * 1024 * 1024; // 2MB
            if ($file['size'] > $maxFileSize) {
                $errors[] = 'Image file size must not exceed 2 MB.';
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = 'Invalid image format. Only JPG, JPEG, PNG, and WEBP files are allowed.';
            }

            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                $errors[] = 'The uploaded file is not a valid image.';
            }

            if (empty($errors)) {
                $newFilename = 'mobile_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExtension;
                $targetDir = __DIR__ . '/../assets/images/';
                
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $targetPath = $targetDir . $newFilename;
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    // Remove old custom uploaded image (only files generated via upload starting with mobile_)
                    if (!empty($currentImage) && str_starts_with($currentImage, 'mobile_')) {
                        @unlink($targetDir . $currentImage);
                    }
                    $imageFilename = $newFilename;
                } else {
                    $errors[] = 'Failed to save uploaded image.';
                }
            }
        }
    }

    // Update database
    if (empty($errors)) {
        try {
            $update = $db->prepare("
                UPDATE `mobiles`
                SET `brand_id` = ?, `name` = ?, `model` = ?, `ram` = ?, `storage` = ?, `color` = ?, `price` = ?, `stock` = ?, `description` = ?, `image` = ?
                WHERE `id` = ?
            ");
            $update->execute([
                $brandId,
                $name,
                $model,
                $ram,
                $storage,
                $color,
                (float)$price,
                (int)$stock,
                $description,
                $imageFilename,
                $id
            ]);

            setFlash('mobile_msg', 'Mobile phone "' . $name . '" updated successfully!', 'success');
            redirect(url('admin/mobiles.php'));
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
                    <h1 class="h3 fw-bold mb-1">Edit Mobile Phone</h1>
                    <p class="text-muted mb-0">Update specifications or adjust inventory stock for #<?= $id ?></p>
                </div>
                <div>
                    <a href="<?= url('admin/mobiles.php') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Mobiles
                    </a>
                </div>
            </div>

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

                    <form action="<?= url('admin/edit_mobile.php?id=' . $id) ?>" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <!-- Brand Selection -->
                            <div class="col-md-6">
                                <label for="brand_id" class="form-label fw-semibold">Brand / Manufacturer <span class="text-danger">*</span></label>
                                <select class="form-select" id="brand_id" name="brand_id" required>
                                    <option value="">Select Brand</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?= $brand['id'] ?>" <?= ($brandId == $brand['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($brand['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Mobile Name -->
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">Mobile Model Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                            </div>

                            <!-- Model Code -->
                            <div class="col-md-4">
                                <label for="model" class="form-label fw-semibold">Model Number / Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="model" name="model" value="<?= htmlspecialchars($model) ?>" required>
                            </div>

                            <!-- RAM -->
                            <div class="col-md-4">
                                <label for="ram" class="form-label fw-semibold">RAM <span class="text-danger">*</span></label>
                                <select class="form-select" id="ram" name="ram" required>
                                    <option value="4 GB" <?= ($ram === '4 GB') ? 'selected' : '' ?>>4 GB</option>
                                    <option value="6 GB" <?= ($ram === '6 GB') ? 'selected' : '' ?>>6 GB</option>
                                    <option value="8 GB" <?= ($ram === '8 GB') ? 'selected' : '' ?>>8 GB</option>
                                    <option value="12 GB" <?= ($ram === '12 GB') ? 'selected' : '' ?>>12 GB</option>
                                    <option value="16 GB" <?= ($ram === '16 GB') ? 'selected' : '' ?>>16 GB</option>
                                </select>
                            </div>

                            <!-- Storage -->
                            <div class="col-md-4">
                                <label for="storage" class="form-label fw-semibold">Internal Storage <span class="text-danger">*</span></label>
                                <select class="form-select" id="storage" name="storage" required>
                                    <option value="64 GB" <?= ($storage === '64 GB') ? 'selected' : '' ?>>64 GB</option>
                                    <option value="128 GB" <?= ($storage === '128 GB') ? 'selected' : '' ?>>128 GB</option>
                                    <option value="256 GB" <?= ($storage === '256 GB') ? 'selected' : '' ?>>256 GB</option>
                                    <option value="512 GB" <?= ($storage === '512 GB') ? 'selected' : '' ?>>512 GB</option>
                                    <option value="1 TB" <?= ($storage === '1 TB') ? 'selected' : '' ?>>1 TB</option>
                                </select>
                            </div>

                            <!-- Color -->
                            <div class="col-md-4">
                                <label for="color" class="form-label fw-semibold">Color <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="color" name="color" value="<?= htmlspecialchars($color) ?>" required>
                            </div>

                            <!-- Price -->
                            <div class="col-md-4">
                                <label for="price" class="form-label fw-semibold">Selling Price (<?= CURRENCY_SYMBOL ?>) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                                    <input type="number" step="0.01" min="1" class="form-control" id="price" name="price" value="<?= htmlspecialchars($price) ?>" required>
                                </div>
                            </div>

                            <!-- Stock -->
                            <div class="col-md-4">
                                <label for="stock" class="form-label fw-semibold">Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number" min="0" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars($stock) ?>" required>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label for="description" class="form-label fw-semibold">Key Features & Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($description) ?></textarea>
                            </div>

                            <!-- Current Image and New Image Upload -->
                            <div class="col-md-6">
                                <label for="mobileImageInput" class="form-label fw-semibold">Change Image (Optional)</label>
                                <input type="file" class="form-control" id="mobileImageInput" name="image" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted d-block mt-1">Leave blank to keep current image. Allowed: JPG, PNG, WEBP (Max 2MB).</small>
                            </div>

                            <div class="col-md-6">
                                <span class="form-label fw-semibold d-block">Current / Selected Image</span>
                                <img id="imagePreview" src="<?= asset('images/' . ($currentImage ?: 'default_mobile.png')) ?>" alt="Preview" class="img-thumbnail" style="max-height: 120px; object-fit: contain;">
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= url('admin/mobiles.php') ?>" class="btn btn-light border">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-circle me-1"></i> Update Mobile Phone
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
