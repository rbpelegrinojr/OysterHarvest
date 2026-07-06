<?php
/**
 * Application Configuration File
 * 
 * This file contains application-wide configuration settings including
 * base URL configuration for proper routing in different environments.
 */

/**
 * Base URL Configuration
 * 
 * IMPORTANT: Configure this based on your environment
 * 
 * For localhost XAMPP in subdirectory:
 *   define('BASE_URL', '/oyster');
 * 
 * For shared hosting in document root:
 *   define('BASE_URL', '');
 * 
 * For subdomain (e.g., app.example.com):
 *   define('BASE_URL', '');
 */

// Auto-detect base URL based on the script location
// This works for most standard setups
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);

// Sanitize and validate the script path
$scriptPath = str_replace('\\', '/', $scriptPath); // Normalize path separators
$scriptPath = preg_replace('#/+#', '/', $scriptPath); // Remove multiple slashes

$basePath = ($scriptPath === '/' || $scriptPath === '\\') ? '' : $scriptPath;

// Remove trailing slash if present
$basePath = rtrim($basePath, '/\\');

define('BASE_URL', $basePath);

/**
 * Helper function to generate proper URLs
 * 
 * @param string $path Path relative to application root (e.g., '/pages/dashboard/index.php')
 * @return string Full URL path with base URL prepended
 */
function url($path = '') {
    // Ensure path starts with /
    if (!empty($path) && $path[0] !== '/') {
        $path = '/' . $path;
    }
    
    return BASE_URL . $path;
}

/**
 * Helper function to generate asset URLs
 * 
 * @param string $path Path relative to assets directory (e.g., 'css/style.css')
 * @return string Full URL path to asset
 */
function asset($path) {
    // Remove leading slash if present
    $path = ltrim($path, '/');
    
    return BASE_URL . '/assets/' . $path;
}
?>
