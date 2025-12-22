#!/usr/bin/env node

/**
 * Security Checker
 * Scans files for common security issues
 */

const fs = require('fs');
const path = require('path');

const SECURITY_PATTERNS = {
    // SQL Injection
    sql_injection: {
        pattern: /\$_(GET|POST|REQUEST)\[.*?\].*?(SELECT|INSERT|UPDATE|DELETE)/gi,
        severity: 'CRITICAL',
        message: 'Potential SQL injection - use prepared statements'
    },

    // XSS
    xss_echo: {
        pattern: /echo\s+\$_(GET|POST|REQUEST|COOKIE)/gi,
        severity: 'HIGH',
        message: 'Potential XSS - use htmlspecialchars() or json_encode()'
    },

    // Hardcoded Secrets
    hardcoded_password: {
        pattern: /(password|secret|api_key|token)\s*=\s*['"][^'"]{8,}['"]/gi,
        severity: 'CRITICAL',
        message: 'Hardcoded secret detected - use environment variables'
    },

    // Insecure File Operations
    file_inclusion: {
        pattern: /include\s*\(\s*\$_(GET|POST|REQUEST)/gi,
        severity: 'CRITICAL',
        message: 'Potential file inclusion vulnerability'
    },

    // Weak Crypto
    weak_hash: {
        pattern: /md5\(|sha1\(/gi,
        severity: 'HIGH',
        message: 'Weak hashing algorithm - use password_hash() or Argon2'
    },

    // Command Injection
    command_injection: {
        pattern: /(exec|shell_exec|system|passthru)\s*\(\s*\$_(GET|POST|REQUEST)/gi,
        severity: 'CRITICAL',
        message: 'Potential command injection'
    }
};

function checkFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    const lines = content.split('\n');
    const issues = [];

    Object.entries(SECURITY_PATTERNS).forEach(([name, config]) => {
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
    });

    return issues;
}

function scanDirectory(dir, extensions = ['.php', '.js', '.ts']) {
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
    console.log(`🔒 Security Checker - Scanning: ${target}\n`);

    const stats = fs.statSync(target);
    const issues = stats.isDirectory() ? scanDirectory(target) : checkFile(target);

    if (issues.length === 0) {
        console.log('✅ No security issues found\n');
        process.exit(0);
    }

    // Group by severity
    const critical = issues.filter(i => i.severity === 'CRITICAL');
    const high = issues.filter(i => i.severity === 'HIGH');

    console.log(`🚨 Found ${issues.length} security issues:\n`);

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

    process.exit(critical.length > 0 ? 1 : 2);
}

if (require.main === module) {
    main();
}

module.exports = { checkFile, scanDirectory };
