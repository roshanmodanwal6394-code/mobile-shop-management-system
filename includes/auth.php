<?php
/**
 * Authentication & Authorization Helper Functions
 * Mobile Shop Management System
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Check if a user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the currently logged-in user data
 * @return array|null
 */
function currentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Check if the currently logged-in user is an administrator
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

/**
 * Check if the currently logged-in user is a customer
 * @return bool
 */
function isCustomer() {
    return isLoggedIn() && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'customer';
}

/**
 * Enforce authentication: Redirect to login if user is not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('auth_error', 'Please login to access this page.', 'warning');
        redirect(url('auth/login.php'));
    }
}

/**
 * Enforce Admin Role: Redirect if user is not an administrator
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlash('auth_error', 'Access denied! Administrator privileges required.', 'danger');
        redirect(url('customer/dashboard.php'));
    }
}

/**
 * Enforce Customer Role: Redirect if user is not a customer
 */
function requireCustomer() {
    requireLogin();
    if (!isCustomer()) {
        setFlash('auth_error', 'Access denied! This area is for customers only.', 'warning');
        redirect(url('admin/dashboard.php'));
    }
}

/**
 * Store user session upon successful login
 * @param array $user
 */
function loginUser($user) {
    // Regenerate session ID to prevent session fixation attacks
    if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
        session_regenerate_id(true);
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user'] = [
        'id'      => $user['id'],
        'name'    => $user['name'],
        'email'   => $user['email'],
        'role'    => $user['role'],
        'phone'   => $user['phone'] ?? '',
        'address' => $user['address'] ?? ''
    ];
}

/**
 * Terminate user session upon logout
 */
function logoutUser() {
    $_SESSION = [];
    if (ini_get("session.use_cookies") && !headers_sent()) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}
