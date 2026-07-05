<?php
/**
 * Reports Page
 * Generate and display reports with filtering options
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Set page title
$pageTitle = 'Reports';

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

// Get filter parameters
$filterType = isset($_GET['filterType']) ? $_GET['filterType'] : 'all';
$filterStatus = isset($_GET['filterStatus']) ? $_GET['filterStatus'] : 'all';
$filterDateFrom = isset($_GET['filterDateFrom']) ? $_GET['filterDateFrom'] : '';
$filterDateTo = isset($_GET['filterDateTo']) ? $_GET['filterDateTo'] : '';

// Build query based on filters
$query = "SELECT oa.id, oa.area_name, oa.number_of_sacks, oa.planting_datetime, 
          oa.harvest_datetime, oa.status, oa.harvest_completed_at 
          FROM oyster_areas oa 
          WHERE 1=1";

$params = [];
$types = '';

// Apply status filter
if ($filterStatus !== 'all') {
    $query .= " AND oa.status = ?";
    $params[] = $filterStatus;
    $types .= 's';
}

// Apply date range filter
if (!empty($filterDateFrom) && !empty($filterDateTo)) {
    $query .= " AND oa.planting_datetime BETWEEN ? AND ?";
    $params[] = $filterDateFrom . ' 00:00:00';
    $params[] = $filterDateTo . ' 23:59:59';
    $types .= 'ss';
} elseif ($filterType !== 'all') {
    // Apply quick date filters
    $now = new DateTime();
    
    switch ($filterType) {
        case 'daily':
            $query .= " AND DATE(oa.planting_datetime) = CURDATE()";
            break;
        case 'monthly':
            $query .= " AND MONTH(oa.planting_datetime) = MONTH(CURDATE()) 
                        AND YEAR(oa.planting_datetime) = YEAR(CURDATE())";
            break;
        case 'yearly':
            $query .= " AND YEAR(oa.planting_datetime) = YEAR(CURDATE())";
            break;
    }
}

$query .= " ORDER BY oa.planting_datetime DESC";

// Execute query
if (!empty($types)) {
    $stmt = executeQuery($conn, $query, $types, $params);
    $result = $stmt ? $stmt->get_result() : null;
} else {
    $result = $conn->query($query);
}

$areas = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $areas[] = $row;
    }
    if (isset($stmt)) {
        $stmt->close();
    } else {
        $result->free();
    }
}

// Include header
include __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3"><i class="bi bi-file-earmark-text"></i> Reports</h1>
            <p class="text-muted">Generate and view reports of oyster farming areas</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0"><i class="bi bi-funnel"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-3">
                    <label for="filterType" class="form-label">Quick Filter</label>
                    <select class="form-select" id="filterType" name="filterType">
                        <option value="all" <?php echo $filterType === 'all' ? 'selected' : ''; ?>>All Time</option>
                        <option value="daily" <?php echo $filterType === 'daily' ? 'selected' : ''; ?>>Today</option>
                        <option value="monthly" <?php echo $filterType === 'monthly' ? 'selected' : ''; ?>>This Month</option>
                        <option value="yearly" <?php echo $filterType === 'yearly' ? 'selected' : ''; ?>>This Year</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filterStatus" class="form-label">Status</label>
                    <select class="form-select" id="filterStatus" name="filterStatus">
                        <option value="all" <?php echo $filterStatus === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="Active" <?php echo $filterStatus === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Ready for Harvest" <?php echo $filterStatus === 'Ready for Harvest' ? 'selected' : ''; ?>>Ready for Harvest</option>
                        <option value="Harvested" <?php echo $filterStatus === 'Harvested' ? 'selected' : ''; ?>>Harvested</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="filterDateFrom" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="filterDateFrom" name="filterDateFrom" value="<?php echo htmlspecialchars($filterDateFrom); ?>">
                </div>

                <div class="col-md-2">
                    <label for="filterDateTo" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="filterDateTo" name="filterDateTo" value="<?php echo htmlspecialchars($filterDateTo); ?>">
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Apply Filters
                    </button>
                    <a href="/pages/reports/index.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Results -->
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-table"></i> Oyster Areas Report</h5>
            <button onclick="window.print()" class="btn btn-light btn-sm">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>
        <div class="card-body">
            <?php if (count($areas) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Area Name</th>
                                <th>Number of Sacks</th>
                                <th>Planting Date</th>
                                <th>Harvest Date</th>
                                <th>Status</th>
                                <th>Harvest Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($areas as $index => $area): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($area['area_name']); ?></td>
                                    <td><?php echo $area['number_of_sacks']; ?></td>
                                    <td><?php echo formatDateTime($area['planting_datetime'], 'M d, Y h:i A'); ?></td>
                                    <td><?php echo formatDateTime($area['harvest_datetime'], 'M d, Y h:i A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadgeClass($area['status']); ?>">
                                            <?php echo htmlspecialchars($area['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($area['harvest_completed_at']) {
                                            echo formatDateTime($area['harvest_completed_at'], 'M d, Y h:i A');
                                        } else {
                                            echo '<span class="text-muted">N/A</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                <div class="mt-3">
                    <strong>Total Records: <?php echo count($areas); ?></strong>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle"></i> No records found matching the selected filters.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Close database connection
closeDBConnection($conn);

// Include footer
include __DIR__ . '/../../includes/footer.php';
?>
