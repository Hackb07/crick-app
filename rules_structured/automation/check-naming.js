#!/usr/bin/env node

/**
 * Naming Convention Checker
 * Validates naming conventions across different languages
 */

const fs = require('fs');
const path = require('path');

const NAMING_RULES = {
    // PHP
    php: {
        class: /^[A-Z][a-zA-Z0-9]*$/,  // PascalCase
        function: /^[a-z][a-zA-Z0-9]*$/,  // camelCase
        variable: /^[a-z][a-zA-Z0-9]*$/,  // camelCase
        constant: /^[A-Z][A-Z0-9_]*$/,  // UPPER_SNAKE_CASE
    },

    // JavaScript/TypeScript
    js: {
        class: /^[A-Z][a-zA-Z0-9]*$/,  // PascalCase
        function: /^[a-z][a-zA-Z0-9]*$/,  // camelCase
        variable: /^[a-z][a-zA-Z0-9]*$/,  // camelCase
        constant: /^[A-Z][A-Z0-9_]*$/,  // UPPER_SNAKE_CASE
    }
};

const BAD_NAMES = [
    // Generic names
    'data', 'temp', 'tmp', 'obj', 'item', 'thing', 'stuff',
    // Single letters (except loop counters)
    /^[a-z]$/,
    // Abbreviations
    'btn', 'txt', 'img', 'usr', 'pwd', 'msg'
];

function checkPHPFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    const lines = content.split('\n');
    const issues = [];

    lines.forEach((line, index) => {
        // Check class names
        const classMatch = line.match(/class\s+([A-Za-z0-9_]+)/);
        if (classMatch) {
            const className = classMatch[1];
            if (!NAMING_RULES.php.class.test(className)) {
                issues.push({
                    file: filePath,
                    line: index + 1,
                    type: 'class',
                    name: className,
                    message: 'Class names should be PascalCase',
                    code: line.trim()
                });
            }
        }

        // Check function names
        const functionMatch = line.match(/function\s+([A-Za-z0-9_]+)/);
        if (functionMatch) {
            const funcName = functionMatch[1];
            if (funcName.startsWith('__')) return; // Skip magic methods

            if (!NAMING_RULES.php.function.test(funcName)) {
                issues.push({
                    file: filePath,
                    line: index + 1,
                    type: 'function',
                    name: funcName,
                    message: 'Function names should be camelCase',
                    code: line.trim()
                });
            }
        }

        // Check for bad variable names
        const varMatches = line.matchAll(/\$([a-zA-Z_][a-zA-Z0-9_]*)/g);
        for (const match of varMatches) {
            const varName = match[1];

            // Skip superglobals
            if (['_GET', '_POST', '_REQUEST', '_SESSION', '_COOKIE', '_SERVER'].includes(varName)) {
                continue;
            }

            // Check against bad names
            BAD_NAMES.forEach(badName => {
                if (typeof badName === 'string' && varName === badName) {
                    issues.push({
                        file: filePath,
                        line: index + 1,
                        type: 'variable',
                        name: varName,
                        message: `Avoid generic name "${varName}" - use descriptive names`,
                        code: line.trim()
                    });
                } else if (badName instanceof RegExp && badName.test(varName)) {
                    issues.push({
                        file: filePath,
                        line: index + 1,
                        type: 'variable',
                        name: varName,
                        message: 'Avoid single-letter variable names (except loop counters)',
                        code: line.trim()
                    });
                }
            });
        }
    });

    return issues;
}

function checkJSFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    const lines = content.split('\n');
    const issues = [];

    lines.forEach((line, index) => {
        // Check class names
        const classMatch = line.match(/class\s+([A-Za-z0-9_]+)/);
        if (classMatch) {
            const className = classMatch[1];
            if (!NAMING_RULES.js.class.test(className)) {
                issues.push({
                    file: filePath,
                    line: index + 1,
                    type: 'class',
                    name: className,
                    message: 'Class names should be PascalCase',
                    code: line.trim()
                });
            }
        }

        // Check function names
        const functionMatch = line.match(/function\s+([A-Za-z0-9_]+)/);
        if (functionMatch) {
            const funcName = functionMatch[1];
            if (!NAMING_RULES.js.function.test(funcName)) {
                issues.push({
                    file: filePath,
                    line: index + 1,
                    type: 'function',
                    name: funcName,
                    message: 'Function names should be camelCase',
                    code: line.trim()
                });
            }
        }

        // Check const declarations
        const constMatch = line.match(/const\s+([A-Za-z0-9_]+)/);
        if (constMatch) {
            const constName = constMatch[1];
            // If it looks like a constant (all caps), validate it
            if (constName === constName.toUpperCase() && !NAMING_RULES.js.constant.test(constName)) {
                issues.push({
                    file: filePath,
                    line: index + 1,
                    type: 'constant',
                    name: constName,
                    message: 'Constants should be UPPER_SNAKE_CASE',
                    code: line.trim()
                });
            }
        }
    });

    return issues;
}

function scanDirectory(dir) {
    let allIssues = [];

    function scan(currentDir) {
        const entries = fs.readdirSync(currentDir, { withFileTypes: true });

        entries.forEach(entry => {
            const fullPath = path.join(currentDir, entry.name);

            if (entry.isDirectory() && !entry.name.startsWith('.') && entry.name !== 'node_modules') {
                scan(fullPath);
            } else if (entry.isFile()) {
                let issues = [];
                if (entry.name.endsWith('.php')) {
                    issues = checkPHPFile(fullPath);
                } else if (entry.name.endsWith('.js') || entry.name.endsWith('.ts')) {
                    issues = checkJSFile(fullPath);
                }
                allIssues = allIssues.concat(issues);
            }
        });
    }

    scan(dir);
    return allIssues;
}

function main() {
    const target = process.argv[2] || '.';
    console.log(`📝 Naming Convention Checker - Scanning: ${target}\n`);

    const stats = fs.statSync(target);
    let issues = [];

    if (stats.isDirectory()) {
        issues = scanDirectory(target);
    } else if (target.endsWith('.php')) {
        issues = checkPHPFile(target);
    } else if (target.endsWith('.js') || target.endsWith('.ts')) {
        issues = checkJSFile(target);
    }

    if (issues.length === 0) {
        console.log('✅ All naming conventions followed\n');
        process.exit(0);
    }

    console.log(`⚠️  Found ${issues.length} naming issues:\n`);

    issues.forEach(issue => {
        console.log(`   ${issue.file}:${issue.line}`);
        console.log(`   ${issue.type}: "${issue.name}"`);
        console.log(`   ${issue.message}`);
        console.log(`   ${issue.code}\n`);
    });

    process.exit(2); // Warning, not critical
}

if (require.main === module) {
    main();
}

module.exports = { checkPHPFile, checkJSFile, scanDirectory };
