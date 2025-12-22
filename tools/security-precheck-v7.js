#!/usr/bin/env node
// security-precheck-v7.js — lightweight audits (npm audit if package.json)
const fs=require('fs');
const child=require('child_process');
console.log('[SECURITY] running quick security checks...');
if(fs.existsSync('package.json')){
  try{ child.execSync('npm audit --json', { stdio:'pipe' }); console.log('[SECURITY] npm audit executed (inspect output manually)'); }
  catch(e){ console.warn('[SECURITY] npm audit exit (treat as potential issues)'); }
}
if(fs.existsSync('composer.json')) console.log('[SECURITY] composer audit: implement as needed');
console.log('[SECURITY] stub complete — integrate semgrep/gitleaks for real checks');
process.exit(0);
