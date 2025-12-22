#!/usr/bin/env node

/**
 * Performance Checker
 * Validates performance best practices
 */

const fs = require('fs');
const path = require('path');

const PERFORMANCE_RULES = {
  // Animation Performance
  use_request_animation_frame: {
    pattern: /setInterval\s*\(\s*(?:function|\(\)|\w+)/g,
    check: (file, content) => {
      // Check if file has animation/game loop
      const hasAnimationKeywords = /animate|draw|render|gameLoop|update.*draw/i.test(content);
      const usesSetInterval = /setInterval/g.test(content);
      return hasAnimationKeywords && usesSetInterval;
    },
    severity: 'HIGH',
    message: 'Use requestAnimationFrame instead of setInterval for animations (better performance, 60 FPS sync)'
  },

  // Avoid setTimeout in loops
  settimeout_in_loop: {
    pattern: /for\s*\([^)]*\)\s*{[^}]*setTimeout/gs,
    severity: 'MEDIUM',
    message: 'Avoid setTimeout in loops - consider Promise.all or async/await'
  },

  // Memory Leaks - Event Listeners
  missing_event_cleanup: {
    check: (file, content) => {
      const hasAddEventListener = /addEventListener/g.test(content);
      const hasRemoveEventListener = /removeEventListener/g.test(content);
      const hasCleanup = /cleanup|destroy|unmount|componentWillUnmount/i.test(content);

      return hasAddEventListener && !hasRemoveEventListener && !hasCleanup;
    },
    severity: 'HIGH',
    message: 'Potential memory leak - addEventListener without removeEventListener or cleanup'
  },

  // Inefficient DOM queries
  repeated_dom_queries: {
    check: (file, content) => {
      const lines = content.split('\n');
      const domQueries = {};

      lines.forEach((line, index) => {
        const matches = line.match(/document\.(getElementById|querySelector|querySelectorAll)\(['"]([^'"]+)['"]\)/g);
        if (matches) {
          matches.forEach(match => {
            if (!domQueries[match]) {
              domQueries[match] = [];
            }
            domQueries[match].push(index + 1);
          });
        }
      });

      // Check if same query appears multiple times
      return Object.values(domQueries).some(lines => lines.length > 2);
    },
    severity: 'MEDIUM',
    message: 'Repeated DOM queries - cache the result in a variable'
  },

  // Large arrays/objects in loops
  array_creation_in_loop: {
    pattern: /for\s*\([^)]*\)\s*{[^}]*(?:new Array|\[\]|new Object|\{\})/gs,
    severity: 'MEDIUM',
    message: 'Creating arrays/objects in loops - consider pre-allocation or moving outside loop'
  },

  // Synchronous operations
  sync_file_operations: {
    pattern: /fs\.readFileSync|fs\.writeFileSync|fs\.existsSync/g,
    severity: 'MEDIUM',
    message: 'Synchronous file operations block event loop - use async versions'
  },

  // Console.log in production
  console_log_production: {
    pattern: /console\.(log|debug|info)\(/g,
    check: (file, content) => {
      const isProduction = /production|prod|dist/i.test(file);
      const hasConsoleLog = /console\.(log|debug|info)/g.test(content);
      return isProduction && hasConsoleLog;
    },
    severity: 'LOW',
    message: 'Remove console.log in production code (performance impact)'
  },

  // Inefficient string concatenation
  string_concat_loop: {
    pattern: /for\s*\([^)]*\)\s*{[^}]*\+=/gs,
    check: (file, content) => {
      const hasLoop = /for\s*\([^)]*\)\s*{/g.test(content);
      const hasStringConcat = /\w+\s*\+=\s*['"`]/g.test(content);
      return hasLoop && hasStringConcat;
    },
    severity: 'MEDIUM',
    message: 'String concatenation in loop - use array.join() or template literals'
  }
};

function checkFile(filePath) {
  const content = fs.readFileSync(filePath, 'utf8');
  const lines = content.split('\n');
  const issues = [];

  Object.entries(PERFORMANCE_RULES).forEach(([name, config]) => {
    // Pattern-based checks
    if (config.pattern && !config.check) {
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

function scanDirectory(dir, extensions = ['.js', '.ts', '.jsx', '.tsx']) {
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
  console.log(`⚡ Performance Checker - Scanning: ${target}\n`);

  const stats = fs.statSync(target);
  const issues = stats.isDirectory() ? scanDirectory(target) : checkFile(target);

  if (issues.length === 0) {
    console.log('✅ No performance issues found\n');
    process.exit(0);
  }

  const high = issues.filter(i => i.severity === 'HIGH');
  const medium = issues.filter(i => i.severity === 'MEDIUM');
  const low = issues.filter(i => i.severity === 'LOW');

  console.log(`🚨 Found ${issues.length} performance issues:\n`);

  if (high.length > 0) {
    console.log(`❌ HIGH (${high.length}) - Performance/Memory:`);
    high.forEach(issue => {
      console.log(`   ${issue.file}:${issue.line}`);
      console.log(`   ${issue.message}`);
      console.log(`   ${issue.code}\n`);
    });
  }

  if (medium.length > 0) {
    console.log(`⚠️  MEDIUM (${medium.length}) - Optimization:`);
    medium.slice(0, 5).forEach(issue => {
      console.log(`   ${issue.file}:${issue.line}`);
      console.log(`   ${issue.message}\n`);
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
