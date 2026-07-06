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

// Auto-detect base URL based on the application root directory.
// Using __DIR__ (which is always {app_root}/config) is reliable regardless
// of which page is currently being served, unlike SCRIPT_NAME which changes
// per request and would produce wrong paths for pages in subdirectories.
$appRoot = realpath(dirname(__DIR__));
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT']);

// Normalize path separators (important on Windows)
$appRoot = str_replace('\\', '/', $appRoot);
$documentRoot = str_replace('\\', '/', $documentRoot);

// Calculate the base path relative to document root
$basePath = str_replace($documentRoot, '', $appRoot);
$basePath = str_replace('\\', '/', $basePath);

// Remove trailing slash if present
$basePath = rtrim($basePath, '/');

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
