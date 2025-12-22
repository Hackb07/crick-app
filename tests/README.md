# Cricket App Test Suite

This directory contains comprehensive tests for the Cricket Scoring Application, covering all aspects mentioned in `test.md`.

## Test Structure

```
tests/
├── bootstrap.php              # Test environment setup
├── functional/               # Functional tests
│   ├── MatchTest.php         # Match CRUD, scheduling, state transitions
│   ├── PlayerTest.php        # Player CRUD operations
│   └── AuthTest.php          # Authentication and JWT
├── integration/              # Integration tests
│   └── ApiIntegrationTest.php # API → DB → UI pipeline
├── security/                 # Security tests
│   └── SecurityTest.php      # SQL injection, XSS, CSRF, RBAC
├── performance/              # Performance tests
│   └── LoadTest.php          # Response times, load testing
├── regression/               # Regression tests
│   └── RegressionTest.php    # Verify existing functionality
├── ui/                       # UI/UX tests
│   └── UITestChecklist.md    # Manual UI testing checklist
├── manual/                   # Manual testing guides
│   ├── UAT-Guide.md          # User Acceptance Testing guide
│   └── Beta-Testing-Guide.md # Beta testing guide
└── run-tests.php             # Test runner script
```

## Test Categories

### 1. Functional Testing
Tests that every feature works as expected:
- Match creation & scheduling
- Score updates (runs, wickets, overs)
- Player/Team management (CRUD)
- Leaderboard synchronization
- Date/time handling

**Run:** `php tests/functional/MatchTest.php` or use PHPUnit

### 2. Integration Testing
Tests that components work together:
- API → Database → UI data flow
- Admin updates visible on public side
- REST API and UI triggers
- Event synchronization

**Run:** `php tests/integration/ApiIntegrationTest.php`

### 3. Security Testing
Tests security measures:
- SQL Injection protection
- XSS prevention
- CSRF protection
- Authentication requirements
- Role-based access control (RBAC)

**Run:** `php tests/security/SecurityTest.php`

### 4. Performance Testing
Tests performance under load:
- API response times (< 1 second target)
- Database query optimization
- Concurrent request handling
- Connection pooling

**Run:** `php tests/performance/LoadTest.php`

### 5. Regression Testing
Tests that existing functionality still works after changes:
- Match creation still works
- Player CRUD still works
- Authentication still works
- State transitions still work

**Run:** `php tests/regression/RegressionTest.php`

### 6. UI/UX Testing
Manual testing checklist for:
- Responsive design (desktop, tablet, mobile)
- Button and navigation responsiveness
- Animations and smooth performance
- Browser compatibility
- Accessibility

**See:** `tests/ui/UITestChecklist.md`

### 7. User Acceptance Testing (UAT)
Guide for testing with real users:
- View live match scenario
- Record match scores scenario
- View leaderboard scenario
- Manage players scenario

**See:** `tests/manual/UAT-Guide.md`

### 8. Beta Testing
Guide for live deployment testing:
- Monitor logs and performance
- Collect user feedback
- Test server stress
- Identify issues before production

**See:** `tests/manual/Beta-Testing-Guide.md`

## Setup

### 1. Install Dependencies
```bash
composer install
```

### 2. Configure Test Database
Update `phpunit.xml` or `includes/config.php` with test database credentials:
```xml
<env name="DB_NAME" value="cricapp_test"/>
<env name="DB_USER" value="root"/>
<env name="DB_PASS" value=""/>
```

### 3. Create Test Database
```bash
mysql -u root -p -e "CREATE DATABASE cricapp_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p cricapp_test < sql/schema.sql
mysql -u root -p cricapp_test < sql/seeds.sql
```

## Running Tests

### Run All Tests
```bash
php tests/run-tests.php
```

Or using PHPUnit directly:
```bash
vendor/bin/phpunit
```

### Run Specific Test Suite
```bash
vendor/bin/phpunit --testsuite Functional
vendor/bin/phpunit --testsuite Integration
vendor/bin/phpunit --testsuite Security
vendor/bin/phpunit --testsuite Performance
vendor/bin/phpunit --testsuite Regression
```

### Run Specific Test Class
```bash
vendor/bin/phpunit tests/functional/MatchTest.php
vendor/bin/phpunit tests/security/SecurityTest.php
```

### Run with Coverage
```bash
vendor/bin/phpunit --coverage-html tests/coverage
```

## Manual Testing

### UI/UX Testing
Follow the checklist in `tests/ui/UITestChecklist.md`

### User Acceptance Testing
Follow the guide in `tests/manual/UAT-Guide.md`

### Beta Testing
Follow the guide in `tests/manual/Beta-Testing-Guide.md`

## Test Reports

Test reports are generated in `tests/reports/`:
- JSON reports: `test-report-YYYY-MM-DD-HH-MM-SS.json`
- HTML reports: `test-report-YYYY-MM-DD-HH-MM-SS.html`

## Continuous Integration

### GitHub Actions Example
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: vendor/bin/phpunit
```

## Test Coverage Goals

- **Functional Tests:** 80%+ coverage for core logic
- **Integration Tests:** All critical paths covered
- **Security Tests:** All identified vulnerabilities tested
- **Performance Tests:** All endpoints benchmarked
- **Regression Tests:** All critical features verified

## Troubleshooting

### Tests Fail with Database Connection Error
- Verify database credentials in `includes/config.php`
- Ensure test database exists
- Check database user permissions

### Tests Fail with Class Not Found
- Run `composer dump-autoload`
- Check that classes are in correct directories
- Verify namespace usage

### API Tests Fail
- Ensure server is running on `http://localhost/cricapp`
- Verify `.htaccess` is configured correctly
- Check API endpoints are accessible

## Contributing

When adding new features:
1. Write tests first (TDD approach)
2. Ensure all tests pass
3. Add tests for edge cases
4. Update test documentation
5. Run full test suite before committing

## Notes

- Tests use a separate test database to avoid affecting production data
- Test data is created and cleaned up automatically
- Some tests require the application server to be running
- Security tests may generate false positives - review carefully



