# Windows Installation Guide - Hotel Information System

Quick and easy installation guide for Windows users.

## Quick Start (5 Minutes)

### Prerequisites Checklist
- [ ] Laragon installed (or XAMPP)
- [ ] Internet connection for downloading dependencies

---

## Method 1: Laragon (Easiest - Recommended)

### Step 1: Install Laragon (If not installed)
1. Download: [https://laragon.org/download/](https://laragon.org/download/)
2. Install to: `C:\laragon`
3. Launch Laragon

### Step 2: Start Services
1. Click **Start All** in Laragon
2. Wait for green lights (Apache + MySQL)

### Step 3: Open Terminal
1. Right-click Laragon tray icon
2. Select **Terminal** (or Quick app → Terminal)

### Step 4: Navigate to Project
```bash
cd laravel\his
```

### Step 5: Install Dependencies (Takes 2-3 minutes)
```bash
composer install
npm install
```

### Step 6: Setup Environment
```bash
copy .env.example .env
php artisan key:generate
```

### Step 7: Configure Database
1. Open `.env` file in Notepad or VS Code
2. Update these lines:
```env
DB_DATABASE=his
DB_USERNAME=root
DB_PASSWORD=
```

### Step 8: Create Database
1. Click **Database** in Laragon
2. Click **Open phpMyAdmin**
3. Click **New** → Database name: `his`
4. Click **Create**

### Step 9: Run Migrations
Back in terminal:
```bash
php artisan migrate
```
Type `yes` when asked.

### Step 10: Access Your Site
```
http://his.test
```

### Step 11: Compile Assets
```bash
npm run dev
```

**✅ Done! Your site is ready!**

---

## Method 2: XAMPP

### Step 1: Install XAMPP
1. Download: [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install to: `C:\xampp`
3. Open XAMPP Control Panel
4. Start **Apache** and **MySQL**

### Step 2: Install Composer
1. Download: [https://getcomposer.org/Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)
2. Run installer (use default settings)
3. Restart Command Prompt after installation

### Step 3: Install Node.js
1. Download: [https://nodejs.org/](https://nodejs.org/) (LTS version)
2. Run installer (use default settings)
3. Restart Command Prompt after installation

### Step 4: Open Command Prompt
1. Press `Win + R`
2. Type `cmd`
3. Press Enter

### Step 5: Navigate to Project
```bash
cd C:\xampp\htdocs\his
```

### Step 6: Install Dependencies
```bash
composer install
npm install
```

### Step 7: Setup Environment
```bash
copy .env.example .env
php artisan key:generate
```

### Step 8: Configure Database
1. Open `.env` file
2. Update:
```env
APP_URL=http://localhost/his/public

DB_DATABASE=his
DB_USERNAME=root
DB_PASSWORD=
```

### Step 9: Create Database
1. Open: `http://localhost/phpmyadmin`
2. Click **New**
3. Database name: `his`
4. Click **Create**

### Step 10: Run Migrations
```bash
php artisan migrate
```

### Step 11: Access Your Site
```
http://localhost/his/public
```

**✅ Done!**

---

## Quick Commands Reference

### Daily Use Commands

**Start Development Server (Alternative to Laragon/XAMPP)**
```bash
php artisan serve
```
Access at: `http://localhost:8000`

**Compile Frontend (Keep running during development)**
```bash
npm run dev
```

**Clear All Cache**
```bash
php artisan optimize:clear
```

---

## Common Errors & Quick Fixes

### ❌ Error: "composer is not recognized"
**Fix:**
```bash
# Download and run Composer installer again:
# https://getcomposer.org/Composer-Setup.exe
# Then restart Command Prompt
```

### ❌ Error: "npm is not recognized"
**Fix:**
```bash
# Download and install Node.js:
# https://nodejs.org/
# Then restart Command Prompt
```

### ❌ Error: "SQLSTATE[HY000] [1045] Access denied"
**Fix:** Check `.env` file:
```env
DB_USERNAME=root
DB_PASSWORD=         # Leave empty for Laragon/XAMPP default
```

### ❌ Error: "Base table or view not found"
**Fix:**
```bash
php artisan migrate
```

### ❌ Error: "file_put_contents(...): failed to open stream"
**Fix in PowerShell (Run as Administrator):**
```powershell
icacls "C:\laragon\www\laravel\his\storage" /grant Everyone:F /t
icacls "C:\laragon\www\laravel\his\bootstrap\cache" /grant Everyone:F /t
```

**Fix in XAMPP:** Right-click folders → Properties → Security → Edit → Add "Everyone" with Full Control

### ❌ Error: "Class 'Botble\...' not found"
**Fix:**
```bash
composer dump-autoload
php artisan optimize:clear
```

### ❌ Browser shows code instead of website
**Fix:** Make sure you're accessing:
- Laragon: `http://his.test` (not the folder directly)
- XAMPP: `http://localhost/his/public`
- Or use: `php artisan serve` then `http://localhost:8000`

---

## Testing the Installation

### 1. Check PHP Version
```bash
php -v
```
Should show: PHP 8.1 or higher

### 2. Check Composer
```bash
composer -V
```

### 3. Check Node.js
```bash
node -v
npm -v
```

### 4. Check Database Connection
```bash
php artisan tinker
```
Then type:
```php
DB::connection()->getPdo();
```
Should show: `PDO connection successful`
Type `exit` to quit.

---

## Admin Panel Access

### First Time Setup

1. Navigate to admin panel:
```
http://his.test/admin
```

2. If no admin user exists, create one:
```bash
php artisan cms:user:create
```

3. Follow prompts to create admin user

### Default Credentials (if seeded)
```
Email: admin@example.com
Password: 12345678
```

**⚠️ IMPORTANT:** Change password immediately after first login!

---

## Folder Permissions (If Needed)

### For Laragon Users (PowerShell as Administrator)
```powershell
cd C:\laragon\www\laravel\his
icacls storage /grant Everyone:F /t
icacls bootstrap\cache /grant Everyone:F /t
```

### For XAMPP Users
1. Right-click `storage` folder
2. Properties → Security tab
3. Click **Edit** → **Add**
4. Type "Everyone" → Check names → OK
5. Check "Full Control" → Apply
6. Repeat for `bootstrap\cache` folder

---

## Update Site URL (If Needed)

### Change from localhost to custom domain

1. Edit `.env`:
```env
APP_URL=http://his.test
```

2. Clear cache:
```bash
php artisan config:clear
```

3. For Laragon: Right-click → Apache → sites-enabled → Add → Type: `his.test`

---

## Uninstallation

### Complete Removal

1. **Stop Services** (Laragon/XAMPP)

2. **Drop Database:**
   - Open phpMyAdmin
   - Select `his` database
   - Click **Drop**

3. **Delete Files:**
   - Delete `C:\laragon\www\laravel\his` (Laragon)
   - Delete `C:\xampp\htdocs\his` (XAMPP)

4. **Remove Virtual Host (Laragon only):**
   - Right-click Laragon
   - Apache → sites-enabled
   - Remove `his.test.conf`
   - Reload Apache

5. **Edit hosts file (if modified):**
   - Open: `C:\Windows\System32\drivers\etc\hosts`
   - Remove line: `127.0.0.1 his.test`

---

## Performance Optimization

### Production Mode

When ready to deploy:

1. Set environment to production:
```env
APP_ENV=production
APP_DEBUG=false
```

2. Optimize application:
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

---

## Backup Your Site

### Quick Backup Steps

1. **Export Database:**
   - phpMyAdmin → Select `his` → Export → Go
   - Save SQL file

2. **Backup Files:**
   - Copy entire project folder
   - Especially important: `storage/app` and `.env`

3. **Restore:**
   - Copy files back
   - Import SQL file in phpMyAdmin
   - Run: `composer install` and `npm install`

---

## Getting Help

### Before Asking for Help:

1. ✅ Check error message carefully
2. ✅ Try cache clear: `php artisan optimize:clear`
3. ✅ Check `.env` file configuration
4. ✅ Verify database exists and credentials are correct
5. ✅ Review this guide's troubleshooting section

### Useful Commands for Debugging:
```bash
# View detailed errors
php artisan route:list           # List all routes
php artisan tinker              # Test code interactively
php artisan migrate:status      # Check migration status
composer dump-autoload          # Regenerate autoload files
```

---

## Next Steps

After successful installation:

1. ✅ Access admin panel
2. ✅ Change default password
3. ✅ Configure hotel settings
4. ✅ Add locations
5. ✅ Add hotels
6. ✅ Add rooms
7. ✅ Test booking system
8. ✅ Test location/hotel filters

---

**Installation Support:**
- Check README.md for detailed documentation
- Review Laravel docs: https://laravel.com/docs
- Botble docs: https://docs.botble.com

**Last Updated:** December 2024
