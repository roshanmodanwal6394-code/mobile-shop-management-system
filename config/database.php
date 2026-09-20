<?php
/**
 * Database Connection using PDO
 * Mobile Shop Management System
 */

// Database credentials for XAMPP
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'mobile_shop_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get Database Connection
 * @return PDO
 */
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            // Friendly error message for college demonstration
            die('
                <div style="font-family: Arial, sans-serif; max-width: 650px; margin: 50px auto; padding: 25px; border: 1px solid #f5c6cb; background-color: #f8d7da; color: #721c24; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <h2 style="margin-top: 0; color: #721c24;">Database Connection Error</h2>
                    <p>Unable to connect to the MySQL database. Please verify the following:</p>
                    <ol style="line-height: 1.6;">
                        <li><strong>XAMPP Control Panel:</strong> Ensure both <strong>Apache</strong> and <strong>MySQL</strong> are started and showing green.</li>
                        <li><strong>Database Exists:</strong> Open <a href="http://localhost/phpmyadmin" target="_blank" style="color: #0056b3;">phpMyAdmin</a> and ensure the database <code>' . htmlspecialchars(DB_NAME) . '</code> has been created and imported from <code>database/database.sql</code>.</li>
                        <li><strong>Credentials:</strong> Check if your MySQL user/password in <code>config/database.php</code> matches your local configuration.</li>
                    </ol>
                </div>
            ');
        }
    }

    return $pdo;
}
