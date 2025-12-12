# Hotel Information System (HIS)

A comprehensive hotel management system built with Laravel, featuring room booking, location management, and multi-hotel support.

## Table of Contents
- [System Requirements](#system-requirements)
- [Installation on Windows](#installation-on-windows)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [Features](#features)
- [Troubleshooting](#troubleshooting)

## System Requirements

### Required Software
- **PHP**: Version 8.1 or higher
- **Composer**: Latest version
- **MySQL/MariaDB**: Version 5.7+ or 10.3+
- **Node.js**: Version 16+ (for asset compilation)
- **NPM**: Version 8+ (comes with Node.js)

### Recommended Windows Environment
- **Laragon** (Recommended) - All-in-one development environment
- **XAMPP** - Alternative PHP development environment
- **WAMP** - Windows Apache MySQL PHP stack

## Installation on Windows

### Option 1: Using Laragon (Recommended)

#### Step 1: Install Laragon
1. Download Laragon from [https://laragon.org/download/](https://laragon.org/download/)
2. Run the installer and follow the installation wizard
3. Choose installation directory (default: `C:\laragon`)
4. Complete the installation

#### Step 2: Start Laragon
1. Launch Laragon
2. Click **Start All** button
3. Wait for Apache and MySQL to start (green indicators)

#### Step 3: Clone or Copy Project
```bash
# Navigate to Laragon's www directory
cd C:\laragon\www\

# If you haven't already, your project should be at:
# C:\laragon\www\laravel\his
```

#### Step 4: Install Dependencies
1. Open Laragon Terminal (Right-click Laragon → Terminal)
2. Navigate to project directory:
```bash
cd laravel\his
```

3. Install PHP dependencies:
```bash
composer install
```

4. Install Node.js dependencies:
```bash
npm install
```

#### Step 5: Environment Configuration
1. Copy the example environment file:
```bash
copy .env.example .env
```

2. Generate application key:
```bash
php artisan key:generate
```

3. Edit `.env` file with your database credentials:
```env
APP_NAME="Hotel Information System"
APP_ENV=local
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=true
APP_URL=http://his.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=his
DB_USERNAME=root
DB_PASSWORD=

# Mail Configuration (Optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Option 2: Using XAMPP

#### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Run installer and install to `C:\xampp`
3. Start Apache and MySQL from XAMPP Control Panel

#### Step 2: Install Composer
1. Download Composer from [https://getcomposer.org/download/](https://getcomposer.org/download/)
2. Run the installer (Composer-Setup.exe)
3. Follow the installation wizard

#### Step 3: Copy Project
```bash
# Copy your project to XAMPP's htdocs directory
# Your project should be at: C:\xampp\htdocs\his
```

#### Step 4: Follow Steps 4-5 from Laragon Instructions
Use Command Prompt or PowerShell instead of Laragon Terminal.

## Database Setup

### Step 1: Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click **New** in the left sidebar
3. Enter database name: `his`
4. Choose **utf8mb4_unicode_ci** collation
5. Click **Create**

### Step 2: Run Migrations
```bash
php artisan migrate
```

### Step 3: Seed Database (Optional)
```bash
php artisan db:seed
```

### Step 4: Run Specific Hotel Migrations
The following migrations are required for location and hotel management:
```bash
# These should already be run with the migrate command above
# - 2024_12_06_000000_create_ht_locations_table.php
# - 2024_12_06_000001_add_location_id_to_ht_rooms_table.php
# - 2024_12_06_000002_remove_location_id_from_ht_rooms_table.php
# - 2024_12_06_000003_add_location_id_to_ht_hotels_table.php
```

## Running the Application

### Using Laragon
1. Laragon automatically creates virtual host: `http://his.test`
2. If not working, add virtual host manually:
   - Right-click Laragon → Apache → sites-enabled → Add
   - Enter: `his.test`
   - Restart Apache

### Using XAMPP
1. Start Apache and MySQL from XAMPP Control Panel
2. Access application:
   - `http://localhost/his/public`
   - Or configure virtual host for cleaner URL

### Using PHP Built-in Server
```bash
php artisan serve
```
Access at: `http://localhost:8000`

### Compile Frontend Assets
```bash
# Development (with hot reload)
npm run dev

# Production (optimized)
npm run build
```

## Features

### Core Features
- ✅ Multi-hotel management
- ✅ Location-based hotel organization
- ✅ Room booking system
- ✅ Room availability checking
- ✅ Dynamic pricing by date
- ✅ Customer management
- ✅ Booking history
- ✅ Review and rating system

### New Features (Recently Added)
- ✅ **Location Filter**: Filter rooms by location
- ✅ **Hotel Filter**: Filter rooms by specific hotel
- ✅ Combined filtering (location + hotel)
- ✅ Persistent filter state in URL parameters

### Using the Filter Feature

#### On the Rooms Page
1. Navigate to the rooms listing page
2. You'll see filter dropdowns at the top:
   - **Filter by Location**: Select a location to show rooms from hotels in that area
   - **Filter by Hotel**: Select a specific hotel to show only its rooms
3. Click **Apply Filters** to see filtered results
4. Click **Clear Filters** to reset and show all rooms

#### Filter Behavior
- **Location only**: Shows all rooms from hotels in selected location
- **Hotel only**: Shows all rooms from selected hotel
- **Both filters**: Shows rooms from selected hotel in selected location
- Filters work with existing search and date filters

## Project Structure

```
his/
├── platform/
│   ├── plugins/
│   │   └── hotel/              # Hotel plugin
│   │       ├── database/
│   │       │   └── migrations/ # Database migrations
│   │       ├── src/
│   │       │   ├── Models/     # Hotel, Room, Location models
│   │       │   ├── Http/       # Controllers
│   │       │   ├── Repositories/ # Data repositories
│   │       │   └── Supports/   # Helper classes
│   │       └── resources/
│   │           └── views/      # Plugin views
│   └── themes/
│       └── riorelax/           # Active theme
│           ├── functions/
│           │   └── shortcodes.php # Shortcode definitions
│           ├── partials/       # Partial views
│           └── views/          # Theme views
├── public/                     # Public assets
├── storage/                    # File storage
├── .env                        # Environment configuration
├── composer.json               # PHP dependencies
├── package.json                # Node.js dependencies
└── README.md                   # This file
```

## Configuration

### Admin Panel Access
Default admin credentials (if seeded):
```
URL: http://your-domain.com/admin
Email: admin@example.com
Password: 12345678
```

**Important**: Change default credentials immediately after first login!

### Plugin Activation
1. Go to: **Admin Panel** → **Plugins**
2. Ensure these plugins are activated:
   - ✅ Hotel
   - ✅ Location (if available)
   - ✅ Blog (optional)
   - ✅ Gallery (optional)

### Theme Configuration
1. Go to: **Admin Panel** → **Appearance** → **Themes**
2. Activate **Riorelax** theme
3. Configure theme options in **Appearance** → **Theme Options**

## Troubleshooting

### Common Issues

#### 1. "Call to undefined function Botble\Hotel\Models\wherePublished()"
**Solution**: Check that your Location and Hotel models have the proper status scope. Add to models:
```php
public function scopeWherePublished($query)
{
    return $query->where('status', \Botble\Base\Enums\BaseStatusEnum::PUBLISHED);
}
```

#### 2. "Undefined array key 'hotel_id'"
**Solution**: Already fixed! The HotelSupport::getRoomFilters() method now includes hotel_id and location_id.

#### 3. Composer Install Fails
**Solutions**:
- Update Composer: `composer self-update`
- Clear Composer cache: `composer clear-cache`
- Install with: `composer install --no-scripts`
- Then run: `composer run-script post-install-cmd`

#### 4. NPM Install Fails
**Solutions**:
- Clear NPM cache: `npm cache clean --force`
- Delete `node_modules` folder and `package-lock.json`
- Run: `npm install` again
- Try with legacy peer deps: `npm install --legacy-peer-deps`

#### 5. Permission Denied Errors
**Solution**: Grant write permissions to these folders:
```bash
# Run in PowerShell as Administrator (Laragon)
icacls "C:\laragon\www\laravel\his\storage" /grant Everyone:F /t
icacls "C:\laragon\www\laravel\his\bootstrap\cache" /grant Everyone:F /t
```

#### 6. Database Connection Error
**Solutions**:
- Verify MySQL is running
- Check `.env` database credentials
- Ensure database exists
- Test connection: `php artisan tinker` then `DB::connection()->getPdo();`

#### 7. "Class not found" Errors
**Solution**: Regenerate autoload files:
```bash
composer dump-autoload
php artisan clear-compiled
php artisan optimize:clear
```

#### 8. Virtual Host Not Working (Laragon)
**Solutions**:
1. Check `C:\Windows\System32\drivers\etc\hosts` file contains:
   ```
   127.0.0.1 his.test
   ```
2. Reload Apache in Laragon
3. Flush DNS: `ipconfig /flushdns` in Command Prompt (Admin)

### Clearing Cache
When things don't work as expected, clear all caches:
```bash
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Development Mode
Enable detailed errors during development:
```env
APP_DEBUG=true
APP_ENV=local
```

**Warning**: Never enable `APP_DEBUG=true` in production!

## Git Workflow

### Current Branch Status
```bash
# Check current changes
git status

# View recent commits
git log --oneline -5
```

### Committing Changes
```bash
# Stage files
git add .

# Commit with message
git commit -m "Add location and hotel filters to rooms page"

# Push to remote
git push origin main
```

### Modified Files (Current Session)
- `platform/plugins/hotel/src/Repositories/Eloquent/RoomRepository.php`
- `platform/plugins/hotel/src/Supports/HotelSupport.php`
- `platform/themes/riorelax/functions/shortcodes.php`
- `platform/themes/riorelax/partials/shortcodes/all-rooms/index.blade.php`
- `platform/plugins/hotel/database/migrations/2024_12_06_000003_add_location_id_to_ht_hotels_table.php`

## Additional Resources

### Documentation Links
- Laravel Documentation: [https://laravel.com/docs](https://laravel.com/docs)
- Botble CMS Documentation: [https://docs.botble.com](https://docs.botble.com)
- PHP Manual: [https://www.php.net/manual/](https://www.php.net/manual/)

### Support
For issues and questions:
1. Check this README first
2. Review Laravel documentation
3. Check Botble CMS documentation
4. Search for similar issues on Stack Overflow

## License

This project is proprietary software. All rights reserved.

## Updates and Maintenance

### Updating Dependencies
```bash
# Update Composer packages
composer update

# Update NPM packages
npm update

# Rebuild assets
npm run build
```

### Backup
Regular backups are recommended:
1. **Database**: Export via phpMyAdmin
2. **Files**: Copy entire project directory
3. **Storage**: Backup `storage/app` directory

---

**Last Updated**: December 2024
**Version**: 1.0
**Maintained by**: Development Team
