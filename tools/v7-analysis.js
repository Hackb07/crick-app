#!/usr/bin/env node
// v7-analysis.js
// Analyze v7.mdc rules for efficiency issues

const fs = require('fs');
const path = require('path');

const V7_RULES_PATH = '.cursor/rules/v7.mdc';

function analyzeV7Rules() {
  if (!fs.existsSync(V7_RULES_PATH)) {
    console.error('v7.mdc not found');
    process.exit(1);
  }

  const content = fs.readFileSync(V7_RULES_PATH, 'utf8');
  const analysis = {
    totalRules: 0,
    autoRunRules: 0,
    alwaysTriggered: 0, // Rules matching .*
    conditionalRules: 0,
    processSpawns: 0,
    estimatedOverhead: {},
    issues: []
  };

  // Parse rules
  const rulesMatch = content.match(/"rules":\s*\[([\s\S]*?)\]/);
  if (!rulesMatch) {
    console.error('Could not parse rules');
    return analysis;
  }

  const rulesText = rulesMatch[1];
  
  // Parse JSON to get accurate structure
  try {
    // Extract JSON part (skip frontmatter)
    const jsonStart = content.indexOf('{');
    const jsonContent = content.substring(jsonStart);
    const rulesData = JSON.parse(jsonContent);
    
    if (rulesData.rules && Array.isArray(rulesData.rules)) {
      rulesData.rules.forEach(rule => {
        analysis.totalRules++;
        
        // Check for auto_run: true
        if (rule.then && rule.then.auto_run === true) {
          analysis.autoRunRules++;
          analysis.processSpawns++;
        }
        
        // Check for .* pattern (always matches)
        if (rule.when && rule.when.matches === '.*') {
          analysis.alwaysTriggered++;
          
          analysis.issues.push({
            severity: 'high',
            rule: rule.id || 'unknown',
            issue: 'Matches .* (always triggers on every query)',
            recommendation: 'Use specific regex patterns instead'
          });
        } else if (rule.when && rule.when.matches && rule.when.matches !== '.*') {
          analysis.conditionalRules++;
        }
      });
    }
  } catch (e) {
    // Fallback to regex parsing
    const ruleMatches = rulesText.match(/\{[^}]*"id":\s*"([^"]+)"[^}]*\}/g);
    if (ruleMatches) {
      ruleMatches.forEach(ruleText => {
        analysis.totalRules++;
        
        // Check for auto_run: true
        if (/auto_run["\s]*:\s*true/.test(ruleText)) {
          analysis.autoRunRules++;
          analysis.processSpawns++;
        }
        
        // Check for .* pattern (always matches)
        if (/"matches":\s*"\.\*"/.test(ruleText)) {
          analysis.alwaysTriggered++;
          
          // Extract rule ID
          const idMatch = ruleText.match(/"id":\s*"([^"]+)"/);
          const ruleId = idMatch ? idMatch[1] : 'unknown';
          
          analysis.issues.push({
            severity: 'high',
            rule: ruleId,
            issue: 'Matches .* (always triggers on every query)',
            recommendation: 'Use specific regex patterns instead'
          });
        }
        
        // Check for conditional patterns
        if (/"matches":\s*"[^"]*"/.test(ruleText) && !/"matches":\s*"\.\*"/.test(ruleText)) {
          analysis.conditionalRules++;
        }
      });
    }
  }

  // Calculate overhead
  // Each process spawn: ~50-100ms
  // File I/O per tool: ~10-20ms
  // Total per query if all auto_run rules execute: processSpawns * 75ms average
  analysis.estimatedOverhead = {
    perQueryMs: analysis.processSpawns * 75,
    perQuerySpawns: analysis.processSpawns,
    perDayQueries: analysis.processSpawns * 75 * 100, // Assume 100 queries/day
    yearlyOverhead: analysis.processSpawns * 75 * 100 * 365 / 1000 / 60 // minutes per year
  };

  // Additional issues
  if (analysis.alwaysTriggered > 3) {
    analysis.issues.push({
      severity: 'medium',
      rule: 'multiple',
      issue: `${analysis.alwaysTriggered} rules always trigger on every query`,
      recommendation: 'Review and optimize high-priority rules to use conditional patterns'
    });
  }

  if (analysis.processSpawns > 5) {
    analysis.issues.push({
      severity: 'medium',
      rule: 'multiple',
      issue: `${analysis.processSpawns} tools spawn child processes per query`,
      recommendation: 'Consider batching or reducing process spawns through direct function calls'
    });
  }

  return analysis;
}

function generateReport(analysis) {
  const report = {
    timestamp: new Date().toISOString(),
    summary: {
      totalRules: analysis.totalRules,
      autoRunRules: analysis.autoRunRules,
      alwaysTriggeredRules: analysis.alwaysTriggered,
      conditionalRules: analysis.conditionalRules,
      processSpawnsPerQuery: analysis.processSpawns
    },
    overhead: analysis.estimatedOverhead,
    issues: analysis.issues,
    recommendations: []
  };

  // Generate recommendations
  if (analysis.alwaysTriggered > 0) {
    report.recommendations.push({
      priority: 'high',
      action: 'Replace .* patterns with specific regex',
      impact: `Would reduce ${analysis.alwaysTriggered} unnecessary rule triggers per query`,
      examples: [
        'Instead of: "matches": ".*"',
        'Use: "matches": "(create|add|implement|fix)\\s+(player|match|team)"',
        'Or: "matches": ".*(test|spec|unit).*"'
      ]
    });
  }

  if (analysis.processSpawns > 5) {
    report.recommendations.push({
      priority: 'medium',
      action: 'Reduce child process spawning',
      impact: `Could save ${analysis.processSpawns * 75}ms per query`,
      examples: [
        'Use direct function calls instead of child_process.spawnSync()',
        'Batch multiple operations',
        'Cache results to avoid re-execution'
      ]
    });
  }

  // Check telemetry emitter (priority 12)
  const content = fs.readFileSync(V7_RULES_PATH, 'utf8');
  if (content.includes('"id": "telemetry-emitter-v7"') && content.includes('"matches": ".*"')) {
    report.recommendations.push({
      priority: 'low',
      action: 'Make telemetry conditional',
      impact: 'Reduce logging overhead on every query',
      examples: [
        'Only log actual operations, not every query',
        'Use: "matches": ".*(APPLY|DEPLOY|proposal).*"'
      ]
    });
  }

  return report;
}

if (require.main === module) {
  try {
    const analysis = analyzeV7Rules();
    const report = generateReport(analysis);
    
    const outputDir = 'ops/enhance/comparison';
    if (!fs.existsSync(outputDir)) {
      fs.mkdirSync(outputDir, { recursive: true });
    }
    
    const reportFile = path.join(outputDir, 'v7-analysis-report.json');
    fs.writeFileSync(reportFile, JSON.stringify(report, null, 2));
    
    console.log('=== v7 Rules Analysis ===\n');
    console.log(`Total rules: ${analysis.totalRules}`);
    console.log(`Auto-run rules: ${analysis.autoRunRules}`);
    console.log(`Always-triggered rules: ${analysis.alwaysTriggered}`);
    console.log(`Process spawns per query: ${analysis.processSpawns}`);
    console.log(`\nEstimated overhead per query: ${analysis.estimatedOverhead.perQueryMs}ms`);
    console.log(`\nIssues found: ${analysis.issues.length}`);
    analysis.issues.forEach((issue, i) => {
      console.log(`\n${i + 1}. [${issue.severity.toUpperCase()}] ${issue.rule}`);
      console.log(`   Issue: ${issue.issue}`);
      console.log(`   Recommendation: ${issue.recommendation}`);
    });
    
    console.log(`\n\nReport saved to: ${reportFile}`);
  } catch (error) {
    console.error('[ERROR]', error);
    process.exit(1);
  }
}

module.exports = { analyzeV7Rules, generateReport };

