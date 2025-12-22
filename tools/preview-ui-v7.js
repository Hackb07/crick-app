#!/usr/bin/env node
// preview-ui-v7.js — builds a tiny index JSON for a static preview UI
const fs=require('fs');
const dir='ops/enhance/proposals';
if(!fs.existsSync(dir)){ console.log('No proposals dir'); process.exit(0); }
const files = fs.readdirSync(dir).filter(f=>f.endsWith('.json'));
const items = files.map(f=>{
  const p = JSON.parse(fs.readFileSync(dir+'/'+f,'utf8'));
  return { file: f, ts: p.meta? p.meta.ts : null, feature: p.meta? p.meta.feature : null, summary: p.summary || null };
});
fs.writeFileSync('ops/enhance/preview-index.json', JSON.stringify(items,null,2));
console.log('Preview index updated: ops/enhance/preview-index.json');
