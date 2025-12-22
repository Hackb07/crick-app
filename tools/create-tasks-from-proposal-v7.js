#!/usr/bin/env node
// create-tasks-from-proposal-v7.js
const fs=require('fs');
const P = fs.existsSync('ops/enhance/last-proposal.json')? JSON.parse(fs.readFileSync('ops/enhance/last-proposal.json')): null;
if(!P){ console.error('No proposal found at ops/enhance/last-proposal.json'); process.exit(1); }
const feat = P.meta.feature || 'feature';
const base = Date.now() % 100000;
const tasks = [];
function push(title,desc,files,est,labels,prio,deps){
  tasks.push({ id:`T-${base+tasks.length+1}`, title, description:desc, files, estimate_hours:est, labels, priority:prio, dependencies:deps||[], status:'todo', created_at: new Date().toISOString() });
}
push(`Reproduce: ${feat}`, `Reproduce and collect logs for ${feat}`, P.summary.candidates || [], 0.5, ['investigate'],'high');
push(`Implement fix: ${feat}`, `Apply minimal patch from proposal`, P.summary.candidates || [], 3, ['code'],'high', [`T-${base+1}`]);
push(`Unit tests: ${feat}`, `Add targeted unit tests`, [], 1, ['test'],'medium', [`T-${base+2}`]);
push(`Integration test: ${feat}`, `Add integration test`, [], 2, ['integration'],'medium', [`T-${base+3}`]);
fs.writeFileSync('ops/enhance/tasks.json', JSON.stringify(tasks,null,2));
let md = '# Tasks\n\n';
tasks.forEach(t=> md+= `- [ ] **${t.id}** ${t.title} — ${t.priority}  \n  Files: ${t.files.join(', ')}  \n`);
fs.writeFileSync('ops/enhance/tasks.md', md);
console.log('Tasks written ops/enhance/tasks.json + tasks.md');
