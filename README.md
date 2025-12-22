# Cricket Scoring Application

A comprehensive PHP-based cricket scoring application for local cricket teams, built according to V1.md specification.

## Features

- **Three User Roles**: Admin (all access), Scorer (match creation/scoring), User (read-only)
- **Dynamic Player Management**: No fixed rosters, flexible team selection
- **Variable Overs**: Different overs per match and series
- **Common Players**: Support for same player playing for both teams
- **Real-time Scoring**: Ball-by-ball commentary and live updates
- **POTM/POTS**: Automated Player of the Match and Player of the Series calculations
- **Leaderboards**: Series and all-time player statistics
- **Responsive Design**: Mobile-first admin panel and public portal

## Installation

### Prerequisites

- PHP 7.4+ with PDO MySQL extension
- MySQL 5.7+ or MariaDB 10.2+
- Apache with mod_rewrite (or Nginx with rewrite rules)
- **Composer is NOT required for production** - This is a pure PHP core website with no external dependencies

**Note**: Composer is only needed if you want to run the test suite (PHPUnit). The production application uses a custom autoloader and has no vendor dependencies.

### Setup Steps

1. **Clone/Copy files to your web server directory**
   ```bash
   # Files should be in: E:\xampp\htdocs\cricapp (or your web root)
   ```

2. **Create MySQL Database**
   ```sql
   CREATE DATABASE cricapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import Database Schema**
   ```bash
   mysql -u root -p cricapp < sql/schema.sql
   mysql -u root -p cricapp < sql/seeds.sql
   ```

4. **Configure Database Connection**
   - Copy `includes/config.php` and update database credentials
   - Or create `includes/config.local.php` with your settings:
   ```php
   <?php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'cricapp');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('JWT_SECRET', 'your-secret-key-change-this');
   ```

5. **Set Permissions**
   - Ensure `assets/` directory is writable for uploads
   - Check `.htaccess` works (or configure Nginx rewrite rules)

6. **Default Login Credentials**
   - Username: `admin`
   - Password: `admin123`
   - **CHANGE THIS IMMEDIATELY AFTER FIRST LOGIN!**

## Project Structure

```
cricapp/
├── api/v1/           # REST API endpoints
├── admin/            # Admin panel (Admin/Scorer access)
├── public/           # Public read-only portal
├── classes/          # PHP model classes
├── includes/         # Core PHP files (config, DB, utils)
├── assets/           # CSS, JS, images
├── sql/              # Database schema and migrations
├── cron/             # Background job scripts
└── Spec/             # Specification documentation
```

## Current Implementation Status

### ✅ Completed (Phase 1 & Partial Phase 2)

1. **Database Schema** (`sql/schema.sql`)
   - All core tables: users, teams, series, matches, players, player_appearances
   - Events tables: events, events_suspense
   - Stats tables: stats_cache, potm_decisions, pots_aggregate, pots_overrides, impact_events
   - Admin tables: admin_action_logs, player_edits, clone_links
   - System tables: jobs, match_locks, rate_limits
   - All foreign keys, indexes, and constraints implemented

2. **Core PHP Infrastructure**
   - **Custom Autoloader** (`includes/autoloader.php`) - No Composer required for production
   - **Bootstrap System** (`includes/bootstrap.php`) - Single entry point for all includes
   - `Database` class with PDO (persistent connections, shared-hosting optimized)
   - `DatabaseModel` base class for models
   - Configuration management (`includes/config.php`)
   - Utility functions (`includes/utils.php`)
   - Error handling and JSON response helpers

3. **Authentication & RBAC**
   - JWT token generation and validation (`classes/JWT.php`)
   - User authentication (`classes/User.php`)
   - Middleware functions (`includes/middleware.php`)
   - Role-based access control (admin, scorer, user)
   - Auth API endpoint (`api/v1/auth.php`)

4. **Models**
   - User model
   - Match model (`classes/Match.php`)

5. **Routing Setup**
   - `.htaccess` for URL rewriting
   - Entry point (`index.php`)

### 🚧 Next Steps (To Continue Implementation)

1. **Complete API Endpoints** (`api/v1/`)
   - `matches.php` - Match CRUD, toss, start, finalize, clone
   - `events.php` - Event ingestion with sequence validation
   - `players.php` - Player management
   - `stats.php` - Statistics and leaderboards
   - `admin.php` - Admin-only endpoints

2. **Match State Machine**
   - Implement `MatchStateMachine` class
   - State transitions: draft → scheduled → live → completed
   - Lock mechanism for high-impact operations

3. **Statistics Engines**
   - Stats recomputation engine (`classes/StatsEngine.php`)
   - POTM engine (`classes/POTMEngine.php`)
   - POTS engine (`classes/POTSEngine.php`)

4. **Frontend Pages**
   - Public portal: live matches, recent matches, scheduled matches, leaderboards
   - Admin panel: dashboard, match management, scoring interface, player/team/series management

5. **Additional Features**
   - Cron jobs for stats recomputation
   - Rate limiting implementation
   - Conflict resolution for multi-device scoring
   - Common player handling logic

## API Endpoints

### Authentication
- `POST /api/v1/auth/login` - Login with username/password
- `POST /api/v1/auth/refresh` - Refresh JWT token
- `POST /api/v1/auth/logout` - Logout (client-side token discard)

### Match Management (To be implemented)
- `GET /api/v1/matches` - List matches
- `POST /api/v1/matches` - Create match
- `GET /api/v1/matches/{id}` - Get match details
- `POST /api/v1/matches/{id}/toss` - Record toss
- `POST /api/v1/matches/{id}/start` - Start match
- `POST /api/v1/matches/{id}/finalize` - Finalize match

### Events (To be implemented)
- `POST /api/v1/events` - Batch event insert
- `GET /api/v1/events/sync-status` - Get latest server_seq
- `GET /api/v1/matches/{id}/events` - Get match events

## Testing the Setup

1. **Test Database Connection**
   ```php
   // Create test file: test_db.php
   <?php
   require_once 'includes/db.php';
   $db = Database::getInstance()->getConnection();
   echo "Database connected!";
   ```

2. **Test Authentication API**
   ```bash
   curl -X POST http://localhost/cricapp/api/v1/auth/login \
     -H "Content-Type: application/json" \
     -d '{"username":"admin","password":"admin123"}'
   ```

## Development Notes

- **Pure PHP Core Website** - No framework, no external dependencies for production
- Uses prepared statements everywhere (SQL injection prevention)
- Custom JWT implementation (no external libraries)
- Custom autoloader replaces Composer for production
- Event-driven architecture optimized for shared hosting
- Supports multi-device scoring with conflict resolution
- Designed for 5th-grade-friendly UX in scoring interface

## Architecture: Core PHP + Vue.js Frontend

This is a **pure PHP core website** with **Vue.js 3 frontend**:

### Backend (Core PHP)

- ✅ **No Framework** - Built with vanilla PHP
- ✅ **No Vendor Dependencies** - Production code has zero external dependencies
- ✅ **Custom Autoloader** - Simple `spl_autoload_register` implementation
- ✅ **Bootstrap System** - Single `includes/bootstrap.php` entry point
- ✅ **Self-Contained** - All classes, utilities, and helpers are custom-built
- ✅ **Production Ready** - Works without `vendor/` directory

### Frontend (React & Vue.js)

**Both React and Vue.js are available!**

#### React 18
- ✅ **React 18 via CDN** - No build process required, shared hosting compatible
- ✅ **JSX with Babel Standalone** - JSX transformation in browser
- ✅ **Modern Components** - Reusable React components
- ✅ **Hooks Support** - useState, useEffect, useCallback

#### Vue.js 3
- ✅ **Vue.js 3 via CDN** - No build process required, shared hosting compatible
- ✅ **Template Syntax** - Easy-to-read templates
- ✅ **Modern Components** - Reusable Vue components
- ✅ **Real-Time Updates** - Auto-refresh for live matches

**Both Frameworks:**
- ✅ **Modern Design System** - Responsive, mobile-first design
- ✅ **Real-Time Updates** - Auto-refresh for live matches
- ✅ **No Node.js Required** - Works with standard LAMP stack

The `vendor/` directory is only present for **development/testing** (PHPUnit test suite). Production deployment can exclude it entirely using `.gitignore`.

## Documentation

- Full specification: `Spec/V1.md`
- Database schema documentation: See inline comments in `sql/schema.sql`

## License

[Your License Here]

## Support

For questions or issues, refer to the V1.md specification document.



# crick-app
