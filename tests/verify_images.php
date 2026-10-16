<?php
require_once __DIR__ . '/../config/database.php';
$db = getDB();
$mobiles = $db->query('SELECT id, name, image FROM mobiles ORDER BY id')->fetchAll();
$allOk = true;
foreach ($mobiles as $m) {
    $path = __DIR__ . '/../assets/images/' . $m['image'];
    if (!file_exists($path)) {
        echo "MISSING: {$m['name']} -> {$path}\n";
        $allOk = false;
    } else {
        $size = filesize($path);
        $info = @getimagesize($path);
        if (!$info) {
            echo "CORRUPT: {$m['name']} -> {$path}\n";
            $allOk = false;
        } else {
            echo "OK ({$info[0]}x{$info[1]}, " . round($size/1024, 1) . " KB): {$m['name']} -> {$m['image']}\n";
        }
    }
}
if ($allOk) {
    echo "\nAll mobile images verified successfully!\n";
}
