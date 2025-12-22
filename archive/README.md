# Cricket Scoring Application (CricApp)

A simple, lightweight cricket scoring application designed specifically for local cricket teams (2-4 teams, 20-25 players).

## Features

- ✅ **Zero Production Dependencies** - Pure PHP, no complex setup
- ✅ **Mobile-First Design** - Large touch buttons for outdoor scoring
- ✅ **Offline-First PWA** - Score without internet, sync later
- ✅ **Budget-Friendly** - Minimal resource requirements ($2-5/month hosting)
- ✅ **Simple Setup** - Deploy via FTP, no SSH required
- ✅ **Auto-Calculated POTM/POTS** - No manual calculation needed

## Requirements

- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.2+
- Apache with mod_rewrite (or Nginx with rewrite rules)
- Node.js 18+ and npm 9+ (for building frontend - only needed locally)

## Quick Start

### 1. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE cricapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p cricapp < sql/schema.sql

# Import seed data
mysql -u root -p cricapp < sql/seeds.sql
```

### 2. Configuration

Copy `includes/config.local.php.example` to `includes/config.local.php` and update with your database credentials:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cricapp');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('JWT_SECRET', 'your-secret-key-change-this');
```

### 3. Build Frontend (Development)

```bash
npm install
npm run build
```

### 4. Access Application

- **Public Portal**: `http://localhost/cricapp/public/`
- **Admin Panel**: `http://localhost/cricapp/admin/login.php`
- **Default Credentials**: 
  - Username: `admin`
  - Password: `admin123`
  - ⚠️ **CHANGE PASSWORD IMMEDIATELY AFTER FIRST LOGIN!**

## Project Structure

```
cricapp/
├── admin/          # Admin panel (protected routes)
├── public/         # Public portal (read-only)
├── api/v1/         # REST API endpoints
├── classes/        # PHP model classes
├── includes/       # Core infrastructure
├── assets/         # Static assets (CSS, JS, images)
├── apps/           # React applications (monorepo)
├── sql/            # Database schema and seeds
└── index.php       # Application entry point
```

## API Endpoints

- `POST /api/v1/auth/login` - Login
- `GET /api/v1/matches` - List matches
- `POST /api/v1/matches` - Create match
- `POST /api/v1/events` - Batch insert events
- `GET /api/v1/stats/leaderboard` - Get leaderboard

See `PROJECT_SPECIFICATION.md` for complete API documentation.

## Development

### PHP Development

```bash
# Run PHPUnit tests
vendor/bin/phpunit
```

### Frontend Development

```bash
# Start development servers
npm run dev:admin    # Admin app (localhost:5173)
npm run dev:scorer   # Scorer app (localhost:5174)
npm run dev:public   # Public app (localhost:5176)
```

### Building for Production

```bash
npm run build
```

Built React apps are served via PHP entry points:
- `admin/index-react.php` - Admin panel
- `public/index-react.php` - Public portal
- `admin/matches/score-react.php` - Scoring interface

## Deployment

### Shared Hosting

1. Build frontend locally: `npm run build`
2. Upload files via FTP/cPanel (exclude `node_modules/`, `.git/`, `vendor/`)
3. Create database and import schema
4. Configure `includes/config.local.php`
5. Set file permissions (755 for directories, 644 for files)

### Cron Jobs

Set up cron jobs for statistics recomputation:

```bash
# Every 1 minute (or as allowed by hosting)
*/1 * * * * /usr/bin/php /path/to/cricapp/cron/stats-recompute.php

# Every 5 minutes
*/5 * * * * /usr/bin/php /path/to/cricapp/cron/series-aggregates.php
```

## License

[Your License Here]

## Support

For questions or issues, please refer to `PROJECT_SPECIFICATION.md` or contact the project owner.

