/**
 * Map Initialization and Management
 * Handles Leaflet map setup, polygon drawing, and editing
 */

// Global variables
let map;
let drawnItems;
let drawControl;
let currentPolygonLayer = null;
let areas = [];

/**
 * Initialize the Leaflet map
 */
function initializeMap() {
    // Fetch settings from the server to get map center coordinates
    $.ajax({
        url: '/pages/settings/get_settings.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            let centerLat = 14.5995;
            let centerLng = 120.9842;
            
            if (response.success && response.settings) {
                centerLat = parseFloat(response.settings.map_center_latitude) || centerLat;
                centerLng = parseFloat(response.settings.map_center_longitude) || centerLng;
            }
            
            // Create map centered on configured coordinates
            map = L.map('map').setView([centerLat, centerLng], 13);

            // Add satellite tile layer with labels (using ESRI World Imagery + OpenStreetMap labels)
            // Base satellite layer
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
                maxZoom: 19
            }).addTo(map);

            // Overlay with labels
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19,
                opacity: 0 // Make the base map transparent, only showing labels
            }).addTo(map);

            // Alternative: Use labels overlay from CartoDB
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
                maxZoom: 19,
                pane: 'shadowPane'
            }).addTo(map);

            // Initialize FeatureGroup to store drawn items
            drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            // Initialize draw control
            drawControl = new L.Control.Draw({
                position: 'topright',
                draw: {
                    polygon: {
                        allowIntersection: false,
                        drawError: {
                            color: '#e1e100',
                            message: '<strong>Error:</strong> Polygon cannot intersect!'
                        },
                        shapeOptions: {
                            color: '#91DDF2'
                        }
                    },
                    polyline: false,
                    circle: false,
                    rectangle: false,
                    marker: false,
                    circlemarker: false
                },
                edit: {
                    featureGroup: drawnItems,
                    remove: false
                }
            });

            map.addControl(drawControl);

            // Handle polygon creation
            map.on(L.Draw.Event.CREATED, function (event) {
                const layer = event.layer;
                currentPolygonLayer = layer;
                
                // Get coordinates
                const coordinates = layer.getLatLngs()[0].map(latlng => ({
                    lat: latlng.lat,
                    lng: latlng.lng
                }));

                // Store coordinates in hidden field
                $('#polygonCoordinates').val(JSON.stringify(coordinates));

                // Open modal for area details
                $('#areaModalLabel').text('Create New Oyster Area');
                $('#areaId').val('');
                $('#areaForm')[0].reset();
                $('#areaModal').modal('show');
            });

            // Handle polygon editing
            map.on(L.Draw.Event.EDITED, function (event) {
                const layers = event.layers;
                layers.eachLayer(function (layer) {
                    const areaId = layer.options.areaId;
                    if (areaId) {
                        const coordinates = layer.getLatLngs()[0].map(latlng => ({
                            lat: latlng.lat,
                            lng: latlng.lng
                        }));

                        // Update area with new coordinates
                        updateAreaCoordinates(areaId, coordinates);
                    }
                });
            });

            // Load existing areas
            loadAreas();
        },
        error: function(xhr, status, error) {
            console.error('Error loading settings:', error);
            // Fallback to default coordinates if settings cannot be loaded
            initializeMapWithDefaults();
        }
    });
}

/**
 * Fallback function to initialize map with default coordinates
 */
function initializeMapWithDefaults() {
    // Create map centered on Manila Bay (Philippines) as fallback
    map = L.map('map').setView([14.5995, 120.9842], 13);

    // Add satellite tile layer with labels
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
        maxZoom: 19
    }).addTo(map);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attributions">CARTO</a>',
        maxZoom: 19,
        pane: 'shadowPane'
    }).addTo(map);

    // Initialize FeatureGroup to store drawn items
    drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    // Initialize draw control
    drawControl = new L.Control.Draw({
        position: 'topright',
        draw: {
            polygon: {
                allowIntersection: false,
                drawError: {
                    color: '#e1e100',
                    message: '<strong>Error:</strong> Polygon cannot intersect!'
                },
                shapeOptions: {
                    color: '#91DDF2'
                }
            },
            polyline: false,
            circle: false,
            rectangle: false,
            marker: false,
            circlemarker: false
        },
        edit: {
            featureGroup: drawnItems,
            remove: false
        }
    });

    map.addControl(drawControl);

    // Handle polygon creation
    map.on(L.Draw.Event.CREATED, function (event) {
        const layer = event.layer;
        currentPolygonLayer = layer;
        
        // Get coordinates
        const coordinates = layer.getLatLngs()[0].map(latlng => ({
            lat: latlng.lat,
            lng: latlng.lng
        }));

        // Store coordinates in hidden field
        $('#polygonCoordinates').val(JSON.stringify(coordinates));

        // Open modal for area details
        $('#areaModalLabel').text('Create New Oyster Area');
        $('#areaId').val('');
        $('#areaForm')[0].reset();
        $('#areaModal').modal('show');
    });

    // Handle polygon editing
    map.on(L.Draw.Event.EDITED, function (event) {
        const layers = event.layers;
        layers.eachLayer(function (layer) {
            const areaId = layer.options.areaId;
            if (areaId) {
                const coordinates = layer.getLatLngs()[0].map(latlng => ({
                    lat: latlng.lat,
                    lng: latlng.lng
                }));

                // Update area with new coordinates
                updateAreaCoordinates(areaId, coordinates);
            }
        });
    });

    // Load existing areas
    loadAreas();
}

/**
 * Get status color for polygon
 */
function getStatusColor(status) {
    switch (status) {
        case 'Active':
            return '#91DDF2'; // New theme color
        case 'Ready for Harvest':
            return '#fd7e14'; // Orange
        case 'Harvested':
            return '#198754'; // Green
        default:
            return '#6c757d'; // Gray
    }
}

/**
 * Load all areas from database and display on map
 */
function loadAreas() {
    $.ajax({
        url: '/pages/areas/get_areas.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                areas = response.areas;
                displayAreasOnMap();
            } else {
                console.error('Failed to load areas');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading areas:', error);
        }
    });
}

/**
 * Display areas on map with appropriate styling
 */
function displayAreasOnMap() {
    // Clear existing layers
    drawnItems.clearLayers();

    // Add each area to map
    areas.forEach(function(area) {
        if (area.coordinates && area.coordinates.length >= 3) {
            const latlngs = area.coordinates.map(coord => [coord.lat, coord.lng]);
            
            const polygon = L.polygon(latlngs, {
                color: getStatusColor(area.status),
                fillColor: getStatusColor(area.status),
                fillOpacity: 0.3,
                weight: 2,
                areaId: area.id
            });

            // Add popup with area information
            const popupContent = createPopupContent(area);
            polygon.bindPopup(popupContent);

            // Add to drawn items
            drawnItems.addLayer(polygon);
        }
    });
}

/**
 * Create popup content for area
 */
function createPopupContent(area) {
    const plantingDate = new Date(area.planting_datetime).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    const harvestDate = new Date(area.harvest_datetime).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });

    let statusBadge = '';
    switch (area.status) {
        case 'Active':
            statusBadge = '<span class="badge bg-primary">Active</span>';
            break;
        case 'Ready for Harvest':
            statusBadge = '<span class="badge bg-warning">Ready for Harvest</span>';
            break;
        case 'Harvested':
            statusBadge = '<span class="badge bg-success">Harvested</span>';
            break;
    }

    let content = `
        <div style="min-width: 250px;">
            <h6 class="mb-2">${escapeHtml(area.area_name)}</h6>
            <p class="mb-1"><strong>Status:</strong> ${statusBadge}</p>
            <p class="mb-1"><strong>Sacks:</strong> ${area.number_of_sacks}</p>
            <p class="mb-1"><strong>Planted:</strong> ${plantingDate}</p>
            <p class="mb-1"><strong>Harvest Date:</strong> ${harvestDate}</p>
    `;

    if (area.harvest_completed_at) {
        const completedDate = new Date(area.harvest_completed_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        content += `<p class="mb-1"><strong>Completed:</strong> ${completedDate}</p>`;
    }

    content += `<div class="mt-3">`;

    // Add action buttons based on status
    if (area.status === 'Ready for Harvest') {
        content += `
            <button class="btn btn-success btn-sm me-1" onclick="markAsHarvested(${area.id})">
                <i class="bi bi-check-circle"></i> Mark Harvested
            </button>
        `;
    }

    content += `
            <button class="btn btn-primary btn-sm me-1" onclick="editArea(${area.id})">
                <i class="bi bi-pencil"></i> Edit
            </button>
            <button class="btn btn-danger btn-sm" onclick="deleteArea(${area.id})">
                <i class="bi bi-trash"></i> Delete
            </button>
        </div>
        </div>
    `;

    return content;
}

/**
 * Update area coordinates after editing polygon
 */
function updateAreaCoordinates(areaId, coordinates) {
    $.ajax({
        url: '/pages/areas/edit.php',
        method: 'POST',
        data: {
            areaId: areaId,
            coordinates: JSON.stringify(coordinates),
            // We need to send the other required fields as well
            // Get them from the current area data
            areaName: areas.find(a => a.id === areaId).area_name,
            numberOfSacks: areas.find(a => a.id === areaId).number_of_sacks,
            plantingDate: areas.find(a => a.id === areaId).planting_datetime.split(' ')[0],
            plantingTime: areas.find(a => a.id === areaId).planting_datetime.split(' ')[1].substring(0, 5)
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification('Area updated successfully', 'success');
                loadAreas();
            } else {
                showNotification('Failed to update area: ' + response.message, 'danger');
            }
        },
        error: function() {
            showNotification('Error updating area', 'danger');
        }
    });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
