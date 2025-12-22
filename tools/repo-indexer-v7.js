#!/usr/bin/env node
// repo-indexer-v7.js (AST-lite starter)
// Writes ops/enhance/index.json with {files:[...], functions:[{file,fn}], tests_map: {...}}
const fs=require('fs');
const child=require('child_process');
function ensure(p){ if(!fs.existsSync(p)) fs.mkdirSync(p,{recursive:true}); }
ensure('ops/enhance');
const files = child.execSync('git ls-files').toString().split('\n').filter(Boolean);
const js = files.filter(f=>/\.(js|jsx|ts|tsx)$/i.test(f));
const py = files.filter(f=>/\.py$/i.test(f));
const php = files.filter(f=>/\.php$/i.test(f));
const index = { generated: new Date().toISOString(), files_count: files.length, files, functions: [], tests_map: {} };
// crude extract: function names in js files
js.forEach(f=>{
  try{
    const s=fs.readFileSync(f,'utf8');
    const names = (s.match(/function\s+([a-zA-Z0-9_]+)/g)||[]).map(m=>m.replace('function ',''));
    index.functions.push({file:f,functions:names});
  }catch(e){}
});
// map tests by filename heuristic
files.filter(f=>/test|spec/i.test(f)).forEach(t=>{
  const content = fs.readFileSync(t,'utf8');
  const refs = (content.match(/require\(['"`](.+?)['"`]\)/g)||[]).map(x=>x.replace(/require\(['"`](.+?)['"`]\)/,'$1'));
  index.tests_map[t]=refs;
});
fs.writeFileSync('ops/enhance/index.json', JSON.stringify(index,null,2));
console.log('Index written: ops/enhance/index.json');
