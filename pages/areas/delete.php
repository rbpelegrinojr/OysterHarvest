<?php
/**
 * Delete Oyster Area
 * Removes an area and its coordinates from the database
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$conn = getDBConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get POST data
$areaId = isset($_POST['areaId']) ? (int)$_POST['areaId'] : 0;

// Validate input
if ($areaId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid area ID']);
    closeDBConnection($conn);
    exit;
}

// Delete area (CASCADE will automatically delete coordinates)
$deleteQuery = "DELETE FROM oyster_areas WHERE id = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param('i', $areaId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Area deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Area not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete area: ' . $stmt->error]);
}

$stmt->close();
closeDBConnection($conn);
?>
