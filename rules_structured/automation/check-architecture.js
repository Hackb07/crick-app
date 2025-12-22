#!/usr/bin/env node

/**
 * Architecture Checker
 * Validates architectural rules: boundaries, dependencies, coupling
 */

const fs = require('fs');
const path = require('path');

const ARCHITECTURE_RULES = {
    // Circular Dependencies
    circular_dependency: {
        pattern: /require\(['"]\.\.\/.*['"]\)|import.*from\s+['"]\.\.\/.*['"]/g,
        check: (file, content) => {
            const imports = content.match(/(?:require|import).*?['"]([^'"]+)['"]/g) || [];
            const relativePaths = imports.filter(imp => imp.includes('../'));
            return relativePaths.length > 3; // Flag if too many parent references
        },
        severity: 'HIGH',
        message: 'Potential circular dependency - excessive parent directory imports'
    },

    // Tight Coupling (Direct DB access in presentation layer)
    tight_coupling_db: {
        pattern: /(SELECT|INSERT|UPDATE|DELETE).*FROM/gi,
        check: (file, content) => {
            // Check if file is in presentation/UI layer but has SQL
            const isUILayer = /\/(views|components|pages|ui)\//i.test(file);
            const hasSQL = /(SELECT|INSERT|UPDATE|DELETE).*FROM/gi.test(content);
            return isUILayer && hasSQL;
        },
        severity: 'CRITICAL',
        message: 'Tight coupling - SQL queries in presentation layer (use services/repositories)'
    },

    // Missing Dependency Injection
    no_dependency_injection: {
        pattern: /new\s+(Database|Connection|PDO|mysqli)\(/g,
        severity: 'HIGH',
        message: 'Missing dependency injection - instantiating dependencies directly'
    },

    // God Class (too many methods)
    god_class: {
        check: (file, content) => {
            const methodCount = (content.match(/function\s+\w+\s*\(/g) || []).length;
            const classMatch = content.match(/class\s+(\w+)/);
            return classMatch && methodCount > 20;
        },
        severity: 'HIGH',
        message: 'God class detected - class has >20 methods (violates Single Responsibility)'
    },

    // Missing AIS Documentation
    missing_ais: {
        check: (file, content) => {
            const hasClass = /class\s+\w+/.test(content);
            const hasAIS = /@purpose|@module|@architecture/i.test(content);
            return hasClass && !hasAIS && content.length > 500;
        },
        severity: 'MEDIUM',
        message: 'Missing AIS documentation - add @purpose, @module, @dependencies'
    },

    // Cross-layer violation (Controller accessing DB directly)
    cross_layer_violation: {
        check: (file, content) => {
            const isController = /controller/i.test(file);
            const hasDirectDB = /\$pdo|mysqli_|pg_query/i.test(content);
            return isController && hasDirectDB;
        },
        severity: 'CRITICAL',
        message: 'Cross-layer violation - Controller accessing database directly (use services)'
    },

    // Hardcoded Configuration
    hardcoded_config: {
        pattern: /(localhost|127\.0\.0\.1|3306|5432|mongodb:\/\/)/g,
        severity: 'HIGH',
        message: 'Hardcoded configuration - use environment variables or config files'
    }
};

function checkFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    const lines = content.split('\n');
    const issues = [];

    Object.entries(ARCHITECTURE_RULES).forEach(([name, config]) => {
        // Pattern-based checks
        if (config.pattern) {
            lines.forEach((line, index) => {
                if (config.pattern.test(line)) {
                    issues.push({
                        file: filePath,
                        line: index + 1,
                        severity: config.severity,
                        issue: name,
                        message: config.message,
                        code: line.trim()
                    });
                }
            });
        }

        // Custom check functions
        if (config.check && config.check(filePath, content)) {
            issues.push({
                file: filePath,
                line: 1,
                severity: config.severity,
                issue: name,
                message: config.message,
                code: '(See file)'
            });
        }
    });

    return issues;
}

function scanDirectory(dir, extensions = ['.php', '.js', '.ts', '.jsx', '.tsx']) {
    let allIssues = [];

    function scan(currentDir) {
        const entries = fs.readdirSync(currentDir, { withFileTypes: true });

        entries.forEach(entry => {
            const fullPath = path.join(currentDir, entry.name);

            if (entry.isDirectory() && !entry.name.startsWith('.') && entry.name !== 'node_modules') {
                scan(fullPath);
            } else if (entry.isFile() && extensions.some(ext => entry.name.endsWith(ext))) {
                const issues = checkFile(fullPath);
                allIssues = allIssues.concat(issues);
            }
        });
    }

    scan(dir);
    return allIssues;
}

function main() {
    const target = process.argv[2] || '.';
    console.log(`🏗️  Architecture Checker - Scanning: ${target}\n`);

    const stats = fs.statSync(target);
    const issues = stats.isDirectory() ? scanDirectory(target) : checkFile(target);

    if (issues.length === 0) {
        console.log('✅ No architecture violations found\n');
        process.exit(0);
    }

    const critical = issues.filter(i => i.severity === 'CRITICAL');
    const high = issues.filter(i => i.severity === 'HIGH');
    const medium = issues.filter(i => i.severity === 'MEDIUM');

    console.log(`🚨 Found ${issues.length} architecture issues:\n`);

    if (critical.length > 0) {
        console.log(`❌ CRITICAL (${critical.length}):`);
        critical.forEach(issue => {
            console.log(`   ${issue.file}:${issue.line}`);
            console.log(`   ${issue.message}`);
            console.log(`   ${issue.code}\n`);
        });
    }

    if (high.length > 0) {
        console.log(`⚠️  HIGH (${high.length}):`);
        high.forEach(issue => {
            console.log(`   ${issue.file}:${issue.line}`);
            console.log(`   ${issue.message}`);
            console.log(`   ${issue.code}\n`);
        });
    }

    if (medium.length > 0) {
        console.log(`ℹ️  MEDIUM (${medium.length}):`);
        medium.slice(0, 5).forEach(issue => {
            console.log(`   ${issue.file}:${issue.line}`);
            console.log(`   ${issue.message}\n`);
        });
        if (medium.length > 5) {
            console.log(`   ... and ${medium.length - 5} more\n`);
        }
    }

    process.exit(critical.length > 0 ? 1 : 2);
}

if (require.main === module) {
    main();
}

module.exports = { checkFile, scanDirectory };
