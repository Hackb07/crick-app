#!/usr/bin/env node
// prompt-enhancer-v7.js
// Clarifies vague prompts, writes tools/last-enhanced-prompt.json, creates pending clarify file if needed.
// Usage: node tools/prompt-enhancer-v7.js "create one app" [--answers='{"KEY":"val"}']

const fs = require('fs');
const arg = process.argv.slice(2).find(a => !a.startsWith('--')) || '';
const answersArg = (() => {
  const m = process.argv.find(x=>x&&x.startsWith('--answers='));
  if(!m) return null;
  try{return JSON.parse(m.split('=')[1]);}catch(e){return null;}
})();

function ensureDir(p){ if(!fs.existsSync(p)) fs.mkdirSync(p, { recursive: true }); }
function nowTs(){ return new Date().toISOString().replace(/[:.]/g,'-'); }
const RAW = arg || fs.readFileSync(0,'utf8').trim() || '';

const detectIntent = s=>{
  if(!s) return 'feature';
  if(/\b(create|scaffold|generate|bootstrap|start)\b/i.test(s)) return 'create_app';
  if(/\b(check|validate|audit|scan)\b/i.test(s)) return 'diag';
  if(/\b(bug|error|fail|not working)\b/i.test(s)) return 'bug';
  return 'feature';
};
const enhanced = {
  RAW,
  TYPE: detectIntent(RAW).startsWith('create')? 'feature' : 'task',
  INTENT: detectIntent(RAW),
  ACTION: detectIntent(RAW).startsWith('create')? 'scaffold_project' : 'analyze',
  FEATURE: (RAW.match(/\b(match|leaderboard|auth|login|app|game|score|team)\b/i)||['new-app'])[0].toLowerCase(),
  DETAILS: RAW,
  LANG: 'node,php,python',
  DB: 'postgres',
  AUTH: 'session',
  CI: 'github-actions',
  DOCKER: 'yes',
  TEST: 'YES',
  APPLY: 'NO',
  ENV: 'dev'
};
if(answersArg && typeof answersArg === 'object'){ Object.assign(enhanced, answersArg); }

// required keys for create_app
const required = ['APP_TYPE','DOMAIN','LANG','DB','UI','AUTH'];
const missing = [];
if(enhanced.INTENT === 'create_app'){
  required.forEach(k=>{ if(!enhanced[k]) missing.push(k); });
}

ensureDir('ops/enhance/pending');
ensureDir('tools');

// if missing and no answers provided -> create clarify file
if(enhanced.INTENT === 'create_app' && missing.length>0 && !answersArg){
  const questions = {
    APP_TYPE: 'App type? (web / api / mobile / cli / game)',
    DOMAIN: 'Domain or purpose? (e.g., crm, cricket scoring, ecommerce)',
    LANG: 'Preferred language? (node / php / python / go / java / rust)',
    DB: 'Database? (postgres / mysql / mongo / sqlite / none)',
    UI: 'UI needed? (yes / no)',
    AUTH: 'Auth needed? (session / jwt / oauth / none)'
  };
  const ask = missing.map(k=>({ key:k, question: questions[k] || 'Specify ' + k }));
  const pending = { id:`clarify-${nowTs()}`, raw:RAW, missing, questions:ask };
  const out = `ops/enhance/pending/${pending.id}.json`;
  fs.writeFileSync(out, JSON.stringify(pending,null,2));
  console.log('CLARIFICATION REQUIRED — pending file created:', out);
  console.log('Questions:');
  ask.forEach((q,i)=>console.log(`${i+1}. ${q.question}`));
  console.log('\nAnswer example (one-line):\nnode tools/prompt-enhancer-v7.js "'+RAW+'" --answers \'{"APP_TYPE":"web","DOMAIN":"cricket scoring","LANG":"php","DB":"mysql","UI":"yes","AUTH":"session"}\'');
  process.exit(0);
}

// write enhanced prompt and forward to runner (preview)
fs.writeFileSync('tools/last-enhanced-prompt.json', JSON.stringify(enhanced, null, 2));
console.log('Enhanced prompt written: tools/last-enhanced-prompt.json');
try{
  require('child_process').spawnSync('node', ['tools/enhance-runner-v7.js', JSON.stringify(enhanced)], { stdio:'inherit' });
} catch(e){
  console.error('Failed to forward to enhance-runner-v7.js', e.message);
}
