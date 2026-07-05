<?php
/**
 * Dashboard Page
 * Main interface showing map, statistics, recent activities, and notifications
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Set page title
$pageTitle = 'Dashboard';
$includeMapJS = true;

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

// Get statistics
$stats = getStatistics($conn);

// Get recent activities
$recentActivities = getRecentActivities($conn, 5);

// Get ready for harvest notifications
$readyAreas = getReadyForHarvestAreas($conn);

// Include header
include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3"><i class="bi bi-speedometer2"></i> Dashboard</h1>
            <p class="text-muted">Overview of oyster farming areas and operations</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Total Areas</h6>
                            <h2 class="mb-0"><?php echo $stats['total']; ?></h2>
                        </div>
                        <i class="bi bi-map fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Active Areas</h6>
                            <h2 class="mb-0"><?php echo $stats['active']; ?></h2>
                        </div>
                        <i class="bi bi-activity fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Ready for Harvest</h6>
                            <h2 class="mb-0"><?php echo $stats['ready']; ?></h2>
                        </div>
                        <i class="bi bi-clock-history fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Harvested</h6>
                            <h2 class="mb-0"><?php echo $stats['harvested']; ?></h2>
                        </div>
                        <i class="bi bi-check-circle fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <?php if (count($readyAreas) > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
                <h5 class="alert-heading"><i class="bi bi-bell"></i> Harvest Notifications</h5>
                <p class="mb-0">The following areas are ready for harvesting:</p>
                <ul class="mb-0 mt-2">
                    <?php foreach ($readyAreas as $area): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($area['area_name']); ?></strong> 
                            - <?php echo $area['number_of_sacks']; ?> sacks
                            (Harvest date: <?php echo formatDateTime($area['harvest_datetime'], 'M d, Y'); ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Map and Recent Activities -->
    <div class="row">
        <!-- Interactive Map -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-map"></i> Oyster Farming Areas Map</h5>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 600px; width: 100%;"></div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-success" id="createAreaBtn">
                        <i class="bi bi-plus-circle"></i> Create New Area
                    </button>
                    <div class="float-end">
                        <span class="badge bg-primary me-2"><i class="bi bi-circle-fill"></i> Active</span>
                        <span class="badge bg-warning me-2"><i class="bi bi-circle-fill"></i> Ready for Harvest</span>
                        <span class="badge bg-success"><i class="bi bi-circle-fill"></i> Harvested</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities Sidebar -->
        <div class="col-lg-4 mb-4">
            <!-- Recently Created Areas -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0"><i class="bi bi-plus-square"></i> Recently Created Areas</h6>
                </div>
                <div class="card-body">
                    <?php if (count($recentActivities['new_areas']) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentActivities['new_areas'] as $area): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($area['area_name']); ?></h6>
                                        <small class="text-muted"><?php echo formatDateTime($area['created_at'], 'M d'); ?></small>
                                    </div>
                                    <p class="mb-0 small text-muted">
                                        <?php echo $area['number_of_sacks']; ?> sacks planted
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No recent areas created.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recently Harvested Areas -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0"><i class="bi bi-check-square"></i> Recently Harvested Areas</h6>
                </div>
                <div class="card-body">
                    <?php if (count($recentActivities['harvested_areas']) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentActivities['harvested_areas'] as $area): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($area['area_name']); ?></h6>
                                        <small class="text-muted"><?php echo formatDateTime($area['harvest_completed_at'], 'M d'); ?></small>
                                    </div>
                                    <p class="mb-0 small text-muted">
                                        <?php echo $area['number_of_sacks']; ?> sacks harvested
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No recent harvests.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Creating/Editing Area -->
<div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="areaModalLabel">Create New Oyster Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="areaForm">
                    <input type="hidden" id="areaId" name="areaId">
                    <input type="hidden" id="polygonCoordinates" name="polygonCoordinates">
                    
                    <div class="mb-3">
                        <label for="areaName" class="form-label">Area Name</label>
                        <input type="text" class="form-control" id="areaName" name="areaName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="numberOfSacks" class="form-label">Number of Oyster Sacks</label>
                        <input type="number" class="form-control" id="numberOfSacks" name="numberOfSacks" min="1" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="plantingDate" class="form-label">Planting Date</label>
                        <input type="date" class="form-control" id="plantingDate" name="plantingDate" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="plantingTime" class="form-label">Planting Time</label>
                        <input type="time" class="form-control" id="plantingTime" name="plantingTime" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <small><i class="bi bi-info-circle"></i> Harvest date will be calculated automatically based on system settings.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveAreaBtn">Save Area</button>
            </div>
        </div>
    </div>
</div>

<?php
// Close database connection
closeDBConnection($conn);

// Include footer
include __DIR__ . '/../../includes/footer.php';
?>
