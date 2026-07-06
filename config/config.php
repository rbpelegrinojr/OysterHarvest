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
$appRoot = dirname(__DIR__);
$documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';

// Normalize path separators (important on Windows/Ubuntu)
$appRoot = str_replace('\\', '/', $appRoot);
$documentRoot = str_replace('\\', '/', rtrim($documentRoot, '/\\'));

// Calculate the base path relative to document root.
// First try raw paths (works for most setups without symlinks).
if ($documentRoot !== '' && strpos($appRoot, $documentRoot) === 0) {
    $basePath = substr($appRoot, strlen($documentRoot));
} else {
    // Try realpath to resolve symlinks and normalize paths (Windows, some Ubuntu setups).
    $realAppRoot = realpath(dirname(__DIR__));
    $realDocRoot = ($documentRoot !== '') ? realpath($documentRoot) : false;

    if ($realAppRoot !== false && $realDocRoot !== false && $realDocRoot !== '') {
        $realAppRootNorm = str_replace('\\', '/', $realAppRoot);
        $realDocRootNorm = str_replace('\\', '/', $realDocRoot);
        if (strpos($realAppRootNorm, $realDocRootNorm) === 0) {
            $basePath = substr($realAppRootNorm, strlen($realDocRootNorm));
        } else {
            $basePath = '';
        }
    } else {
        // Cannot reliably determine base path. Default to '' (app served from document root).
        // This is the most common production deployment setup.
        // If your app is in a subdirectory (e.g. /app), set BASE_URL manually below.
        $basePath = '';
    }
}

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
