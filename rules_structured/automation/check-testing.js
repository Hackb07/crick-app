#!/usr/bin/env node

/**
 * Testing Checker
 * Validates test coverage and quality
 */

const fs = require('fs');
const path = require('path');

function checkFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    const issues = [];

    // Check if it's a source file (not a test)
    const isTestFile = /\.(test|spec)\.(js|ts|php)$/.test(filePath);

    if (!isTestFile) {
        // Check if corresponding test file exists
        const testPaths = [
            filePath.replace(/\.(js|ts|php)$/, '.test.$1'),
            filePath.replace(/\.(js|ts|php)$/, '.spec.$1'),
            filePath.replace(/\/src\//, '/tests/'),
            filePath.replace(/\/app\//, '/tests/')
        ];

        const hasTest = testPaths.some(testPath => {
            try {
                return fs.existsSync(testPath);
            } catch {
                return false;
            }
        });

        if (!hasTest && content.includes('function') && content.length > 200) {
            issues.push({
                file: filePath,
                line: 1,
                severity: 'MEDIUM',
                issue: 'missing_tests',
                message: 'No corresponding test file found',
                code: '(Create test file)'
            });
        }
    }

    // If it IS a test file, check test quality
    if (isTestFile) {
        const lines = content.split('\n');

        // Check for test structure (AAA pattern)
        const hasDescribe = /describe\(|test\(|it\(/i.test(content);
        if (!hasDescribe && content.length > 50) {
            issues.push({
                file: filePath,
                line: 1,
                severity: 'HIGH',
                issue: 'missing_test_structure',
                message: 'Test file missing describe/test/it blocks',
                code: '(Add proper test structure)'
            });
        }

        // Check for assertions
        const hasAssertions = /expect\(|assert|should|toBe|toEqual/i.test(content);
        if (!hasAssertions && content.length > 50) {
            issues.push({
                file: filePath,
                line: 1,
                severity: 'CRITICAL',
                issue: 'no_assertions',
                message: 'Test file has no assertions - tests that always pass',
                code: '(Add expect/assert statements)'
            });
        }

        // Check for commented tests
        lines.forEach((line, index) => {
            if (/\/\/\s*(test|it|describe)\(/i.test(line)) {
                issues.push({
                    file: filePath,
                    line: index + 1,
                    severity: 'MEDIUM',
                    issue: 'commented_test',
                    message: 'Commented test found - remove or fix',
                    code: line.trim()
                });
            }
        });

        // Check for .only (focused tests)
        if (/\.(only)\(/.test(content)) {
            issues.push({
                file: filePath,
                line: 1,
                severity: 'HIGH',
                issue: 'focused_test',
                message: 'Focused test (.only) found - remove before commit',
                code: '(Remove .only)'
            });
        }

        // Check for sleep/wait in tests (flaky tests)
        if (/sleep\(|setTimeout\(|wait\(/i.test(content)) {
            issues.push({
                file: filePath,
                line: 1,
                severity: 'HIGH',
                issue: 'flaky_test',
                message: 'Test uses sleep/setTimeout - potential flaky test',
                code: '(Use proper async/await or mocks)'
            });
        }
    }

    return issues;
}

function scanDirectory(dir) {
    let allIssues = [];
    let stats = {
        sourceFiles: 0,
        testFiles: 0,
        coverage: 0
    };

    function scan(currentDir) {
        const entries = fs.readdirSync(currentDir, { withFileTypes: true });

        entries.forEach(entry => {
            const fullPath = path.join(currentDir, entry.name);

            if (entry.isDirectory() && !entry.name.startsWith('.') && entry.name !== 'node_modules') {
                scan(fullPath);
            } else if (entry.isFile()) {
                const isTest = /\.(test|spec)\.(js|ts|php)$/.test(entry.name);
                const isSource = /\.(js|ts|php)$/.test(entry.name) && !isTest;

                if (isTest) stats.testFiles++;
                if (isSource) stats.sourceFiles++;

                if (isTest || isSource) {
                    const issues = checkFile(fullPath);
                    allIssues = allIssues.concat(issues);
                }
            }
        });
    }

    scan(dir);

    // Calculate rough coverage estimate
    stats.coverage = stats.sourceFiles > 0
        ? Math.round((stats.testFiles / stats.sourceFiles) * 100)
        : 0;

    return { issues: allIssues, stats };
}

function main() {
    const target = process.argv[2] || '.';
    console.log(`🧪 Testing Checker - Scanning: ${target}\n`);

    const stats = fs.statSync(target);
    let result;

    if (stats.isDirectory()) {
        result = scanDirectory(target);
    } else {
        result = {
            issues: checkFile(target),
            stats: { sourceFiles: 1, testFiles: 0, coverage: 0 }
        };
    }

    const { issues, stats: testStats } = result;

    console.log(`📊 Test Statistics:`);
    console.log(`   Source Files: ${testStats.sourceFiles}`);
    console.log(`   Test Files: ${testStats.testFiles}`);
    console.log(`   Estimated Coverage: ${testStats.coverage}%`);
    console.log('');

    if (testStats.coverage < 80) {
        console.log(`⚠️  Coverage below 80% threshold\n`);
    }

    if (issues.length === 0) {
        console.log('✅ No testing issues found\n');
        process.exit(testStats.coverage >= 80 ? 0 : 2);
    }

    const critical = issues.filter(i => i.severity === 'CRITICAL');
    const high = issues.filter(i => i.severity === 'HIGH');
    const medium = issues.filter(i => i.severity === 'MEDIUM');

    console.log(`🚨 Found ${issues.length} testing issues:\n`);

    if (critical.length > 0) {
        console.log(`❌ CRITICAL (${critical.length}):`);
        critical.forEach(issue => {
            console.log(`   ${issue.file}`);
            console.log(`   ${issue.message}\n`);
        });
    }

    if (high.length > 0) {
        console.log(`⚠️  HIGH (${high.length}):`);
        high.slice(0, 5).forEach(issue => {
            console.log(`   ${issue.file} - ${issue.message}\n`);
        });
        if (high.length > 5) console.log(`   ... and ${high.length - 5} more\n`);
    }

    if (medium.length > 0) {
        console.log(`ℹ️  MEDIUM (${medium.length}) - Missing tests\n`);
    }

    process.exit(critical.length > 0 || high.length > 0 ? 1 : 2);
}

if (require.main === module) {
    main();
}

module.exports = { checkFile, scanDirectory };
