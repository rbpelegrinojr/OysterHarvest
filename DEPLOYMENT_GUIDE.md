# Oyster Harvest Management System - Deployment Guide

## Overview

This guide explains how to deploy the Oyster Harvest Management System in different environments (localhost XAMPP subdirectory or shared hosting).

## Problem That Was Solved

The application had **hard-coded absolute paths** starting with `/` which caused different behavior between:
- **Localhost (XAMPP)** in subdirectory: `http://localhost/oyster/`
- **Shared Hosting** in document root: `http://yoursite.com/`

### Issues Fixed:

1. ✅ **Hard-coded paths** - All URLs now use dynamic base URL
2. ✅ **Asset loading** - CSS, JavaScript, and images load correctly in both environments
3. ✅ **AJAX requests** - All AJAX calls now use proper base URL
4. ✅ **Navigation links** - Navigation menu works in both environments
5. ✅ **Redirects** - Index redirect now uses correct base path
6. ✅ **Leaflet map** - Satellite tiles now display by default
7. ✅ **Map centering** - Uses database settings for correct coordinates

---

## How It Works

### Auto-Detection

The system now **automatically detects** its installation directory using PHP's `$_SERVER['SCRIPT_NAME']` and sets the `BASE_URL` constant accordingly.

### Examples:

| Environment | Installation Path | Detected BASE_URL |
|------------|-------------------|-------------------|
| XAMPP Subdirectory | `C:/xampp/htdocs/oyster/` | `/oyster` |
| XAMPP Root | `C:/xampp/htdocs/` | `` (empty) |
| Shared Hosting Root | `/public_html/` | `` (empty) |
| Shared Hosting Subdirectory | `/public_html/app/` | `/app` |

---

## Installation Instructions

### 1. **For Localhost (XAMPP) in Subdirectory**

If you're running XAMPP and the application is in `http://localhost/oyster/`:

```
No configuration needed! The system auto-detects the subdirectory.
```

**Steps:**
1. Place the application files in `C:/xampp/htdocs/oyster/`
2. Import the database from `database/database.sql`
3. Configure database credentials in `config/database.php`
4. Access: `http://localhost/oyster/`

### 2. **For Localhost (XAMPP) in Root**

If you're running XAMPP and the application is in document root `http://localhost/`:

```
No configuration needed! The system detects it's in root.
```

**Steps:**
1. Place the application files in `C:/xampp/htdocs/`
2. Import the database from `database/database.sql`
3. Configure database credentials in `config/database.php`
4. Access: `http://localhost/`

### 3. **For Shared Hosting in Document Root**

If your application is in the main domain (e.g., `http://yoursite.com/`):

```
No configuration needed! The system detects it's in root.
```

**Steps:**
1. Upload all files to `public_html/` (or `www/` depending on hosting)
2. Import the database using phpMyAdmin or cPanel
3. Configure database credentials in `config/database.php`
4. Access: `http://yoursite.com/`

### 4. **For Shared Hosting in Subdirectory**

If your application is in a subdirectory (e.g., `http://yoursite.com/app/`):

```
No configuration needed! The system auto-detects the subdirectory.
```

**Steps:**
1. Upload all files to `public_html/app/`
2. Import the database using phpMyAdmin or cPanel
3. Configure database credentials in `config/database.php`
4. Access: `http://yoursite.com/app/`

---

## Database Configuration

Edit `config/database.php` and update these values:

```php
// For localhost
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'oyster_harvest_db');

// For shared hosting
define('DB_HOST', 'localhost');           // Or your hosting DB server
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'your_db_name');
```

---

## Troubleshooting

### Problem: Assets (CSS/JS) Not Loading

**Symptom:** Page displays but without styling or functionality

**Solution:**
1. Check browser console for 404 errors
2. Verify files exist in `assets/css/` and `assets/js/`
3. Clear browser cache (Ctrl+Shift+Delete)
4. The BASE_URL should auto-detect correctly

### Problem: AJAX Requests Failing

**Symptom:** Map doesn't load areas, actions don't work

**Solution:**
1. Open browser Developer Tools (F12) → Network tab
2. Check if AJAX requests are going to correct URLs
3. Verify that `BASE_URL` JavaScript variable is set (check page source)
4. Check PHP error logs for server-side errors

### Problem: Redirects Go to Wrong Location

**Symptom:** Clicking navigation links goes to wrong pages

**Solution:**
1. Verify that `config/config.php` is being loaded
2. Check that `BASE_URL` constant is defined
3. Review the auto-detection logic in `config/config.php`

### Problem: Map Doesn't Display Satellite Tiles

**Solution:**
1. The map now defaults to satellite view
2. Use the layer control (top-right of map) to switch views
3. Check internet connection (tiles load from external server)
4. Map requires active internet connection

### Problem: Map Not Centered Correctly

**Solution:**
1. Go to Settings page
2. Update "Map Center Latitude" and "Map Center Longitude"
3. Default is Manila Bay, Philippines (14.5995, 120.9842)
4. Use decimal degrees format (e.g., 14.5995, not DMS format)

---

## Files Modified

The following files were modified to implement the base URL system:

1. **NEW:** `config/config.php` - Base URL configuration and helper functions
2. `index.php` - Uses `url()` helper for redirect
3. `includes/header.php` - Uses `url()` and `asset()` helpers, exports BASE_URL to JavaScript
4. `includes/footer.php` - Uses `asset()` helper for JavaScript files
5. `assets/js/map.js` - Uses BASE_URL for AJAX requests, satellite layer default
6. `assets/js/areas.js` - Uses BASE_URL for AJAX requests

---

## Manual Configuration (Advanced)

If auto-detection doesn't work for your environment, you can manually set the BASE_URL in `config/config.php`:

### Option 1: Edit the Auto-Detection Logic

Find this section in `config/config.php`:

```php
// Auto-detect base URL based on the script location
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = ($scriptPath === '/' || $scriptPath === '\\') ? '' : $scriptPath;
$basePath = rtrim($basePath, '/\\');
define('BASE_URL', $basePath);
```

### Option 2: Hard-Code BASE_URL (Not Recommended)

Replace the auto-detection code with:

```php
// For XAMPP in subdirectory 'oyster'
define('BASE_URL', '/oyster');

// For shared hosting in root
define('BASE_URL', '');

// For subdomain or subdirectory 'app'
define('BASE_URL', '/app');
```

**⚠️ Warning:** Hard-coding means you'll need to change this value when moving between environments.

---

## Helper Functions Reference

### PHP Functions (Available in all PHP files)

```php
// Generate URL with base path
url('/pages/dashboard/index.php')
// Returns: /oyster/pages/dashboard/index.php (on localhost/oyster)
// Returns: /pages/dashboard/index.php (on root)

// Generate asset URL
asset('css/style.css')
// Returns: /oyster/assets/css/style.css (on localhost/oyster)
// Returns: /assets/css/style.css (on root)

// Get base URL (for JavaScript)
getBaseUrl()
// Returns: /oyster (on localhost/oyster)
// Returns: (empty string on root)
```

### JavaScript Variable (Available in all pages)

```javascript
// BASE_URL is automatically available in JavaScript
console.log(BASE_URL); // Shows current base URL

// Use in AJAX calls
$.ajax({
    url: BASE_URL + '/pages/settings/get_settings.php',
    // ...
});
```

---

## Testing Checklist

After deployment, test these features:

- [ ] Homepage redirects to dashboard
- [ ] Navigation menu links work
- [ ] CSS styling loads correctly
- [ ] Bootstrap icons display
- [ ] Leaflet map loads with satellite tiles
- [ ] Map centers on correct coordinates
- [ ] Creating new oyster area works
- [ ] Editing area works
- [ ] Deleting area works
- [ ] Marking area as harvested works
- [ ] Statistics cards display correct counts
- [ ] Reports page works
- [ ] Settings page works and saves changes
- [ ] All AJAX requests succeed (check browser console)

---

## Support

If you encounter issues not covered in this guide:

1. Check PHP error logs
2. Check browser console (F12 → Console tab)
3. Check network requests (F12 → Network tab)
4. Verify database credentials in `config/database.php`
5. Ensure all files were uploaded correctly

---

## Version History

- **v2.0** (Current) - Implemented dynamic base URL system with auto-detection
- **v1.0** (Previous) - Hard-coded paths (only worked in root directory)
