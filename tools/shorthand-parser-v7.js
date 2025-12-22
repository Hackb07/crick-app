#!/usr/bin/env node
// shorthand-parser-v7.js — parse SH:: lines and forward to enhance-runner
const fs=require('fs');
const raw = process.argv.slice(2).join(' ') || fs.readFileSync(0,'utf8').trim();
if(!raw.startsWith('SH::')){ console.error('No SH:: prefix found'); process.exit(1); }
const body = raw.replace(/^SH::/i,'').trim();
const parts = body.split(';').map(p=>p.trim()).filter(Boolean);
const payload = {};
parts.forEach(p=>{
  const [k,...v]=p.split('=');
  payload[k.trim().toUpperCase()]=v.join('=').trim().replace(/^"|"$/g,'');
});
fs.writeFileSync('tools/last-payload.json', JSON.stringify(payload,null,2));
console.log('Parsed shorthand -> tools/last-payload.json');
require('child_process').spawnSync('node',['tools/enhance-runner-v7.js', JSON.stringify(payload)], { stdio:'inherit' });
