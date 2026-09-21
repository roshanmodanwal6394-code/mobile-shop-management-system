<?php
/**
 * User Logout
 * Mobile Shop Management System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

logoutUser();
setFlash('logout_success', 'You have been successfully logged out.', 'info');
redirect(url('auth/login.php'));
