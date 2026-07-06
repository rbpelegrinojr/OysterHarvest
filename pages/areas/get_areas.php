<?php
/**
 * Get All Oyster Areas with Coordinates
 * Returns JSON array of all areas with their polygon coordinates
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$conn = getDBConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Update all statuses before fetching
checkAndUpdateAllStatuses($conn);

// Get all oyster areas except harvested ones (harvested areas are hidden from the map)
$areasQuery = "SELECT id, area_name, number_of_sacks, planting_datetime, harvest_datetime, 
               status, harvest_completed_at, created_at 
               FROM oyster_areas 
               WHERE status != 'Harvested'
               ORDER BY created_at DESC";

$result = $conn->query($areasQuery);

$areas = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $areaId = $row['id'];
        
        // Get polygon coordinates for this area
        $coordsQuery = "SELECT latitude, longitude, sequence_number 
                        FROM polygon_coordinates 
                        WHERE area_id = ? 
                        ORDER BY sequence_number ASC";
        
        $stmt = executeQuery($conn, $coordsQuery, 'i', [$areaId]);
        
        $coordinates = [];
        if ($stmt) {
            $coordResult = $stmt->get_result();
            while ($coord = $coordResult->fetch_assoc()) {
                $coordinates[] = [
                    'lat' => (float)$coord['latitude'],
                    'lng' => (float)$coord['longitude']
                ];
            }
            $stmt->close();
        }
        
        // Add area with coordinates to result
        $areas[] = [
            'id' => $row['id'],
            'area_name' => $row['area_name'],
            'number_of_sacks' => (int)$row['number_of_sacks'],
            'planting_datetime' => $row['planting_datetime'],
            'harvest_datetime' => $row['harvest_datetime'],
            'status' => $row['status'],
            'harvest_completed_at' => $row['harvest_completed_at'],
            'created_at' => $row['created_at'],
            'coordinates' => $coordinates
        ];
    }
    $result->free();
}

closeDBConnection($conn);

echo json_encode(['success' => true, 'areas' => $areas]);
?>
