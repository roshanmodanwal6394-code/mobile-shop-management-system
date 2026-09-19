<?php
/**
 * Application Configuration
 * Mobile Shop Management System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Site Details
define('SITE_NAME', 'Mobile Planet');
define('SITE_TAGLINE', 'Your Trusted College Mobile Store');
define('CURRENCY_SYMBOL', '₹');
define('CURRENCY_CODE', 'INR');

// Dynamic BASE_URL detection
// Works automatically under XAMPP (http://localhost/mobile-shop-management-system/)
// as well as PHP built-in server (http://localhost:8000/)
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Determine subdirectory if running under htdocs
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    
    // Find position of project root folder name in path
    $projectFolder = 'mobile-shop-management-system';
    $pos = strpos($scriptDir, '/' . $projectFolder);
    
    if ($pos !== false) {
        $basePath = substr($scriptDir, 0, $pos + strlen($projectFolder) + 1);
    } else {
        // Fallback for custom folder or root hosting
        $basePath = rtrim($scriptDir, '/') . '/';
        if ($basePath === '//') {
            $basePath = '/';
        }
    }
    
    define('BASE_URL', rtrim($protocol . $host . $basePath, '/') . '/');
}

/**
 * Generate a complete application URL
 * @param string $path
 * @return string
 */
function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Generate asset URL
 * @param string $path
 * @return string
 */
function asset($path = '') {
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

/**
 * Format currency with Indian Rupee symbol
 * @param float|int|string $amount
 * @return string
 */
function formatPrice($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format((float)$amount, 2);
}

/**
 * Sanitize input string to prevent XSS
 * @param string|null $data
 * @return string
 */
function sanitize($data) {
    if ($data === null) {
        return '';
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

/**
 * Set a flash message in session
 * @param string $name
 * @param string $message
 * @param string $type ('success', 'danger', 'warning', 'info')
 */
function setFlash($name, $message, $type = 'success') {
    $_SESSION['flash'][$name] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Get and clear a flash message
 * @param string $name
 * @return array|null
 */
function getFlash($name) {
    if (isset($_SESSION['flash'][$name])) {
        $flash = $_SESSION['flash'][$name];
        unset($_SESSION['flash'][$name]);
        return $flash;
    }
    return null;
}

/**
 * Redirect to a specific URL
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}
