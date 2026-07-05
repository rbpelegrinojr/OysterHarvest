<?php
/**
 * Utility Functions for Oyster Harvest Management System
 * 
 * This file contains reusable functions for business logic,
 * date calculations, and status management.
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Calculate harvest date based on planting datetime and harvest duration
 * 
 * @param string $plantingDatetime Planting date and time in MySQL datetime format
 * @param int $harvestMonths Number of months until harvest
 * @return string Calculated harvest datetime in MySQL datetime format
 */
function calculateHarvestDate($plantingDatetime, $harvestMonths) {
    try {
        $plantingDate = new DateTime($plantingDatetime);
        $plantingDate->add(new DateInterval("P{$harvestMonths}M")); // Add months
        return $plantingDate->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        error_log("Error calculating harvest date: " . $e->getMessage());
        return date('Y-m-d H:i:s');
    }
}

/**
 * Check and update the status of an oyster area based on harvest date
 * 
 * @param mysqli $conn Database connection
 * @param int $areaId ID of the oyster area
 * @return bool True if status was updated, false otherwise
 */
function checkAndUpdateStatus($conn, $areaId) {
    // Get current area status and harvest date
    $query = "SELECT status, harvest_datetime FROM oyster_areas WHERE id = ?";
    $stmt = executeQuery($conn, $query, 'i', [$areaId]);
    
    if (!$stmt) {
        return false;
    }
    
    $result = $stmt->get_result();
    $area = $result->fetch_assoc();
    $stmt->close();
    
    if (!$area) {
        return false;
    }
    
    // Only update if status is 'Active' and harvest date has passed
    if ($area['status'] === 'Active') {
        $currentDateTime = new DateTime();
        $harvestDateTime = new DateTime($area['harvest_datetime']);
        
        if ($currentDateTime >= $harvestDateTime) {
            // Update status to 'Ready for Harvest'
            $updateQuery = "UPDATE oyster_areas SET status = 'Ready for Harvest' WHERE id = ?";
            $updateStmt = executeQuery($conn, $updateQuery, 'i', [$areaId]);
            
            if ($updateStmt) {
                $updateStmt->close();
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Check and update all active areas' statuses
 * 
 * @param mysqli $conn Database connection
 * @return int Number of areas updated
 */
function checkAndUpdateAllStatuses($conn) {
    $updatedCount = 0;
    
    // Get all active areas
    $query = "SELECT id FROM oyster_areas WHERE status = 'Active'";
    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if (checkAndUpdateStatus($conn, $row['id'])) {
                $updatedCount++;
            }
        }
        $result->free();
    }
    
    return $updatedCount;
}

/**
 * Get the current average harvest duration setting
 * 
 * @param mysqli $conn Database connection
 * @return int Average harvest duration in months
 */
function getAverageHarvestMonths($conn) {
    $query = "SELECT average_harvest_months FROM settings ORDER BY id DESC LIMIT 1";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $result->free();
        return (int)$row['average_harvest_months'];
    }
    
    // Default to 2 months if no setting exists
    return 2;
}

/**
 * Get statistics for dashboard display
 * 
 * @param mysqli $conn Database connection
 * @return array Statistics array with counts for each status
 */
function getStatistics($conn) {
    $stats = [
        'total' => 0,
        'active' => 0,
        'ready' => 0,
        'harvested' => 0
    ];
    
    // First, update all statuses
    checkAndUpdateAllStatuses($conn);
    
    // Get counts for each status
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'Ready for Harvest' THEN 1 ELSE 0 END) as ready,
                SUM(CASE WHEN status = 'Harvested' THEN 1 ELSE 0 END) as harvested
              FROM oyster_areas";
    
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stats['total'] = (int)$row['total'];
        $stats['active'] = (int)$row['active'];
        $stats['ready'] = (int)$row['ready'];
        $stats['harvested'] = (int)$row['harvested'];
        $result->free();
    }
    
    return $stats;
}

/**
 * Get recent activities (newly created and recently harvested areas)
 * 
 * @param mysqli $conn Database connection
 * @param int $limit Number of records to return
 * @return array Array containing 'new_areas' and 'harvested_areas'
 */
function getRecentActivities($conn, $limit = 5) {
    $activities = [
        'new_areas' => [],
        'harvested_areas' => []
    ];
    
    // Get recently created areas
    $newAreasQuery = "SELECT id, area_name, number_of_sacks, planting_datetime, created_at 
                      FROM oyster_areas 
                      ORDER BY created_at DESC 
                      LIMIT ?";
    $stmt = executeQuery($conn, $newAreasQuery, 'i', [$limit]);
    
    if ($stmt) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities['new_areas'][] = $row;
        }
        $stmt->close();
    }
    
    // Get recently harvested areas
    $harvestedQuery = "SELECT id, area_name, number_of_sacks, harvest_completed_at 
                       FROM oyster_areas 
                       WHERE status = 'Harvested' AND harvest_completed_at IS NOT NULL
                       ORDER BY harvest_completed_at DESC 
                       LIMIT ?";
    $stmt = executeQuery($conn, $harvestedQuery, 'i', [$limit]);
    
    if ($stmt) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $activities['harvested_areas'][] = $row;
        }
        $stmt->close();
    }
    
    return $activities;
}

/**
 * Get areas ready for harvest (for notifications)
 * 
 * @param mysqli $conn Database connection
 * @return array Array of areas ready for harvest
 */
function getReadyForHarvestAreas($conn) {
    // First update all statuses
    checkAndUpdateAllStatuses($conn);
    
    $query = "SELECT id, area_name, number_of_sacks, planting_datetime, harvest_datetime 
              FROM oyster_areas 
              WHERE status = 'Ready for Harvest'
              ORDER BY harvest_datetime ASC";
    
    $result = $conn->query($query);
    $areas = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $areas[] = $row;
        }
        $result->free();
    }
    
    return $areas;
}

/**
 * Format datetime for display
 * 
 * @param string $datetime MySQL datetime string
 * @param string $format Desired output format
 * @return string Formatted datetime
 */
function formatDateTime($datetime, $format = 'M d, Y h:i A') {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    
    try {
        $dt = new DateTime($datetime);
        return $dt->format($format);
    } catch (Exception $e) {
        return $datetime;
    }
}

/**
 * Get status badge CSS class
 * 
 * @param string $status Status value
 * @return string Bootstrap badge class
 */
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Active':
            return 'bg-primary';
        case 'Ready for Harvest':
            return 'bg-warning';
        case 'Harvested':
            return 'bg-success';
        default:
            return 'bg-secondary';
    }
}

/**
 * Get status color for map polygon
 * 
 * @param string $status Status value
 * @return string Hex color code
 */
function getStatusColor($status) {
    switch ($status) {
        case 'Active':
            return '#0d6efd'; // Blue
        case 'Ready for Harvest':
            return '#fd7e14'; // Orange
        case 'Harvested':
            return '#198754'; // Green
        default:
            return '#6c757d'; // Gray
    }
}
?>
