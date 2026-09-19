<?php
/**
 * Main Navigation Bar
 * Mobile Shop Management System
 */
$currentUser = currentUser();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm sticky-top">
    <div class="container">
        <!-- Brand Logo & Name -->
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?= url('index.php') ?>">
            <i class="bi bi-phone text-warning fs-4"></i>
            <span><?= SITE_NAME ?></span>
        </a>

        <!-- Mobile Hamburger Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>" href="<?= url('index.php') ?>">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'mobiles.php' && strpos($_SERVER['PHP_SELF'], '/customer/') !== false) ? 'active' : '' ?>" href="<?= url('customer/mobiles.php') ?>">
                        <i class="bi bi-grid me-1"></i> Browse Mobiles
                    </a>
                </li>
            </ul>

            <!-- Right Side Auth Links -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-2">
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-warning btn-sm me-2" href="<?= url('admin/dashboard.php') ?>">
                                <i class="bi bi-speedometer2 me-1"></i> Admin Panel
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white me-2" href="<?= url('customer/purchases.php') ?>">
                                <i class="bi bi-bag-check me-1"></i> My Orders
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- User Account Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-white" href="#" id="userMenuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-5"></i>
                            <span><?= htmlspecialchars($currentUser['name']) ?></span>
                            <span class="badge <?= isAdmin() ? 'bg-danger' : 'bg-primary' ?> text-uppercase" style="font-size: 0.65rem;">
                                <?= htmlspecialchars($currentUser['role']) ?>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userMenuDropdown">
                            <?php if (isAdmin()): ?>
                                <li><a class="dropdown-item" href="<?= url('admin/dashboard.php') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= url('admin/mobiles.php') ?>"><i class="bi bi-phone me-2"></i> Mobiles</a></li>
                                <li><a class="dropdown-item" href="<?= url('admin/brands.php') ?>"><i class="bi bi-tag me-2"></i> Brands</a></li>
                                <li><a class="dropdown-item" href="<?= url('admin/sales.php') ?>"><i class="bi bi-receipt me-2"></i> Sales</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?= url('customer/dashboard.php') ?>"><i class="bi bi-speedometer2 me-2"></i> My Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?= url('customer/purchases.php') ?>"><i class="bi bi-bag-check me-2"></i> My Purchases</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?= url('auth/logout.php') ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm me-2" href="<?= url('auth/login.php') ?>">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm" href="<?= url('auth/register.php') ?>">
                            <i class="bi bi-person-plus me-1"></i> Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
