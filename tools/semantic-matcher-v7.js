#!/usr/bin/env node
// semantic-matcher-v7.js (embeddings-ready stub)
// Usage: node tools/semantic-matcher-v7.js "create app"
const fs=require('fs');
const raw = process.argv.slice(2).join(' ') || fs.readFileSync(0,'utf8').trim();
function tokenize(s){ return s.toLowerCase().split(/\W+/).filter(Boolean); }
const templates = {
  create_app: ["create","scaffold","bootstrap","start","new","init"],
  bug: ["error","bug","fail","not","crash","exception"],
  diag: ["check","validate","audit","scan","verify"],
  design: ["design","mockup","template","ui","ux"]
};
let best = {intent:'unknown',score:0};
const t=tokenize(raw);
Object.keys(templates).forEach(k=>{
  const cand = templates[k];
  const inter = cand.filter(x=>t.includes(x)).length;
  const score = inter/Math.max(cand.length, t.length || 1);
  if(score>best.score) best={intent:k,score};
});
const out = { intent: best.intent, score: best.score, raw };
fs.writeFileSync('tools/semantic-match-result.json', JSON.stringify(out,null,2));
console.log(JSON.stringify(out,null,2));
