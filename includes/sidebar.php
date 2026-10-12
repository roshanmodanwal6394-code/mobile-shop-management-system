<?php
/**
 * Admin Sidebar Navigation
 * Mobile Shop Management System
 */
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="col-md-3 col-lg-2 d-md-block bg-white sidebar shadow-sm rounded mb-4 mb-md-0 p-3">
    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
        <i class="bi bi-shield-lock-fill text-primary fs-3 me-2"></i>
        <div>
            <h6 class="mb-0 fw-bold">Admin Portal</h6>
            <small class="text-muted">Management Panel</small>
        </div>
    </div>

    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item mb-1">
            <a href="<?= url('admin/dashboard.php') ?>" class="nav-link <?= ($currentPage == 'dashboard.php') ? 'active' : 'text-dark' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="<?= url('admin/brands.php') ?>" class="nav-link <?= (in_array($currentPage, ['brands.php', 'add_brand.php', 'edit_brand.php'])) ? 'active' : 'text-dark' ?>">
                <i class="bi bi-tags me-2"></i> Brands
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="<?= url('admin/mobiles.php') ?>" class="nav-link <?= (in_array($currentPage, ['mobiles.php', 'edit_mobile.php', 'view_mobile.php'])) ? 'active' : 'text-dark' ?>">
                <i class="bi bi-phone me-2"></i> Mobile Phones
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="<?= url('admin/add_mobile.php') ?>" class="nav-link <?= ($currentPage == 'add_mobile.php') ? 'active' : 'text-dark' ?>">
                <i class="bi bi-plus-circle me-2"></i> Add Mobile
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="<?= url('admin/customers.php') ?>" class="nav-link <?= ($currentPage == 'customers.php') ? 'active' : 'text-dark' ?>">
                <i class="bi bi-people me-2"></i> Customers
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="<?= url('admin/sales.php') ?>" class="nav-link <?= (in_array($currentPage, ['sales.php', 'invoice.php'])) ? 'active' : 'text-dark' ?>">
                <i class="bi bi-receipt me-2"></i> Sales & Invoices
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="<?= url('admin/create_sale.php') ?>" class="nav-link <?= ($currentPage == 'create_sale.php') ? 'active' : 'text-dark' ?>">
                <i class="bi bi-cart-plus me-2"></i> Create Sale
            </a>
        </li>
    </ul>

    <hr>

    <div class="mt-auto">
        <ul class="nav nav-pills flex-column">
            <li class="nav-item mb-1">
                <a href="<?= url('customer/mobiles.php') ?>" class="nav-link text-secondary" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-2"></i> View Storefront
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= url('auth/logout.php') ?>" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>
