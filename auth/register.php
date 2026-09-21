<?php
/**
 * Customer Registration
 * Mobile Shop Management System
 */
$pageTitle = 'Customer Registration';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(url('admin/dashboard.php'));
    } else {
        redirect(url('customer/dashboard.php'));
    }
}

$errors = [];
$name = '';
$email = '';
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Form Validations
    if (empty($name)) {
        $errors[] = 'Full name is required.';
    }

    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($phone)) {
        $errors[] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9+\s-]{8,15}$/', $phone)) {
        $errors[] = 'Please enter a valid phone number (8-15 digits).';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    // Check if email already exists
    if (empty($errors)) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT `id` FROM `users` WHERE `email` = ? LIMIT 1");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'This email address is already registered. Please login instead.';
            } else {
                // Begin Transaction for atomic user and customer creation
                $db->beginTransaction();

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insertUser = $db->prepare("INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`) VALUES (?, ?, ?, 'customer', ?, ?)");
                $insertUser->execute([$name, $email, $hashedPassword, $phone, $address]);
                $userId = $db->lastInsertId();

                $insertCustomer = $db->prepare("INSERT INTO `customers` (`user_id`, `name`, `email`, `phone`, `address`) VALUES (?, ?, ?, ?, ?)");
                $insertCustomer->execute([$userId, $name, $email, $phone, $address]);

                $db->commit();

                // Automatically log the customer in
                $newUser = [
                    'id'      => $userId,
                    'name'    => $name,
                    'email'   => $email,
                    'role'    => 'customer',
                    'phone'   => $phone,
                    'address' => $address
                ];
                loginUser($newUser);

                setFlash('login_success', 'Account created successfully! Welcome to Mobile Planet.', 'success');
                redirect(url('customer/dashboard.php'));
            }
        } catch (PDOException $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Registration error: " . $e->getMessage());
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5 my-auto">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4 p-sm-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-person-plus fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Create an Account</h4>
                        <p class="text-muted small">Register as a customer to browse and order mobiles</p>
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

                    <form action="<?= url('auth/register.php') ?>" method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="e.g. Rahul Sharma" required autofocus>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="name@example.com" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="10-digit mobile number" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Delivery Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                                <textarea class="form-control" id="address" name="address" rows="2" placeholder="Street, Building, City, Pincode"><?= htmlspecialchars($address) ?></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="At least 6 characters" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-person-check me-1"></i> Register Account
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0 text-muted small">Already registered? <a href="<?= url('auth/login.php') ?>" class="text-primary fw-semibold">Sign In Here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
