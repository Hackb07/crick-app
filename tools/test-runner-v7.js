#!/usr/bin/env node
// test-runner-v7.js — detect framework, run targeted tests via payload {"tests":[...],"affected_files":[...]}
const fs=require('fs');
const child=require('child_process');
const payload = process.argv[2]? JSON.parse(process.argv[2]) : {};
function detect(){ if(fs.existsSync('package.json')) return 'node'; if(fs.existsSync('pyproject.toml')||fs.existsSync('requirements.txt')) return 'py'; if(fs.existsSync('composer.json')) return 'php'; if(fs.existsSync('go.mod')) return 'go'; return 'unknown'; }
const framework = payload.framework || detect();
console.log('[TEST RUNNER] framework:', framework);
try{
  if(framework==='node'){
    const tests = payload.tests && payload.tests.length? payload.tests.join(' ') : '';
    const cmd = tests? `npx jest ${tests}` : `npx jest --passWithNoTests`;
    child.execSync(cmd, { stdio:'inherit' });
  } else if(framework==='py'){
    child.execSync('pytest -q', { stdio:'inherit' });
  } else if(framework==='go'){
    child.execSync('go test ./...', { stdio:'inherit' });
  } else {
    console.log('[TEST RUNNER] no tests executed (unknown framework)');
  }
  console.log('[TEST RUNNER] success'); process.exit(0);
}catch(e){ console.error('[TEST RUNNER] tests failed'); process.exit(2); }
