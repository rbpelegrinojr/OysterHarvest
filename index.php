<?php
/**
 * Entry Point for Oyster Harvest Management System
 * Redirects to the dashboard
 */

// Load configuration
require_once __DIR__ . '/config/config.php';

// Redirect to dashboard
header('Location: ' . url('/pages/dashboard/index.php'));
exit;
?>
