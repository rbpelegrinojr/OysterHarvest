<?php
/**
 * Create New Oyster Area
 * Receives area details and polygon coordinates, creates new area in database
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

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
$areaName = isset($_POST['areaName']) ? trim($_POST['areaName']) : '';
$numberOfSacks = isset($_POST['numberOfSacks']) ? (int)$_POST['numberOfSacks'] : 0;
$plantingDate = isset($_POST['plantingDate']) ? trim($_POST['plantingDate']) : '';
$plantingTime = isset($_POST['plantingTime']) ? trim($_POST['plantingTime']) : '';
$coordinates = isset($_POST['coordinates']) ? $_POST['coordinates'] : '';

// Validate input
if (empty($areaName)) {
    echo json_encode(['success' => false, 'message' => 'Area name is required']);
    closeDBConnection($conn);
    exit;
}

if ($numberOfSacks <= 0) {
    echo json_encode(['success' => false, 'message' => 'Number of sacks must be greater than 0']);
    closeDBConnection($conn);
    exit;
}

if (empty($plantingDate) || empty($plantingTime)) {
    echo json_encode(['success' => false, 'message' => 'Planting date and time are required']);
    closeDBConnection($conn);
    exit;
}

if (empty($coordinates)) {
    echo json_encode(['success' => false, 'message' => 'Polygon coordinates are required']);
    closeDBConnection($conn);
    exit;
}

// Parse coordinates (JSON string)
$coordsArray = json_decode($coordinates, true);
if (!is_array($coordsArray) || count($coordsArray) < 3) {
    echo json_encode(['success' => false, 'message' => 'Invalid polygon coordinates (minimum 3 points required)']);
    closeDBConnection($conn);
    exit;
}

// Combine date and time
$plantingDatetime = $plantingDate . ' ' . $plantingTime . ':00';

// Get average harvest duration setting
$harvestMonths = getAverageHarvestMonths($conn);

// Calculate harvest date
$harvestDatetime = calculateHarvestDate($plantingDatetime, $harvestMonths);

// Start transaction
$conn->begin_transaction();

try {
    // Insert oyster area
    $insertAreaQuery = "INSERT INTO oyster_areas 
                        (area_name, number_of_sacks, planting_datetime, harvest_datetime, status) 
                        VALUES (?, ?, ?, ?, 'Active')";
    
    $stmt = $conn->prepare($insertAreaQuery);
    $stmt->bind_param('siss', $areaName, $numberOfSacks, $plantingDatetime, $harvestDatetime);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to insert area: ' . $stmt->error);
    }
    
    $areaId = $conn->insert_id;
    $stmt->close();
    
    // Insert polygon coordinates
    $insertCoordQuery = "INSERT INTO polygon_coordinates 
                         (area_id, latitude, longitude, sequence_number) 
                         VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insertCoordQuery);
    
    $sequenceNumber = 1;
    foreach ($coordsArray as $coord) {
        $lat = (float)$coord['lat'];
        $lng = (float)$coord['lng'];
        
        $stmt->bind_param('iddi', $areaId, $lat, $lng, $sequenceNumber);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert coordinates: ' . $stmt->error);
        }
        
        $sequenceNumber++;
    }
    
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Area created successfully',
        'area_id' => $areaId,
        'harvest_datetime' => $harvestDatetime
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

closeDBConnection($conn);
?>
