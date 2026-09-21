<?php
/**
 * User Login
 * Mobile Shop Management System
 */
$pageTitle = 'Login';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect based on role
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(url('admin/dashboard.php'));
    } else {
        redirect(url('customer/dashboard.php'));
    }
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email)) {
        $errors[] = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors[] = 'Please enter your password.';
    }

    // Authenticate user
    if (empty($errors)) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM `users` WHERE `email` = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Successful login
                loginUser($user);
                
                if ($user['role'] === 'admin') {
                    setFlash('login_success', 'Welcome back, Administrator!', 'success');
                    redirect(url('admin/dashboard.php'));
                } else {
                    setFlash('login_success', 'Welcome back, ' . $user['name'] . '!', 'success');
                    redirect(url('customer/dashboard.php'));
                }
            } else {
                $errors[] = 'Invalid email address or password.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Authentication failed. Please check database connection.';
        }
    }
}

$flashAuth = getFlash('auth_error');
$flashLogout = getFlash('logout_success');
$flashRegister = getFlash('register_success');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-5 my-auto">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4 p-sm-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-box-arrow-in-right fs-2"></i>
                        </div>
                        <h4 class="fw-bold mb-1">Sign In</h4>
                        <p class="text-muted small">Access your Mobile Planet account</p>
                    </div>

                    <?php if ($flashAuth): ?>
                        <div class="alert alert-<?= $flashAuth['type'] ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($flashAuth['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($flashLogout): ?>
                        <div class="alert alert-<?= $flashLogout['type'] ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($flashLogout['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($flashRegister): ?>
                        <div class="alert alert-<?= $flashRegister['type'] ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($flashRegister['message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

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

                    <form action="<?= url('auth/login.php') ?>" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="name@example.com" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="mb-0 text-muted small">Don't have an account? <a href="<?= url('auth/register.php') ?>" class="text-primary fw-semibold">Register Here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
