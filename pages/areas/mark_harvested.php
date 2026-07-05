<?php
/**
 * Mark Area as Harvested
 * Updates area status to 'Harvested' and records harvest completion datetime
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

// Check if area exists and is ready for harvest
$checkQuery = "SELECT status FROM oyster_areas WHERE id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param('i', $areaId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Area not found']);
    $stmt->close();
    closeDBConnection($conn);
    exit;
}

$area = $result->fetch_assoc();
$stmt->close();

// Update status to 'Harvested' and set harvest completion datetime
$currentDatetime = date('Y-m-d H:i:s');
$updateQuery = "UPDATE oyster_areas 
                SET status = 'Harvested', 
                    harvest_completed_at = ? 
                WHERE id = ?";

$stmt = $conn->prepare($updateQuery);
$stmt->bind_param('si', $currentDatetime, $areaId);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Area marked as harvested successfully',
        'harvest_completed_at' => $currentDatetime
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update area: ' . $stmt->error]);
}

$stmt->close();
closeDBConnection($conn);
?>
