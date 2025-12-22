#!/usr/bin/env node
// rbac-gate-v7.js — checks user against ops/enhance/maintainers.json
const fs=require('fs');
const payload = process.argv[2]? JSON.parse(process.argv[2]):{};
const user = process.env.USER || process.env.GIT_USER || 'unknown';
const mfile = 'ops/enhance/maintainers.json';
if(!fs.existsSync(mfile)){ console.warn('Maintainers file not found, creating sample'); fs.writeFileSync(mfile, JSON.stringify([user],null,2)); }
const maintainers = JSON.parse(fs.readFileSync(mfile,'utf8'));
if(maintainers.includes(user)){ console.log('[RBAC] allowed:', user); process.exit(0); }
console.log('[RBAC] not allowed:', user); process.exit(2);
