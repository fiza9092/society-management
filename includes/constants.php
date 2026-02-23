<?php
/**
 * Global constants definition
 * Include this file in config.php or any file that needs constants
 */

// Define SITE_URL if not defined
if (!defined('SITE_URL')) {
    if (getenv('RAILWAY_PUBLIC_DOMAIN')) {
        define('SITE_URL', 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN'));
    } else {
        // Auto-detect
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        define('SITE_URL', $protocol . $host);
    }
}

// Define SITE_NAME if not defined
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Society Management System');
}

// Define ADMIN_EMAIL if not defined
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 'admin@society.com');
}
?>