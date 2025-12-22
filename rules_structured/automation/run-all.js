#!/usr/bin/env node

/**
 * Master Script - Runs All Checks
 * Usage: node run-all.js <directory>
 */

const { spawn } = require('child_process');
const path = require('path');

const CHECKS = [
    { name: 'Security', script: 'check-security.js', critical: true, category: '@sec' },
    { name: 'Architecture', script: 'check-architecture.js', critical: true, category: '@arch' },
    { name: 'AI Governance', script: 'check-ai-governance.js', critical: true, category: '@ai' },
    { name: 'Performance', script: 'check-performance.js', critical: false, category: '@ops' },
    { name: 'Code Quality', script: 'check-code-quality.js', critical: false, category: '@quality' },
    { name: 'Naming', script: 'check-naming.js', critical: false, category: '@quality' },
    { name: 'UI/UX Design', script: 'check-ui-design.js', critical: false, category: '@design' },
    { name: 'Testing', script: 'check-testing.js', critical: false, category: '@test' },
];

function runCheck(scriptPath, target) {
    return new Promise((resolve) => {
        const child = spawn('node', [scriptPath, target], {
            stdio: 'inherit',
            shell: true
        });

        child.on('close', (code) => {
            resolve(code);
        });

        child.on('error', (err) => {
            console.error(`Error running ${scriptPath}:`, err);
            resolve(1);
        });
    });
}

async function main() {
    const target = process.argv[2] || '.';
    const scriptDir = __dirname;

    console.log('🚀 Running All Rule Checks\n');
    console.log(`Target: ${path.resolve(target)}\n`);
    console.log('═'.repeat(50));
    console.log('');

    const results = [];

    for (const check of CHECKS) {
        const scriptPath = path.join(scriptDir, check.script);
        console.log(`\n${'─'.repeat(50)}`);
        console.log(`Running: ${check.name}`);
        console.log('─'.repeat(50));

        const exitCode = await runCheck(scriptPath, target);
        results.push({
            name: check.name,
            exitCode,
            critical: check.critical,
            status: exitCode === 0 ? 'PASS' : exitCode === 1 ? 'FAIL' : 'WARN'
        });
    }

    // Summary
    console.log('\n\n' + '═'.repeat(50));
    console.log('📊 SUMMARY');
    console.log('═'.repeat(50));
    console.log('');

    results.forEach(result => {
        const icon = result.status === 'PASS' ? '✅' : result.status === 'FAIL' ? '❌' : '⚠️';
        console.log(`${icon} ${result.name}: ${result.status}`);
    });

    console.log('');

    // Determine overall exit code
    const hasCriticalFailures = results.some(r => r.critical && r.exitCode === 1);
    const hasWarnings = results.some(r => r.exitCode === 2);

    if (hasCriticalFailures) {
        console.log('❌ CRITICAL FAILURES DETECTED - Fix before committing\n');
        process.exit(1);
    } else if (hasWarnings) {
        console.log('⚠️  WARNINGS FOUND - Review recommended\n');
        process.exit(2);
    } else {
        console.log('✅ ALL CHECKS PASSED\n');
        process.exit(0);
    }
}

if (require.main === module) {
    main();
}
