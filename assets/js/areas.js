/**
 * Area Management Functions
 * Handles CRUD operations for oyster areas
 */

/**
 * Edit an existing area
 */
function editArea(areaId) {
    const area = areas.find(a => a.id === areaId);
    
    if (!area) {
        showNotification('Area not found', 'danger');
        return;
    }

    // Populate form with area data
    $('#areaModalLabel').text('Edit Oyster Area');
    $('#areaId').val(area.id);
    $('#areaName').val(area.area_name);
    $('#numberOfSacks').val(area.number_of_sacks);
    
    // Split datetime into date and time
    const plantingDatetime = new Date(area.planting_datetime);
    const plantingDate = plantingDatetime.toISOString().split('T')[0];
    const plantingTime = plantingDatetime.toTimeString().substring(0, 5);
    
    $('#plantingDate').val(plantingDate);
    $('#plantingTime').val(plantingTime);
    
    // Store coordinates (they will be updated if polygon is edited)
    $('#polygonCoordinates').val(JSON.stringify(area.coordinates));

    // Show modal
    $('#areaModal').modal('show');
}

/**
 * Delete an area
 */
function deleteArea(areaId) {
    if (!confirm('Are you sure you want to delete this area? This action cannot be undone.')) {
        return;
    }

    $.ajax({
        url: '/pages/areas/delete.php',
        method: 'POST',
        data: { areaId: areaId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Area deleted successfully', 'success');
                loadAreas();
            } else {
                showNotification('Failed to delete area: ' + response.message, 'danger');
            }
        },
        error: function() {
            showNotification('Error deleting area', 'danger');
        }
    });
}

/**
 * Mark area as harvested
 */
function markAsHarvested(areaId) {
    if (!confirm('Mark this area as harvested?')) {
        return;
    }

    $.ajax({
        url: '/pages/areas/mark_harvested.php',
        method: 'POST',
        data: { areaId: areaId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Area marked as harvested', 'success');
                loadAreas();
            } else {
                showNotification('Failed to mark as harvested: ' + response.message, 'danger');
            }
        },
        error: function() {
            showNotification('Error marking area as harvested', 'danger');
        }
    });
}

/**
 * Save area (create or update)
 */
function saveArea() {
    // Validate form
    const form = $('#areaForm')[0];
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const areaId = $('#areaId').val();
    const areaName = $('#areaName').val();
    const numberOfSacks = $('#numberOfSacks').val();
    const plantingDate = $('#plantingDate').val();
    const plantingTime = $('#plantingTime').val();
    const coordinates = $('#polygonCoordinates').val();

    // Validate coordinates
    if (!coordinates || coordinates === '[]') {
        showNotification('Please draw a polygon on the map', 'warning');
        return;
    }

    const url = areaId ? '/pages/areas/edit.php' : '/pages/areas/create.php';
    const data = {
        areaName: areaName,
        numberOfSacks: numberOfSacks,
        plantingDate: plantingDate,
        plantingTime: plantingTime,
        coordinates: coordinates
    };

    if (areaId) {
        data.areaId = areaId;
    }

    // Disable save button
    $('#saveAreaBtn').prop('disabled', true).text('Saving...');

    $.ajax({
        url: url,
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification(areaId ? 'Area updated successfully' : 'Area created successfully', 'success');
                
                // If creating new area, add the polygon to the map
                if (!areaId && currentPolygonLayer) {
                    drawnItems.addLayer(currentPolygonLayer);
                    currentPolygonLayer = null;
                }
                
                // Close modal and reset form
                $('#areaModal').modal('hide');
                $('#areaForm')[0].reset();
                $('#areaId').val('');
                $('#polygonCoordinates').val('');
                
                // Reload areas
                loadAreas();
            } else {
                showNotification('Failed to save area: ' + response.message, 'danger');
            }
        },
        error: function() {
            showNotification('Error saving area', 'danger');
        },
        complete: function() {
            $('#saveAreaBtn').prop('disabled', false).text('Save Area');
        }
    });
}

/**
 * Show notification message
 */
function showNotification(message, type) {
    // Create alert element
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" role="alert" style="z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Add to body
    $('body').append(alertHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}
