<?php
/**
 * Live HTTP Session & RBAC / IDOR Audit Test via Apache cURL
 */

$cookieFile = __DIR__ . '/test_cookie.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

function httpReq($url, $postData = null, $follow = true) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
    curl_setopt($ch, CURLOPT_HEADER, true);

    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }

    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    return [
        'code' => $httpCode,
        'url'  => $effectiveUrl,
        'headers' => $headers,
        'body' => $body
    ];
}

echo "======================================================================\n";
echo "  LIVE HTTP SESSION, RBAC & IDOR AUDIT VIA APACHE\n";
echo "======================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck($desc, $cond) {
    global $passCount, $failCount;
    if ($cond) {
        $passCount++;
        echo "[ PASS ] {$desc}\n";
    } else {
        $failCount++;
        echo "[ FAIL ] {$desc}\n";
    }
}

// ---------------------------------------------------------
// TEST 1: ADMIN LOGIN & RBAC
// ---------------------------------------------------------
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

// Attempt login
$res = httpReq('http://localhost/mobile-shop-management-system/auth/login.php', [
    'email' => 'admin@mobileshop.com',
    'password' => 'admin123'
]);

assertCheck('Admin login succeeds and redirects to admin/dashboard.php',
    $res['code'] === 200 && str_contains($res['url'], 'admin/dashboard.php') && str_contains($res['body'], 'Dashboard Overview')
);

// Access Admin Mobiles page
$resMobiles = httpReq('http://localhost/mobile-shop-management-system/admin/mobiles.php');
assertCheck('Admin can access admin/mobiles.php inventory list',
    $resMobiles['code'] === 200 && str_contains($resMobiles['body'], 'Mobile Phones Inventory')
);

// Access Admin Brands page
$resBrands = httpReq('http://localhost/mobile-shop-management-system/admin/brands.php');
assertCheck('Admin can access admin/brands.php',
    $resBrands['code'] === 200 && str_contains($resBrands['body'], 'Brand Management')
);

// Access Admin Customers page
$resCustomers = httpReq('http://localhost/mobile-shop-management-system/admin/customers.php');
assertCheck('Admin can access admin/customers.php',
    $resCustomers['code'] === 200 && str_contains($resCustomers['body'], 'Customer Management')
);

// Access Admin Sales page
$resSales = httpReq('http://localhost/mobile-shop-management-system/admin/sales.php');
assertCheck('Admin can access admin/sales.php',
    $resSales['code'] === 200 && str_contains($resSales['body'], 'Sales &amp; Invoices') || str_contains($resSales['body'], 'Sales & Invoices')
);

// Access Invoice #1 as Admin
$resInv = httpReq('http://localhost/mobile-shop-management-system/admin/invoice.php?id=1');
assertCheck('Admin can view Tax Invoice #1',
    $resInv['code'] === 200 && str_contains($resInv['body'], 'Tax Invoice')
);

// Logout Admin
$resLogout = httpReq('http://localhost/mobile-shop-management-system/auth/logout.php');
assertCheck('Admin logout redirects to login page',
    $resLogout['code'] === 200 && str_contains($resLogout['url'], 'auth/login.php')
);

// Verify Admin Dashboard is now blocked without session
$resBlocked = httpReq('http://localhost/mobile-shop-management-system/admin/dashboard.php', null, false);
assertCheck('Logged-out user is blocked from admin dashboard (HTTP 302 to login)',
    $resBlocked['code'] === 302 && str_contains($resBlocked['headers'], 'auth/login.php')
);

// ---------------------------------------------------------
// TEST 2: CUSTOMER LOGIN, RBAC & IDOR CHECKS
// ---------------------------------------------------------
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

// Login as Customer Aditya Sharma (user_id=2, customer_id=1, owns Invoice #1)
$resCustLogin = httpReq('http://localhost/mobile-shop-management-system/auth/login.php', [
    'email' => 'aditya.sharma@example.com',
    'password' => 'aditya123'
]);

assertCheck('Customer login succeeds and redirects to customer/dashboard.php',
    $resCustLogin['code'] === 200 && str_contains($resCustLogin['url'], 'customer/dashboard.php') && str_contains($resCustLogin['body'], 'Aditya Sharma')
);

// Attempt customer accessing admin page (RBAC enforcement)
$resForbiddenAdmin = httpReq('http://localhost/mobile-shop-management-system/admin/dashboard.php', null, false);
assertCheck('Customer accessing admin dashboard is redirected away (HTTP 302)',
    $resForbiddenAdmin['code'] === 302 && str_contains($resForbiddenAdmin['headers'], 'customer/dashboard.php')
);

// Customer viewing own invoice #1 (Authorized)
$resOwnInv = httpReq('http://localhost/mobile-shop-management-system/admin/invoice.php?id=1');
assertCheck('Customer Aditya can view their own Invoice #1',
    $resOwnInv['code'] === 200 && str_contains($resOwnInv['body'], 'Tax Invoice') && str_contains($resOwnInv['body'], 'Aditya Sharma')
);

// Customer Aditya attempting to view Invoice #2 (which belongs to Sneha Joshi) (IDOR Test)
$resSnehaInv = httpReq('http://localhost/mobile-shop-management-system/admin/invoice.php?id=2', null, false);
assertCheck('IDOR Protection: Customer Aditya cannot view Sneha\'s Invoice #2 (Blocked with 302 redirect)',
    $resSnehaInv['code'] === 302 && str_contains($resSnehaInv['headers'], 'customer/purchases.php')
);

// Cleanup cookie file
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

echo "\n----------------------------------------------------------------------\n";
echo "Total Live HTTP Session Checks: " . ($passCount + $failCount) . " | Passed: {$passCount} | Failed: {$failCount}\n";
echo "======================================================================\n";

exit($failCount === 0 ? 0 : 1);
