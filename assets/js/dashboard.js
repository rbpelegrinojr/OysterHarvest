/**
 * Dashboard Initialization
 * Handles dashboard-specific functionality
 */

$(document).ready(function() {
    // Initialize map if on dashboard page
    if ($('#map').length) {
        initializeMap();
    }

    // Handle create area button click
    $('#createAreaBtn').on('click', function() {
        // Enable polygon drawing
        const polygonDrawer = new L.Draw.Polygon(map, drawControl.options.draw.polygon);
        polygonDrawer.enable();
    });

    // Handle save area button click
    $('#saveAreaBtn').on('click', function() {
        saveArea();
    });

    // Handle modal close - remove temporary polygon if not saved
    $('#areaModal').on('hidden.bs.modal', function() {
        if (currentPolygonLayer && !$('#areaId').val()) {
            // Remove the polygon if it wasn't saved
            currentPolygonLayer = null;
        }
    });

    // Auto-refresh data every 5 minutes
    setInterval(function() {
        if (typeof loadAreas === 'function') {
            loadAreas();
        }
    }, 300000); // 5 minutes
});
