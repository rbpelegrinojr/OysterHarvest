# Oyster Farm Monitoring and Harvest Management System

A web-based management system for oyster farming operations that enables farmers to visually manage farming areas using interactive maps with polygon zones, track planting and harvest schedules, and generate comprehensive reports.

## 📋 Table of Contents

- [System Description](#system-description)
- [Features](#features)
- [Requirements](#requirements)
- [Installation Guide](#installation-guide)
- [Database Setup](#database-setup)
- [Configuration](#configuration)
- [Running the System](#running-the-system)
- [User Guide](#user-guide)
- [Folder Structure](#folder-structure)
- [Technology Stack](#technology-stack)
- [Troubleshooting](#troubleshooting)
- [License](#license)

---

## 🎯 System Description

The Oyster Farm Monitoring and Harvest Management System is a comprehensive web application designed to help oyster farmers manage their farming areas efficiently. The system provides an interactive map interface where users can create, edit, and monitor polygon-based farming zones. It automatically calculates harvest dates based on configurable parameters and provides visual indicators for area status.

**Key Capabilities:**
- Interactive map-based area management using OpenStreetMap and Leaflet.js
- Automated harvest date calculation
- Automatic status tracking (Active → Ready for Harvest → Harvested)
- Real-time notifications for areas ready for harvesting
- Comprehensive reporting with multiple filter options
- Historical tracking of all farming activities

---

## ✨ Features

### 1. **Interactive Map Management**
- Create oyster farming areas by drawing polygons on an interactive map
- Edit polygon shapes and boundaries
- View all areas with color-coded status indicators:
  - 🔵 **Blue**: Active areas
  - 🟠 **Orange**: Ready for harvest
  - 🟢 **Green**: Harvested areas
- Click on polygons to view detailed information and perform actions

### 2. **Automated Harvest Calculations**
- Configurable average harvest duration (in months)
- Automatic harvest date calculation: `Harvest Date = Planting Date + Average Duration`
- Automatic status updates when harvest date is reached
- Recalculation when planting dates are modified

### 3. **Area Information Management**
- Area name
- Number of oyster sacks planted
- Planting date and time
- Calculated harvest date
- Current status tracking
- Harvest completion timestamp

### 4. **Dashboard with Real-Time Statistics**
- Total number of farming areas
- Count of active areas
- Count of areas ready for harvest
- Count of harvested areas
- Recent activities feed
- Harvest notifications

### 5. **Comprehensive Reports**
- View all oyster areas in tabular format
- Filter by:
  - Date range (Daily, Monthly, Yearly, Custom)
  - Status (Active, Ready for Harvest, Harvested)
- Printable reports with optimized layout
- Export-ready format

### 6. **Settings Management**
- Configure average harvest duration
- System-wide settings that affect all calculations
- Clear documentation of setting impacts

---

## 💻 Requirements

### Server Requirements
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: Version 7.4 or higher (8.0+ recommended)
- **MySQL**: Version 5.7 or higher (8.0+ recommended)
- **PHP Extensions**:
  - mysqli
  - json
  - session

### Client Requirements
- Modern web browser (Chrome 90+, Firefox 88+, Edge 90+, Safari 14+)
- JavaScript enabled
- Minimum screen resolution: 1024x768 (1920x1080 recommended)
- Internet connection (for map tiles and CDN resources)

### Recommended Server Configuration
- **Memory**: 256 MB minimum, 512 MB recommended
- **Storage**: 100 MB minimum
- **PHP Settings**:
  ```ini
  max_execution_time = 300
  memory_limit = 256M
  upload_max_filesize = 10M
  post_max_size = 10M
  ```

---

## 📥 Installation Guide

### Step 1: Download the System

Clone the repository or download the ZIP file:

```bash
git clone https://github.com/yourusername/OysterHarvest.git
cd OysterHarvest
```

Or download and extract the ZIP file to your web server directory.

### Step 2: Set Up Web Server

#### For Apache (XAMPP/WAMP/LAMP):

1. Copy the project folder to your web server's document root:
   - **XAMPP**: `C:\xampp\htdocs\OysterHarvest`
   - **WAMP**: `C:\wamp64\www\OysterHarvest`
   - **Linux**: `/var/www/html/OysterHarvest`

2. Ensure Apache and MySQL services are running

3. Create a virtual host (optional but recommended):
   ```apache
   <VirtualHost *:80>
       ServerName oysterharvest.local
       DocumentRoot "C:/xampp/htdocs/OysterHarvest"
       <Directory "C:/xampp/htdocs/OysterHarvest">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

4. Update your hosts file:
   ```
   127.0.0.1 oysterharvest.local
   ```

#### For Nginx:

Create a server block configuration:

```nginx
server {
    listen 80;
    server_name oysterharvest.local;
    root /var/www/html/OysterHarvest;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 🗄️ Database Setup

### Step 1: Create Database

1. Open phpMyAdmin or your preferred MySQL client
2. Access MySQL via command line:
   ```bash
   mysql -u root -p
   ```

3. The database will be created automatically when you import the SQL file

### Step 2: Import Database Schema

#### Using phpMyAdmin:
1. Open phpMyAdmin in your browser (usually `http://localhost/phpmyadmin`)
2. Click "Import" in the top menu
3. Click "Choose File" and select `database/database.sql`
4. Click "Go" to import

#### Using Command Line:
```bash
mysql -u root -p < database/database.sql
```

### Step 3: Verify Import

The following should be created:
- **Database**: `oyster_harvest_db`
- **Tables**:
  - `settings` (1 row with default configuration)
  - `oyster_areas` (3 sample areas for testing)
  - `polygon_coordinates` (sample polygon data)

---

## ⚙️ Configuration

### Database Configuration

Edit the file `config/database.php` with your database credentials:

```php
<?php
// Database configuration constants
define('DB_HOST', 'localhost');           // Your database host
define('DB_USER', 'root');                // Your database username
define('DB_PASS', '');                    // Your database password
define('DB_NAME', 'oyster_harvest_db');   // Database name
?>
```

**Security Note**: In production environments:
1. Use a dedicated database user (not root)
2. Use a strong password
3. Grant only necessary privileges
4. Consider excluding `config/database.php` from version control

### PHP Configuration (Optional)

For optimal performance, adjust your `php.ini`:

```ini
; Display errors (disable in production)
display_errors = Off
log_errors = On
error_log = /path/to/php-error.log

; Time zone
date.timezone = "Asia/Manila"

; Session settings
session.gc_maxlifetime = 3600
session.cookie_httponly = 1
```

---

## 🚀 Running the System

### Development Environment

1. Start your web server (Apache/Nginx)
2. Start MySQL server
3. Open your web browser
4. Navigate to:
   - If using XAMPP/WAMP: `http://localhost/OysterHarvest`
   - If using virtual host: `http://oysterharvest.local`
   - If using IP: `http://your-server-ip/OysterHarvest`

### Production Environment

1. Upload files to your production server via FTP/SFTP
2. Import the database on your production MySQL server
3. Update `config/database.php` with production credentials
4. Set appropriate file permissions:
   ```bash
   chmod 755 /path/to/OysterHarvest
   chmod 644 /path/to/OysterHarvest/config/database.php
   ```
5. Enable HTTPS (strongly recommended)
6. Configure backups for database and files

### Testing the Installation

After accessing the system:

1. ✅ Dashboard loads with map
2. ✅ Sample areas visible on map (3 polygons)
3. ✅ Statistics cards show correct counts
4. ✅ Navigation menu works
5. ✅ Settings page accessible
6. ✅ Reports page displays sample data

---

## 📖 User Guide

### Creating a New Oyster Area

1. **Navigate to Dashboard**
2. **Click "Create New Area" button**
3. **Draw polygon on map**:
   - Click to add vertices
   - Double-click or click first point to complete polygon
4. **Fill in area details**:
   - Area Name (e.g., "North Bay Zone A")
   - Number of Oyster Sacks (e.g., 150)
   - Planting Date
   - Planting Time
5. **Click "Save Area"**
6. Harvest date is automatically calculated and displayed

### Editing an Area

1. **Click on a polygon** on the map
2. **Click "Edit" button** in the popup
3. **Modify details** in the form
4. **To change polygon shape**: Use the edit tool in the map toolbar
5. **Click "Save Area"**
6. Harvest date recalculates if planting date changed

### Marking an Area as Harvested

1. **Click on a polygon** with "Ready for Harvest" status (orange)
2. **Click "Mark Harvested"** button
3. **Confirm action**
4. Status changes to "Harvested" (green)
5. Harvest completion date is recorded

### Deleting an Area

1. **Click on a polygon** on the map
2. **Click "Delete"** button in the popup
3. **Confirm deletion**
4. Area and all associated data are removed

### Generating Reports

1. **Navigate to Reports** page
2. **Select filters**:
   - Quick Filter: All Time, Today, This Month, This Year
   - Status: All, Active, Ready for Harvest, Harvested
   - Custom Date Range: From and To dates
3. **Click "Apply Filters"**
4. **View results** in table format
5. **Click "Print Report"** for printable version

### Configuring System Settings

1. **Navigate to Settings** page
2. **Update "Average Harvest Duration"** (in months)
3. **Click "Save Settings"**
4. New value applies to all future area calculations

---

## 📁 Folder Structure

```
OysterHarvest/
│
├── config/
│   └── database.php              # Database connection configuration
│
├── database/
│   └── database.sql              # MySQL schema with sample data
│
├── assets/
│   ├── css/
│   │   ├── style.css            # Main stylesheet
│   │   └── print.css            # Print-specific styles
│   ├── js/
│   │   ├── map.js               # Leaflet map initialization
│   │   ├── dashboard.js         # Dashboard functionality
│   │   └── areas.js             # Area CRUD operations
│   └── images/                   # Icons and images (empty initially)
│
├── includes/
│   ├── header.php               # Common header with navigation
│   ├── footer.php               # Common footer with scripts
│   └── functions.php            # Utility functions
│
├── pages/
│   ├── dashboard/
│   │   └── index.php            # Main dashboard
│   ├── settings/
│   │   └── index.php            # Settings management
│   ├── reports/
│   │   └── index.php            # Report generation
│   └── areas/
│       ├── create.php           # Create area endpoint
│       ├── edit.php             # Edit area endpoint
│       ├── delete.php           # Delete area endpoint
│       ├── mark_harvested.php   # Mark as harvested endpoint
│       └── get_areas.php        # Fetch areas JSON
│
├── index.php                     # Entry point (redirects to dashboard)
├── README.md                     # This documentation
├── .gitignore                    # Git ignore rules
└── file.txt                      # (Can be removed)
```

### Key Directories

- **config/**: Database and system configuration
- **database/**: SQL schema and initialization scripts
- **assets/**: Frontend resources (CSS, JavaScript, images)
- **includes/**: Reusable PHP components
- **pages/**: Application pages organized by feature

---

## 🛠️ Technology Stack

### Backend
- **PHP 7.4+**: Server-side scripting
- **MySQL 5.7+**: Relational database
- **Native PHP**: No framework dependencies

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Styling with Flexbox/Grid
- **JavaScript (ES6)**: Client-side logic
- **jQuery 3.7**: AJAX and DOM manipulation
- **Bootstrap 5.3**: Responsive UI framework
- **Bootstrap Icons 1.10**: Icon library

### Mapping
- **Leaflet.js 1.9.4**: Interactive map library
- **Leaflet.draw 1.0.4**: Polygon drawing plugin
- **OpenStreetMap**: Free map tiles

### Architecture
- **MVC Pattern**: Separation of concerns
- **RESTful API**: AJAX endpoints for area operations
- **Responsive Design**: Mobile and desktop support

---

## 🐛 Troubleshooting

### Common Issues and Solutions

#### 1. Blank Page or 500 Error

**Cause**: PHP errors or missing database connection

**Solution**:
```php
// Enable error display temporarily in config/database.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

Check PHP error logs:
- XAMPP: `xampp/apache/logs/error.log`
- Linux: `/var/log/apache2/error.log`

#### 2. Database Connection Failed

**Symptoms**: "Database connection failed" message

**Solution**:
1. Verify MySQL is running:
   ```bash
   # Windows
   net start MySQL
   
   # Linux
   sudo systemctl status mysql
   ```

2. Check credentials in `config/database.php`
3. Test connection:
   ```bash
   mysql -u root -p
   ```

4. Verify database exists:
   ```sql
   SHOW DATABASES LIKE 'oyster_harvest_db';
   ```

#### 3. Map Not Loading

**Symptoms**: Gray box where map should be

**Solution**:
1. Check browser console for JavaScript errors (F12)
2. Verify internet connection (map tiles load from CDN)
3. Check if Leaflet CSS/JS are loading:
   ```html
   <!-- Should be in page source -->
   <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
   <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
   ```

4. Clear browser cache

#### 4. Polygons Not Saving

**Symptoms**: Modal closes but polygon disappears

**Solution**:
1. Open browser console (F12) and check for errors
2. Verify AJAX endpoints are accessible:
   - Test: `http://localhost/OysterHarvest/pages/areas/create.php`
   - Should return JSON error (not 404)
3. Check file permissions on `pages/areas/` directory
4. Verify database connection

#### 5. Harvest Date Not Calculating

**Symptoms**: Harvest date shows incorrect value

**Solution**:
1. Check settings: Navigate to Settings page
2. Verify "Average Harvest Duration" is set (default: 2 months)
3. Check PHP date functions:
   ```php
   // Test in PHP
   $date = new DateTime('2026-07-05');
   $date->add(new DateInterval('P2M'));
   echo $date->format('Y-m-d'); // Should add 2 months
   ```

#### 6. Status Not Auto-Updating

**Symptoms**: Areas remain "Active" past harvest date

**Solution**:
- Status updates occur on page load
- Refresh the dashboard page
- Check server timezone matches your timezone:
  ```php
  echo date_default_timezone_get();
  ```
- Set timezone in `php.ini` or config:
  ```php
  date_default_timezone_set('Asia/Manila');
  ```

#### 7. Print Layout Issues

**Symptoms**: Report prints incorrectly

**Solution**:
1. Verify `print.css` is loaded:
   ```html
   <link rel="stylesheet" href="/assets/css/print.css" media="print">
   ```
2. Use browser's print preview to adjust settings
3. Recommended print settings:
   - Layout: Portrait
   - Paper: A4
   - Margins: Default

#### 8. Permission Denied Errors

**Symptoms**: File operation errors

**Solution**:
```bash
# Linux/Mac
chmod -R 755 /path/to/OysterHarvest
chown -R www-data:www-data /path/to/OysterHarvest

# Or for specific user
chown -R username:username /path/to/OysterHarvest
```

### Getting Help

If you encounter issues not covered here:

1. **Check PHP error logs** for detailed error messages
2. **Verify requirements** are met (PHP version, extensions)
3. **Test database connection** independently
4. **Clear browser cache** and try again
5. **Check file permissions** on server
6. **Review browser console** for JavaScript errors

---

## 📊 Database Schema Reference

### Table: settings
Stores system-wide configuration parameters.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| average_harvest_months | INT | Default harvest duration in months |
| created_at | DATETIME | Record creation timestamp |
| updated_at | DATETIME | Last update timestamp |

### Table: oyster_areas
Stores oyster farming area metadata.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| area_name | VARCHAR(255) | Name of the farming area |
| number_of_sacks | INT | Number of oyster sacks planted |
| planting_datetime | DATETIME | Date and time of planting |
| harvest_datetime | DATETIME | Calculated harvest date |
| status | ENUM | Current status (Active, Ready for Harvest, Harvested) |
| harvest_completed_at | DATETIME | Actual harvest completion timestamp |
| created_at | DATETIME | Record creation timestamp |
| updated_at | DATETIME | Last update timestamp |

### Table: polygon_coordinates
Stores polygon vertex coordinates for map visualization.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| area_id | INT | Foreign key to oyster_areas |
| latitude | DECIMAL(10,8) | Latitude coordinate |
| longitude | DECIMAL(11,8) | Longitude coordinate |
| sequence_number | INT | Vertex order in polygon |

**Relationships:**
- `oyster_areas` (1) → `polygon_coordinates` (N)
- CASCADE DELETE: Removing an area deletes its coordinates

---

## 🔒 Security Considerations

### Production Deployment

1. **Database Security**:
   - Use strong passwords
   - Create dedicated database user with minimal privileges
   - Disable remote MySQL access if not needed

2. **File Permissions**:
   - Configuration files: 644
   - Directories: 755
   - No write permissions for web server user

3. **PHP Configuration**:
   - Disable `display_errors` in production
   - Enable `log_errors`
   - Set secure `session.cookie_httponly`
   - Use HTTPS for all connections

4. **Input Validation**:
   - All user inputs are validated server-side
   - Prepared statements prevent SQL injection
   - XSS protection via `htmlspecialchars()`

5. **Regular Maintenance**:
   - Keep PHP and MySQL updated
   - Regular database backups
   - Monitor error logs
   - Review access logs

---

## 📝 License

This project is licensed under the MIT License. You are free to use, modify, and distribute this software for personal or commercial purposes.

---

## 👥 Support

For support, please:
1. Check this README for solutions
2. Review the troubleshooting section
3. Check PHP and Apache/Nginx error logs
4. Open an issue on GitHub (if applicable)

---

## 🎉 Acknowledgments

- **Leaflet.js**: For the amazing mapping library
- **OpenStreetMap**: For free map tiles
- **Bootstrap**: For the responsive UI framework
- **Bootstrap Icons**: For the comprehensive icon set

---

**Version**: 1.0.0  
**Last Updated**: July 5, 2026  
**Author**: Oyster Harvest Development Team

---

## Quick Start Summary

```bash
# 1. Clone/Download the project
git clone https://github.com/yourusername/OysterHarvest.git

# 2. Import database
mysql -u root -p < database/database.sql

# 3. Configure database connection
# Edit config/database.php with your credentials

# 4. Access in browser
http://localhost/OysterHarvest

# 5. Start managing your oyster farm!
```

**Default System Settings:**
- Average Harvest Duration: 2 months
- Sample data included for testing
- No authentication required (single-user system)

---

Enjoy using the Oyster Farm Monitoring and Harvest Management System! 🦪🌊
