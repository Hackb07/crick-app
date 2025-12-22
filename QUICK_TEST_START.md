# Quick Test Start Guide

## 🚀 Quick Start (3 Steps)

### 1. Install Dependencies
```bash
composer install
```

### 2. Set Up Test Database
```bash
mysql -u root -p -e "CREATE DATABASE cricapp_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p cricapp_test < sql/schema.sql
```

### 3. Run Tests
```bash
php tests/run-tests.php
```

## 📋 Test Categories

All tests from `test.md` are covered:

1. ✅ **Functional Testing** - `tests/functional/`
   - Match CRUD, Player CRUD, Auth

2. ✅ **Integration Testing** - `tests/integration/`
   - API → DB → UI pipeline

3. ✅ **Security Testing** - `tests/security/`
   - SQL injection, XSS, CSRF, RBAC

4. ✅ **Performance Testing** - `tests/performance/`
   - Load testing, response times

5. ✅ **Regression Testing** - `tests/regression/`
   - Verify existing functionality

6. ✅ **UI/UX Testing** - `tests/ui/UITestChecklist.md`
   - Manual testing checklist

7. ✅ **UAT** - `tests/manual/UAT-Guide.md`
   - User acceptance testing guide

8. ✅ **Beta Testing** - `tests/manual/Beta-Testing-Guide.md`
   - Live deployment testing guide

## 🎯 Run Specific Tests

```bash
# All tests
vendor/bin/phpunit

# Functional only
vendor/bin/phpunit --testsuite Functional

# Security only
vendor/bin/phpunit --testsuite Security

# Performance only
vendor/bin/phpunit --testsuite Performance

# Single test class
vendor/bin/phpunit tests/functional/MatchTest.php
```

## 📊 View Reports

After running tests, reports are generated in:
- `tests/reports/test-report-*.html` (HTML)
- `tests/reports/test-report-*.json` (JSON)

## 📖 Full Documentation

- **Complete Guide:** `tests/README.md`
- **Summary:** `TEST_SUITE_SUMMARY.md`
- **UI Checklist:** `tests/ui/UITestChecklist.md`
- **UAT Guide:** `tests/manual/UAT-Guide.md`
- **Beta Guide:** `tests/manual/Beta-Testing-Guide.md`

## ⚡ Quick Commands

```bash
# Run all tests with reports
php tests/run-tests.php

# Run security scan
bash tests/security/security-scan.sh

# Run load test
bash tests/performance/load-test.sh

# Run with coverage
vendor/bin/phpunit --coverage-html tests/coverage
```



