<?php
/**
 * Test HTTP Endpoints via Apache
 */
$pages = [
    'Homepage'                   => 'http://localhost/mobile-shop-management-system/',
    'Login Page'                 => 'http://localhost/mobile-shop-management-system/auth/login.php',
    'Register Page'              => 'http://localhost/mobile-shop-management-system/auth/register.php',
    'Browse Mobiles'             => 'http://localhost/mobile-shop-management-system/customer/mobiles.php',
    'Redmi Note 13 Pro Details'  => 'http://localhost/mobile-shop-management-system/customer/mobile_details.php?id=7',
    'Xiaomi 14 Civi Details'     => 'http://localhost/mobile-shop-management-system/customer/mobile_details.php?id=9',
    'Realme P1 Details'          => 'http://localhost/mobile-shop-management-system/customer/mobile_details.php?id=10',
    'Filtered by Xiaomi'         => 'http://localhost/mobile-shop-management-system/customer/mobiles.php?brand_id=3',
    'Filtered by Samsung'        => 'http://localhost/mobile-shop-management-system/customer/mobiles.php?brand_id=1',
    'Search for 5G'              => 'http://localhost/mobile-shop-management-system/customer/mobiles.php?search=5G'
];

echo "Testing Live HTTP Endpoints via Apache (localhost:80):\n\n";
$all200 = true;
foreach ($pages as $label => $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $statusStr = ($httpCode === 200) ? "[ OK 200 ]" : "[ FAIL {$httpCode} ]";
    echo sprintf("%-12s | %-28s | Size: %6d bytes\n", $statusStr, $label, strlen($html));
    if ($httpCode !== 200) {
        $all200 = false;
    }
}

if ($all200) {
    echo "\nAll live public pages returned HTTP 200 OK with full HTML content!\n";
} else {
    echo "\nSome pages failed to return HTTP 200 OK.\n";
    exit(1);
}
