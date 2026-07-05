<?php
/**
 * Edit Oyster Area
 * Updates area details and optionally polygon coordinates
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
$areaId = isset($_POST['areaId']) ? (int)$_POST['areaId'] : 0;
$areaName = isset($_POST['areaName']) ? trim($_POST['areaName']) : '';
$numberOfSacks = isset($_POST['numberOfSacks']) ? (int)$_POST['numberOfSacks'] : 0;
$plantingDate = isset($_POST['plantingDate']) ? trim($_POST['plantingDate']) : '';
$plantingTime = isset($_POST['plantingTime']) ? trim($_POST['plantingTime']) : '';
$coordinates = isset($_POST['coordinates']) ? $_POST['coordinates'] : null;

// Validate input
if ($areaId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid area ID']);
    closeDBConnection($conn);
    exit;
}

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

// Combine date and time
$plantingDatetime = $plantingDate . ' ' . $plantingTime . ':00';

// Get average harvest duration setting
$harvestMonths = getAverageHarvestMonths($conn);

// Calculate new harvest date
$harvestDatetime = calculateHarvestDate($plantingDatetime, $harvestMonths);

// Start transaction
$conn->begin_transaction();

try {
    // Update oyster area
    $updateAreaQuery = "UPDATE oyster_areas 
                        SET area_name = ?, 
                            number_of_sacks = ?, 
                            planting_datetime = ?, 
                            harvest_datetime = ?
                        WHERE id = ?";
    
    $stmt = $conn->prepare($updateAreaQuery);
    $stmt->bind_param('sissi', $areaName, $numberOfSacks, $plantingDatetime, $harvestDatetime, $areaId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update area: ' . $stmt->error);
    }
    
    $stmt->close();
    
    // Update polygon coordinates if provided
    if ($coordinates !== null && !empty($coordinates)) {
        $coordsArray = json_decode($coordinates, true);
        
        if (is_array($coordsArray) && count($coordsArray) >= 3) {
            // Delete existing coordinates
            $deleteCoordQuery = "DELETE FROM polygon_coordinates WHERE area_id = ?";
            $stmt = $conn->prepare($deleteCoordQuery);
            $stmt->bind_param('i', $areaId);
            $stmt->execute();
            $stmt->close();
            
            // Insert new coordinates
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
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Area updated successfully',
        'harvest_datetime' => $harvestDatetime
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

closeDBConnection($conn);
?>
