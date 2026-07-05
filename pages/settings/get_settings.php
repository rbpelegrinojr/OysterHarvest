<?php
/**
 * Get Settings API
 * Returns current system settings as JSON
 */

require_once __DIR__ . '/../../config/database.php';

// Set JSON header
header('Content-Type: application/json');

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Get settings
$query = "SELECT average_harvest_months, map_center_latitude, map_center_longitude FROM settings ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $settings = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'settings' => $settings
    ]);
    $result->free();
} else {
    // Return default values if no settings found
    echo json_encode([
        'success' => true,
        'settings' => [
            'average_harvest_months' => 2,
            'map_center_latitude' => 14.5995,
            'map_center_longitude' => 120.9842
        ]
    ]);
}

// Close database connection
closeDBConnection($conn);
?>
