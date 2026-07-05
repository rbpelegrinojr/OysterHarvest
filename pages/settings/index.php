<?php
/**
 * Settings Page
 * Configure system settings including average harvest duration
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Set page title
$pageTitle = 'Settings';

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['saveSettings'])) {
    $averageHarvestMonths = isset($_POST['averageHarvestMonths']) ? (int)$_POST['averageHarvestMonths'] : 2;
    
    if ($averageHarvestMonths < 1) {
        $message = 'Average harvest duration must be at least 1 month.';
        $messageType = 'danger';
    } else {
        // Update settings
        $updateQuery = "UPDATE settings SET average_harvest_months = ? WHERE id = 1";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('i', $averageHarvestMonths);
        
        if ($stmt->execute()) {
            $message = 'Settings saved successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to save settings: ' . $stmt->error;
            $messageType = 'danger';
        }
        
        $stmt->close();
    }
}

// Get current settings
$currentSettings = [
    'average_harvest_months' => 2
];

$query = "SELECT average_harvest_months FROM settings ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $currentSettings = $result->fetch_assoc();
    $result->free();
}

// Include header
include __DIR__ . '/../../includes/header.php';
?>

<div class="container py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3"><i class="bi bi-gear"></i> System Settings</h1>
            <p class="text-muted">Configure system parameters and preferences</p>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="row">
        <div class="col-lg-8">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0"><i class="bi bi-sliders"></i> Harvest Configuration</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="averageHarvestMonths" class="form-label">
                                Average Harvest Duration (Months)
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="averageHarvestMonths" 
                                   name="averageHarvestMonths" 
                                   value="<?php echo $currentSettings['average_harvest_months']; ?>" 
                                   min="1" 
                                   max="24"
                                   required>
                            <div class="form-text">
                                This value is used to automatically calculate harvest dates for all new oyster areas.
                                For example, if set to 2 months, an area planted on July 5, 2026 will have a harvest date of September 5, 2026.
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Note:</strong> Changing this value will only affect newly created areas and edited areas. 
                            Existing areas will not be automatically recalculated.
                        </div>

                        <button type="submit" name="saveSettings" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Settings
                        </button>
                        <a href="/pages/dashboard/index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Information Panel -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="card-title mb-0"><i class="bi bi-info-circle"></i> About Settings</h6>
                </div>
                <div class="card-body">
                    <h6>Harvest Duration</h6>
                    <p class="small text-muted">
                        The average harvest duration defines how long it typically takes for oysters to mature 
                        from planting to harvest. This setting helps the system automatically calculate when 
                        areas will be ready for harvesting.
                    </p>
                    
                    <h6 class="mt-3">Automatic Calculations</h6>
                    <p class="small text-muted">
                        When you create a new oyster area, the system automatically calculates the harvest date by:
                    </p>
                    <ol class="small text-muted">
                        <li>Taking the planting date and time</li>
                        <li>Adding the average harvest duration (in months)</li>
                        <li>Setting the harvest date accordingly</li>
                    </ol>

                    <h6 class="mt-3">Status Updates</h6>
                    <p class="small text-muted mb-0">
                        The system automatically checks if areas are ready for harvest by comparing the current 
                        date with the calculated harvest date. When an area reaches its harvest date, its status 
                        automatically changes to "Ready for Harvest".
                    </p>
                </div>
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
