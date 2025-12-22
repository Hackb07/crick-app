#!/usr/bin/env node
// enhance-runner-v7.js (orchestrator preview-first)
// Usage: node tools/enhance-runner-v7.js '{"TYPE":"feature",...}'
const fs=require('fs');
const child=require('child_process');
const arg = process.argv[2] || '{}';
let payload={};
try{ payload=JSON.parse(arg); }catch(e){ payload={ RAW: arg }; }
console.log('[ENHANCE] payload:', payload.RAW || payload);
const semantic = JSON.parse(fs.existsSync('tools/semantic-match-result.json')? fs.readFileSync('tools/semantic-match-result.json') : '{}');
const index = fs.existsSync('ops/enhance/index.json')? JSON.parse(fs.readFileSync('ops/enhance/index.json')):{};
// candidate discovery (by feature token)
const feature = payload.FEATURE || (payload.RAW && payload.RAW.split(' ')[0]) || 'match';
const candidates = (index.files||[]).filter(f=> new RegExp(feature,'i').test(f)).slice(0,12);
const report = {
  meta:{ ts: new Date().toISOString(), feature, semantic, payload },
  summary:{ files_scanned: candidates.length, candidates },
  proposals: [],
  risk: 0.25
};
// quick heuristic findings
if(candidates.length===0) report.summary.note='No candidate files matched; try FILES=...';
else report.proposals.push(`Inspect ${candidates[0]} for ${feature} logic`);
function ensureDir(p){ if(!fs.existsSync(p)) fs.mkdirSync(p,{recursive:true}); }
ensureDir('ops/enhance/proposals');
const ts = new Date().toISOString().replace(/[:.]/g,'-');
const out = `ops/enhance/proposals/proposal-${feature}-${ts}.json`;
fs.writeFileSync(out, JSON.stringify(report,null,2));
fs.writeFileSync('ops/enhance/last-proposal.json', JSON.stringify(report,null,2));
console.log('[ENHANCE] Proposal written:', out);
