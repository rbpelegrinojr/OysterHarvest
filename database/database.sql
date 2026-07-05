-- ============================================================
-- Oyster Farm Monitoring and Harvest Management System
-- Database Schema
-- ============================================================

-- Create database
CREATE DATABASE IF NOT EXISTS oyster_harvest_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE oyster_harvest_db;

-- ============================================================
-- Table: settings
-- Purpose: Store system-wide configuration parameters
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    average_harvest_months INT NOT NULL DEFAULT 2 COMMENT 'Average duration in months from planting to harvest',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: oyster_areas
-- Purpose: Store oyster farming area metadata
-- ============================================================
CREATE TABLE IF NOT EXISTS oyster_areas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    area_name VARCHAR(255) NOT NULL COMMENT 'Name of the oyster farming area',
    number_of_sacks INT NOT NULL COMMENT 'Number of oyster sacks planted in this area',
    planting_datetime DATETIME NOT NULL COMMENT 'Date and time when oysters were planted',
    harvest_datetime DATETIME NOT NULL COMMENT 'Calculated harvest date based on planting date and average harvest duration',
    status ENUM('Active', 'Ready for Harvest', 'Harvested') DEFAULT 'Active' COMMENT 'Current status of the oyster area',
    harvest_completed_at DATETIME NULL COMMENT 'Actual date and time when harvest was completed',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_harvest_datetime (harvest_datetime),
    INDEX idx_planting_datetime (planting_datetime)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: polygon_coordinates
-- Purpose: Store polygon vertex coordinates for map visualization
-- Relationship: Many coordinates belong to one oyster area
-- ============================================================
CREATE TABLE IF NOT EXISTS polygon_coordinates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    area_id INT NOT NULL COMMENT 'Foreign key reference to oyster_areas table',
    latitude DECIMAL(10, 8) NOT NULL COMMENT 'Latitude coordinate of polygon vertex',
    longitude DECIMAL(11, 8) NOT NULL COMMENT 'Longitude coordinate of polygon vertex',
    sequence_number INT NOT NULL COMMENT 'Order of the vertex in the polygon (for drawing)',
    FOREIGN KEY (area_id) REFERENCES oyster_areas(id) ON DELETE CASCADE,
    INDEX idx_area_id (area_id),
    INDEX idx_area_sequence (area_id, sequence_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Initial Data
-- ============================================================

-- Insert default settings
INSERT INTO settings (average_harvest_months) VALUES (2);

-- ============================================================
-- Sample Data (for testing purposes)
-- ============================================================

-- Sample Area 1: Active area in Manila Bay
INSERT INTO oyster_areas (area_name, number_of_sacks, planting_datetime, harvest_datetime, status)
VALUES (
    'Manila Bay North Zone',
    150,
    '2026-06-01 08:00:00',
    '2026-08-01 08:00:00',
    'Active'
);

SET @area1_id = LAST_INSERT_ID();

-- Polygon coordinates for Manila Bay North Zone (approximate rectangle)
INSERT INTO polygon_coordinates (area_id, latitude, longitude, sequence_number) VALUES
(@area1_id, 14.5995, 120.9842, 1),
(@area1_id, 14.6005, 120.9842, 2),
(@area1_id, 14.6005, 120.9852, 3),
(@area1_id, 14.5995, 120.9852, 4);

-- Sample Area 2: Ready for Harvest (planted 3 months ago)
INSERT INTO oyster_areas (area_name, number_of_sacks, planting_datetime, harvest_datetime, status)
VALUES (
    'Bacoor Bay Area',
    200,
    '2026-04-05 09:30:00',
    '2026-06-05 09:30:00',
    'Ready for Harvest'
);

SET @area2_id = LAST_INSERT_ID();

-- Polygon coordinates for Bacoor Bay Area
INSERT INTO polygon_coordinates (area_id, latitude, longitude, sequence_number) VALUES
(@area2_id, 14.5800, 120.9750, 1),
(@area2_id, 14.5810, 120.9750, 2),
(@area2_id, 14.5810, 120.9765, 3),
(@area2_id, 14.5800, 120.9765, 4);

-- Sample Area 3: Harvested area
INSERT INTO oyster_areas (area_name, number_of_sacks, planting_datetime, harvest_datetime, status, harvest_completed_at)
VALUES (
    'Coastal Zone Alpha',
    100,
    '2026-02-15 10:00:00',
    '2026-04-15 10:00:00',
    'Harvested',
    '2026-04-20 14:30:00'
);

SET @area3_id = LAST_INSERT_ID();

-- Polygon coordinates for Coastal Zone Alpha
INSERT INTO polygon_coordinates (area_id, latitude, longitude, sequence_number) VALUES
(@area3_id, 14.5900, 120.9800, 1),
(@area3_id, 14.5920, 120.9800, 2),
(@area3_id, 14.5920, 120.9820, 3),
(@area3_id, 14.5910, 120.9830, 4),
(@area3_id, 14.5900, 120.9820, 5);

-- ============================================================
-- Database Schema Complete
-- ============================================================
