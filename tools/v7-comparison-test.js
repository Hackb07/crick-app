#!/usr/bin/env node
// v7-comparison-test.js
// Test harness to compare system behavior with and without v7 rules

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const TEST_QUERIES = [
  { type: 'simple', query: 'add a player profile page', category: 'feature' },
  { type: 'complex', query: 'implement match scoring with live updates', category: 'feature' },
  { type: 'bug', query: 'fix login authentication issue', category: 'bug' },
  { type: 'design', query: 'create a mobile-responsive match view', category: 'design' },
  { type: 'test', query: 'add unit tests for player class', category: 'test' }
];

const METRICS_DIR = 'ops/enhance/comparison';
const V7_RULES_PATH = '.cursor/rules/v7.mdc';
const V7_RULES_DISABLED = '.cursor/rules/v7.mdc.disabled';

class MetricsCollector {
  constructor() {
    this.metrics = {
      startTime: null,
      endTime: null,
      processSpawns: 0,
      fileIO: { reads: 0, writes: 0 },
      filesCreated: [],
      filesModified: [],
      errors: []
    };
  }

  start() {
    this.metrics.startTime = Date.now();
    // Track process spawns by monitoring child_process calls
    // Track file I/O by checking ops/enhance directory
  }

  stop() {
    this.metrics.endTime = Date.now();
    this.metrics.duration = this.metrics.endTime - this.metrics.startTime;
  }

  collectFileMetrics(baseDir) {
    const enhanceDir = path.join(baseDir, 'ops/enhance');
    if (!fs.existsSync(enhanceDir)) return;

    const walk = (dir) => {
      const files = fs.readdirSync(dir);
      files.forEach(file => {
        const filePath = path.join(dir, file);
        const stat = fs.statSync(filePath);
        if (stat.isDirectory()) {
          walk(filePath);
        } else {
          const mtime = stat.mtime.getTime();
          const createdAfter = mtime > this.metrics.startTime;
          if (createdAfter) {
            this.metrics.filesCreated.push(filePath);
          }
          this.metrics.fileIO.reads++;
        }
      });
    };
    walk(enhanceDir);
  }

  getMetrics() {
    return {
      ...this.metrics,
      filesCreatedCount: this.metrics.filesCreated.length,
      totalFileIO: this.metrics.fileIO.reads + this.metrics.fileIO.writes
    };
  }
}

function ensureDir(dir) {
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }
}

function disableV7Rules() {
  if (fs.existsSync(V7_RULES_PATH)) {
    // Change alwaysApply to false
    let content = fs.readFileSync(V7_RULES_PATH, 'utf8');
    content = content.replace(/alwaysApply:\s*true/, 'alwaysApply: false');
    fs.writeFileSync(V7_RULES_PATH, content);
    console.log('[TEST] v7 rules disabled (alwaysApply: false)');
    return true;
  }
  return false;
}

function enableV7Rules() {
  if (fs.existsSync(V7_RULES_PATH)) {
    let content = fs.readFileSync(V7_RULES_PATH, 'utf8');
    content = content.replace(/alwaysApply:\s*false/, 'alwaysApply: true');
    fs.writeFileSync(V7_RULES_PATH, content);
    console.log('[TEST] v7 rules enabled (alwaysApply: true)');
    return true;
  }
  return false;
}

function analyzeToolExecution(metrics, rulesEnabled = true) {
  // Analyze which tools would execute based on rules
  const analysis = {
    toolsExecuted: [],
    rulesTriggered: [],
    estimatedOverhead: {
      processSpawns: 0,
      estimatedMs: 0
    }
  };

  if (!rulesEnabled) {
    // Without v7 rules: minimal overhead (just AI processing)
    analysis.estimatedOverhead.processSpawns = 0;
    analysis.estimatedOverhead.estimatedMs = 0;
    return analysis;
  }

  // Read v7.mdc rules and check if enabled
  if (fs.existsSync(V7_RULES_PATH)) {
    const content = fs.readFileSync(V7_RULES_PATH, 'utf8');
    
    // Check if rules are enabled (alwaysApply: true)
    const alwaysApplyMatch = content.match(/alwaysApply:\s*(true|false)/);
    const isEnabled = alwaysApplyMatch && alwaysApplyMatch[1] === 'true';
    
    if (!isEnabled) {
      analysis.estimatedOverhead.processSpawns = 0;
      analysis.estimatedOverhead.estimatedMs = 0;
      return analysis;
    }

    // Parse JSON to get accurate structure
    try {
      const jsonStart = content.indexOf('{');
      const jsonContent = content.substring(jsonStart);
      const rulesData = JSON.parse(jsonContent);
      
      if (rulesData.rules && Array.isArray(rulesData.rules)) {
        let autoRunCount = 0;
        
        rulesData.rules.forEach(rule => {
          // Check if rule matches the query pattern
          const matches = rule.when && rule.when.matches;
          const isAlwaysTrigger = matches === '.*';
          const isConditional = matches && matches !== '.*';
          
          // For this analysis, consider all auto_run rules that would execute
          if (rule.then && rule.then.auto_run === true) {
            // Check if rule would trigger
            if (isAlwaysTrigger) {
              autoRunCount++;
              analysis.toolsExecuted.push(rule.id);
              analysis.rulesTriggered.push(rule.id);
            } else if (isConditional) {
              // Conditional rules may or may not trigger (estimate 50% for average)
              // But for simplicity in comparison, we'll count all auto_run rules
              autoRunCount++;
            }
          }
        });
        
        analysis.estimatedOverhead.processSpawns = autoRunCount;
        // Estimate: each spawn ~50-100ms, file I/O ~10-20ms per operation
        // Average: 75ms per spawn
        analysis.estimatedOverhead.estimatedMs = autoRunCount * 75;
      }
    } catch (e) {
      // Fallback to regex parsing
      const autoRunMatches = content.match(/auto_run["\s]*:\s*true/g);
      if (autoRunMatches) {
        analysis.estimatedOverhead.processSpawns = autoRunMatches.length;
        analysis.estimatedOverhead.estimatedMs = autoRunMatches.length * 75;
      }
    }
  }

  return analysis;
}

function runComparison() {
  ensureDir(METRICS_DIR);
  
  const results = {
    without: [],
    with: [],
    summary: {}
  };

  console.log('=== v7 Rules Comparison Test ===\n');

  // Phase 1: Test WITHOUT v7 rules
  console.log('Phase 1: Testing WITHOUT v7 rules...');
  disableV7Rules();
  
  for (const testCase of TEST_QUERIES) {
    const collector = new MetricsCollector();
    collector.start();
    
    // Analyze without v7 rules enabled
    const analysis = analyzeToolExecution(collector, false);
    
    collector.stop();
    collector.collectFileMetrics(process.cwd());
    
    const metrics = collector.getMetrics();
    results.without.push({
      ...testCase,
      metrics,
      analysis,
      mode: 'without'
    });
    
    console.log(`  ✓ ${testCase.type}: ${testCase.query.substring(0, 40)}... (overhead: ${analysis.estimatedOverhead.estimatedMs}ms)`);
  }

  // Phase 2: Test WITH v7 rules
  console.log('\nPhase 2: Testing WITH v7 rules...');
  enableV7Rules();
  
  for (const testCase of TEST_QUERIES) {
    const collector = new MetricsCollector();
    collector.start();
    
    // Analyze with v7 rules enabled
    const analysis = analyzeToolExecution(collector, true);
    
    collector.stop();
    collector.collectFileMetrics(process.cwd());
    
    const metrics = collector.getMetrics();
    results.with.push({
      ...testCase,
      metrics,
      analysis,
      mode: 'with'
    });
    
    console.log(`  ✓ ${testCase.type}: ${testCase.query.substring(0, 40)}... (overhead: ${analysis.estimatedOverhead.estimatedMs}ms)`);
  }

  // Calculate summary
  const avgWithout = results.without.reduce((sum, r) => sum + (r.metrics.duration || 0), 0) / results.without.length;
  const avgWith = results.with.reduce((sum, r) => sum + (r.metrics.duration || 0), 0) / results.with.length;
  const avgOverheadWithout = results.without.reduce((sum, r) => sum + (r.analysis.estimatedOverhead.estimatedMs || 0), 0) / results.without.length;
  const avgOverheadWith = results.with.reduce((sum, r) => sum + (r.analysis.estimatedOverhead.estimatedMs || 0), 0) / results.with.length;

  results.summary = {
    avgDurationWithout: avgWithout,
    avgDurationWith: avgWith,
    avgOverheadWithout: avgOverheadWithout,
    avgOverheadWith: avgOverheadWith,
    overheadDifference: avgOverheadWith - avgOverheadWithout,
    percentageIncrease: ((avgOverheadWith - avgOverheadWithout) / avgOverheadWithout * 100) || 0
  };

  // Save results
  const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
  const resultsFile = path.join(METRICS_DIR, `comparison-${timestamp}.json`);
  fs.writeFileSync(resultsFile, JSON.stringify(results, null, 2));
  
  console.log(`\n=== Results Summary ===`);
  console.log(`Average estimated overhead WITHOUT: ${avgOverheadWithout.toFixed(2)}ms`);
  console.log(`Average estimated overhead WITH: ${avgOverheadWith.toFixed(2)}ms`);
  console.log(`Overhead difference: ${results.summary.overheadDifference.toFixed(2)}ms`);
  console.log(`Percentage increase: ${results.summary.percentageIncrease.toFixed(2)}%`);
  console.log(`\nResults saved to: ${resultsFile}`);
  
  return results;
}

// Run if executed directly
if (require.main === module) {
  try {
    const results = runComparison();
    process.exit(0);
  } catch (error) {
    console.error('[ERROR]', error);
    process.exit(1);
  }
}

module.exports = { runComparison, MetricsCollector };

