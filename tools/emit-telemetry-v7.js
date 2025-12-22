#!/usr/bin/env node
// emit-telemetry-v7.js — append JSONL telemetry to ops/enhance/enhance-telemetry.log
const fs=require('fs');
const payload = process.argv[2]? JSON.parse(process.argv[2]): {event:'heartbeat'};
payload.ts = new Date().toISOString();
fs.writeFileSync('ops/enhance/enhance-telemetry.log', JSON.stringify(payload)+'\n', { flag:'a' });
console.log('[TELEMETRY] emitted');
