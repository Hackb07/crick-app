#!/usr/bin/env node

/**
 * Code Quality Checker
 * Validates code quality: complexity, function size, duplication
 */

const fs = require('fs');
const path = require('path');

function calculateComplexity(code) {
    // McCabe Cyclomatic Complexity
    const decisionPoints = (code.match(/if|else|for|while|case|catch|\?\?|\|\||&&/g) || []).length;
    return decisionPoints + 1;
}

function checkFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    const lines = content.split('\n');
    const issues = [];

    // Extract functions
    const functionRegex = /function\s+(\w+)\s*\([^)]*\)\s*{/g;
    let match;
    const functions = [];

    while ((match = functionRegex.exec(content)) !== null) {
        const funcName = match[1];
        const startIndex = match.index;

        // Find function end (simple brace matching)
        let braceCount = 1;
        let endIndex = startIndex + match[0].length;

        while (braceCount > 0 && endIndex < content.length) {
            if (content[endIndex] === '{') braceCount++;
            if (content[endIndex] === '}') braceCount--;
            endIndex++;
        }

        const funcCode = content.substring(startIndex, endIndex);
        const funcLines = funcCode.split('\n');
        const lineNumber = content.substring(0, startIndex).split('\n').length;

        functions.push({
            name: funcName,
            code: funcCode,
            lineCount: funcLines.length,
            lineNumber: lineNumber,
            complexity: calculateComplexity(funcCode)
        });
    }

    // Check each function
    functions.forEach(func => {
        // Complexity check
        if (func.complexity > 10) {
            issues.push({
                file: filePath,
                line: func.lineNumber,
                severity: 'HIGH',
                issue: 'high_complexity',
                message: `Function "${func.name}" has complexity ${func.complexity} (max: 10)`,
                code: `function ${func.name}()`
            });
        }

        // Function size check
        if (func.lineCount > 50) {
            issues.push({
                file: filePath,
                line: func.lineNumber,
                severity: 'MEDIUM',
                issue: 'large_function',
                message: `Function "${func.name}" has ${func.lineCount} lines (max: 50)`,
                code: `function ${func.name}()`
            });
        }

        // Deep nesting check
        const nestingLevel = (func.code.match(/{/g) || []).length;
        if (nestingLevel > 4) {
            issues.push({
                file: filePath,
                line: func.lineNumber,
                severity: 'MEDIUM',
                issue: 'deep_nesting',
                message: `Function "${func.name}" has ${nestingLevel} nesting levels (max: 3)`,
                code: `function ${func.name}()`
            });
        }
    });

    // Check for code duplication (simple line-based)
    const codeLines = lines.filter(l => l.trim() && !l.trim().startsWith('//'));
    const lineMap = new Map();

    codeLines.forEach((line, index) => {
        const trimmed = line.trim();
        if (trimmed.length > 20) { // Only check substantial lines
            if (!lineMap.has(trimmed)) {
                lineMap.set(trimmed, []);
            }
            lineMap.get(trimmed).push(index + 1);
        }
    });

    lineMap.forEach((lineNumbers, code) => {
        if (lineNumbers.length >= 3) {
            issues.push({
                file: filePath,
                line: lineNumbers[0],
                severity: 'LOW',
                issue: 'code_duplication',
                message: `Code duplicated ${lineNumbers.length} times - consider extracting to function`,
                code: code.substring(0, 60)
            });
        }
    });

    // Magic numbers check
    lines.forEach((line, index) => {
        const magicNumbers = line.match(/[^a-zA-Z_](\d{2,})[^a-zA-Z_\d]/g);
        if (magicNumbers && !line.includes('//') && !line.includes('const')) {
            issues.push({
                file: filePath,
                line: index + 1,
                severity: 'LOW',
                issue: 'magic_number',
                message: 'Magic number detected - extract to named constant',
                code: line.trim()
            });
        }
    });

    return issues;
}

function scanDirectory(dir, extensions = ['.php', '.js', '.ts', '.py']) {
    let allIssues = [];

    function scan(currentDir) {
        const entries = fs.readdirSync(currentDir, { withFileTypes: true });

        entries.forEach(entry => {
            const fullPath = path.join(currentDir, entry.name);

            if (entry.isDirectory() && !entry.name.startsWith('.') && entry.name !== 'node_modules') {
                scan(fullPath);
            } else if (entry.isFile() && extensions.some(ext => entry.name.endsWith(ext))) {
                try {
                    const issues = checkFile(fullPath);
                    allIssues = allIssues.concat(issues);
                } catch (err) {
                    // Skip files that can't be parsed
                }
            }
        });
    }

    scan(dir);
    return allIssues;
}

function main() {
    const target = process.argv[2] || '.';
    console.log(`📊 Code Quality Checker - Scanning: ${target}\n`);

    const stats = fs.statSync(target);
    const issues = stats.isDirectory() ? scanDirectory(target) : checkFile(target);

    if (issues.length === 0) {
        console.log('✅ No code quality issues found\n');
        process.exit(0);
    }

    const high = issues.filter(i => i.severity === 'HIGH');
    const medium = issues.filter(i => i.severity === 'MEDIUM');
    const low = issues.filter(i => i.severity === 'LOW');

    console.log(`🚨 Found ${issues.length} code quality issues:\n`);

    if (high.length > 0) {
        console.log(`❌ HIGH (${high.length}) - Complexity:`);
        high.forEach(issue => {
            console.log(`   ${issue.file}:${issue.line}`);
            console.log(`   ${issue.message}\n`);
        });
    }

    if (medium.length > 0) {
        console.log(`⚠️  MEDIUM (${medium.length}) - Maintainability:`);
        medium.slice(0, 5).forEach(issue => {
            console.log(`   ${issue.file}:${issue.line} - ${issue.message}\n`);
        });
        if (medium.length > 5) console.log(`   ... and ${medium.length - 5} more\n`);
    }

    if (low.length > 0) {
        console.log(`ℹ️  LOW (${low.length}) - Best Practices\n`);
    }

    process.exit(high.length > 0 ? 1 : 2);
}

if (require.main === module) {
    main();
}

module.exports = { checkFile, scanDirectory, calculateComplexity };
