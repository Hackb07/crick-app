#!/usr/bin/env node
// apply-gate-v7.js — preview-first: runs tests & creates branch/commit (local only stub)
const fs=require('fs');
const child=require('child_process');
const payload = process.argv[2]? JSON.parse(process.argv[2]): {};
console.log('[APPLY GATE] payload:', payload);
console.log('[APPLY GATE] Running targeted tests (if any)...');
try{
  child.execSync(`node tools/test-runner-v7.js '${JSON.stringify(payload.tests||{})}'`, { stdio:'inherit' });
} catch(e){ console.error('[APPLY GATE] tests failed — abort'); process.exit(2); }
console.log('[APPLY GATE] Tests passed (or skipped). Creating branch and committing preview patch (stub).');
// stub: write commit message to ops/enhance/last-apply.json
fs.writeFileSync('ops/enhance/last-apply.json', JSON.stringify({payload,ts:new Date().toISOString()},null,2));
console.log('[APPLY GATE] preview commit recorded at ops/enhance/last-apply.json — integrate actual git/PR flow');
process.exit(0);
