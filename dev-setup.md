# Optimized Development Setup for Cross-Environment

## Problem
Running `npm run dev` on WSL and `php artisan serve` on Windows CMD causes significant latency due to cross-environment communication.

## Recommended Setup

### Option 1: Run Everything in WSL (Recommended)
```bash
# In WSL terminal 1
npm run dev

# In WSL terminal 2
php artisan serve --host=0.0.0.0 --port=8080
```

Access your app at: `http://localhost:8080`

### Option 2: Run Everything in Windows
```cmd
# In Windows CMD/PowerShell terminal 1
npm run dev

# In Windows CMD/PowerShell terminal 2
php artisan serve --port=8080
```

### Option 3: Use XAMPP (Best Performance)
Since you have XAMPP installed:

1. **Configure XAMPP Virtual Host:**
   - Add to `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
   ```apache
   <VirtualHost *:80>
       DocumentRoot "C:/xampp/htdocs/claude-project/public"
       ServerName claude-project.local
       <Directory "C:/xampp/htdocs/claude-project/public">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

2. **Update hosts file:**
   - Add to `C:\Windows\System32\drivers\etc\hosts`:
   ```
   127.0.0.1 claude-project.local
   ```

3. **Run only Vite in WSL:**
   ```bash
   npm run dev
   ```

4. **Access via:** `http://claude-project.local`

## Performance Commands

### Clear all caches
```bash
php artisan optimize:clear
```

### Cache for production
```bash
php artisan optimize
php artisan filament:cache-components
php artisan icons:cache
```

### Monitor performance
```bash
php artisan tinker
>>> \App\Services\DashboardPreloader::preloadAdminDashboard()
>>> \App\Services\DashboardPreloader::preloadEmployeeDashboard(auth()->user())
```

## Environment Variables for Cross-Environment

Add to `.env`:
```env
# For cross-environment development
VITE_HOST=0.0.0.0
VITE_PORT=5173

# Cache settings
CACHE_DRIVER=redis  # or 'array' for development
SESSION_DRIVER=redis  # or 'file' for development
QUEUE_CONNECTION=sync

# Optimize for development
DEBUGBAR_ENABLED=false
QUERY_DETECTOR_ENABLED=false
```

## Quick Fix Commands

If you experience slow loading:

```bash
# 1. Clear everything
php artisan optimize:clear
rm -rf node_modules/.vite

# 2. Restart Vite with proper config
npm run dev -- --force

# 3. Warm up caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Debugging Slow Loads

1. Check browser DevTools Network tab for slow requests
2. Check Laravel Telescope or Debugbar for slow queries
3. Monitor `storage/logs/laravel.log` for errors
4. Use `php artisan tinker` to test cache:
   ```php
   Cache::get('admin_task_stats');
   Cache::get('employee_stats_1');
   ```