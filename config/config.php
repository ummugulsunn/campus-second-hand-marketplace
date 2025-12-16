<?php
declare(strict_types=1);

/**
 * Application Configuration
 */

// Base URL - adjust this if your app is in a subdirectory
// Examples:
//   - Root: define('BASE_URL', '');
//   - Subdirectory: define('BASE_URL', '/campus-marketplace');
define('BASE_URL', '/campus-marketplace');

/**
 * Generate full URL with base path
 * 
 * @param string $path Path relative to app root (e.g., '/pages/login.php')
 * @return string Full URL
 */
function base_url(string $path = ''): string {
    return BASE_URL . $path;
}
