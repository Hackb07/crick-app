#!/usr/bin/env node

/**
 * UI/UX Design Checker
 * Validates design standards: accessibility, responsiveness, semantic HTML
 */

const fs = require('fs');
const path = require('path');

const UI_DESIGN_RULES = {
    // Missing alt text on images
    missing_alt_text: {
        pattern: /<img(?![^>]*alt=)[^>]*>/gi,
        severity: 'HIGH',
        message: 'Accessibility violation - <img> missing alt attribute'
    },

    // Non-semantic HTML (div/span soup)
    non_semantic_html: {
        check: (file, content) => {
            const divCount = (content.match(/<div/gi) || []).length;
            const semanticCount = (content.match(/<(header|nav|main|article|section|aside|footer)/gi) || []).length;
            return divCount > 10 && semanticCount === 0;
        },
        severity: 'MEDIUM',
        message: 'Non-semantic HTML - use <header>, <nav>, <main>, <article>, <section> instead of <div>'
    },

    // Missing ARIA labels on interactive elements
    missing_aria_label: {
        pattern: /<button(?![^>]*aria-label=)(?![^>]*>.*<\/button>)[^>]*\/>/gi,
        severity: 'HIGH',
        message: 'Accessibility violation - icon-only button missing aria-label'
    },

    // Inline styles (should use CSS)
    inline_styles: {
        pattern: /style=["'][^"']{50,}["']/g,
        severity: 'MEDIUM',
        message: 'Maintainability issue - excessive inline styles (use CSS classes)'
    },

    // Missing viewport meta tag
    missing_viewport: {
        check: (file, content) => {
            const isHTML = /<html/i.test(content);
            const hasViewport = /<meta[^>]*name=["']viewport["']/i.test(content);
            return isHTML && !hasViewport;
        },
        severity: 'HIGH',
        message: 'Responsive design violation - missing viewport meta tag'
    },

    // Hardcoded colors (should use CSS variables)
    hardcoded_colors: {
        pattern: /(background-color|color|border-color):\s*#[0-9a-f]{3,6}/gi,
        severity: 'LOW',
        message: 'Design tokens violation - use CSS variables (--primary-color) instead of hardcoded colors'
    },

    // Missing form labels
    missing_form_labels: {
        pattern: /<input(?![^>]*id=)(?![^>]*type=["'](hidden|submit|button)["'])/gi,
        severity: 'HIGH',
        message: 'Accessibility violation - form input missing id (required for <label>)'
    },

    // Non-responsive units (px instead of rem/em)
    non_responsive_units: {
        pattern: /font-size:\s*\d+px/gi,
        severity: 'LOW',
        message: 'Responsive design - use rem/em for font-size instead of px'
    },

    // Missing heading hierarchy
    missing_h1: {
        check: (file, content) => {
            const isHTML = /<html/i.test(content);
            const hasH1 = /<h1/i.test(content);
            return isHTML && !hasH1;
        },
        severity: 'MEDIUM',
        message: 'SEO/Accessibility violation - page missing <h1> tag'
    },

    // Inaccessible color contrast (placeholder check)
    low_contrast_text: {
        pattern: /color:\s*(#fff|white|#f{3,6}).*background(-color)?:\s*(#fff|white|#f{3,6})/gi,
        severity: 'HIGH',
        message: 'Accessibility violation - potential low contrast (white on white)'
    },

    // Missing lang attribute
    missing_lang: {
        check: (file, content) => {
            const hasHTML = /<html/i.test(content);
            const hasLang = /<html[^>]*lang=/i.test(content);
            return hasHTML && !hasLang;
        },
        severity: 'MEDIUM',
        message: 'Accessibility violation - <html> missing lang attribute'
    },

    // Tables without proper structure
    improper_table: {
        pattern: /<table(?![^>]*<thead)/gi,
        severity: 'MEDIUM',
        message: 'Accessibility violation - <table> missing <thead> (use semantic table structure)'
    }
};

function checkFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    const lines = content.split('\n');
    const issues = [];

    Object.entries(UI_DESIGN_RULES).forEach(([name, config]) => {
        // Pattern-based checks
        if (config.pattern) {
            lines.forEach((line, index) => {
                const matches = line.match(config.pattern);
                if (matches) {
                    issues.push({
                        file: filePath,
                        line: index + 1,
                        severity: config.severity,
                        issue: name,
                        message: config.message,
                        code: line.trim().substring(0, 80)
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

function scanDirectory(dir, extensions = ['.html', '.php', '.jsx', '.tsx', '.vue']) {
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
    console.log(`🎨 UI/UX Design Checker - Scanning: ${target}\n`);

    const stats = fs.statSync(target);
    const issues = stats.isDirectory() ? scanDirectory(target) : checkFile(target);

    if (issues.length === 0) {
        console.log('✅ No UI/UX violations found\n');
        process.exit(0);
    }

    const high = issues.filter(i => i.severity === 'HIGH');
    const medium = issues.filter(i => i.severity === 'MEDIUM');
    const low = issues.filter(i => i.severity === 'LOW');

    console.log(`🚨 Found ${issues.length} UI/UX issues:\n`);

    if (high.length > 0) {
        console.log(`❌ HIGH (${high.length}) - Accessibility/Responsive:`);
        high.slice(0, 10).forEach(issue => {
            console.log(`   ${issue.file}:${issue.line}`);
            console.log(`   ${issue.message}`);
            console.log(`   ${issue.code}\n`);
        });
        if (high.length > 10) console.log(`   ... and ${high.length - 10} more\n`);
    }

    if (medium.length > 0) {
        console.log(`⚠️  MEDIUM (${medium.length}) - SEO/Semantics:`);
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

module.exports = { checkFile, scanDirectory };
