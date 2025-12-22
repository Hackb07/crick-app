#!/usr/bin/env node
/**
 * design-pipeline-v7.js
 *
 * Reads a design reference (image or layout JSON),
 * extracts colors & layout skeleton, and generates a Tailwind/SCSS-based component preview.
 *
 * Usage:
 *   node tools/design-pipeline-v7.js path/to/reference.png
 *   node tools/design-pipeline-v7.js path/to/reference.json
 *
 * Output:
 *   ops/enhance/previews/design-<timestamp>/
 *     ├── palette.json
 *     ├── layout.md
 *     └── component.jsx
 */

const fs = require('fs');
const path = require('path');
const child = require('child_process');

function ensureDir(p) { if (!fs.existsSync(p)) fs.mkdirSync(p, { recursive: true }); }
function nowTs() { return new Date().toISOString().replace(/[:.]/g, '-'); }

const ref = process.argv[2];
if (!ref) {
  console.error('Usage: node tools/design-pipeline-v7.js <reference-file>');
  process.exit(1);
}

if (!fs.existsSync(ref)) {
  console.error('Reference file not found:', ref);
  process.exit(2);
}

ensureDir('ops/enhance/previews');

const outDir = `ops/enhance/previews/design-${nowTs()}`;
ensureDir(outDir);

console.log('[DESIGN] Reading reference:', ref);

// Determine file type
const ext = path.extname(ref).toLowerCase();
let mode = 'image';
if (ext === '.json' || ext === '.fig' || ext === '.layout') mode = 'json';

const palette = [];
const layout = [];

if (mode === 'image') {
  // crude color extraction via ImageMagick if available
  try {
    const escapedRef = ref.replace(/"/g, '\\"');
    const raw = child.execSync(`convert "${escapedRef}" -resize 50x50 -format "%c" histogram:info:-`, { 
      encoding: 'utf8',
      stdio: ['pipe', 'pipe', 'pipe']
    });
    const lines = raw.split('\n').filter(Boolean).slice(0, 8);
    lines.forEach(l => {
      const m = l.match(/#[0-9A-Fa-f]{6}/);
      if (m) palette.push(m[0]);
    });
    if (palette.length === 0) {
      console.warn('[DESIGN] No colors extracted. Using fallback palette.');
      palette.push('#2E2E2E', '#F5F5F5', '#1E90FF', '#FF5722', '#4CAF50');
    }
  } catch (e) {
    console.warn('[DESIGN] ImageMagick not found or failed. Using fallback palette.');
    if (palette.length === 0) {
      palette.push('#2E2E2E', '#F5F5F5', '#1E90FF', '#FF5722', '#4CAF50');
    }
  }

  layout.push('Auto-layout inferred: header, content, footer (guess)');
} else {
  try {
    const data = JSON.parse(fs.readFileSync(ref, 'utf8'));
    if (data.colors && Array.isArray(data.colors)) {
      palette.push(...data.colors);
    }
    if (data.layout && Array.isArray(data.layout)) {
      layout.push(...data.layout);
    } else if (data.layout && typeof data.layout === 'string') {
      layout.push(data.layout);
    }
    if (palette.length === 0) {
      palette.push('#222', '#ddd', '#0088ff');
    }
    if (layout.length === 0) {
      layout.push('single-column');
    }
  } catch (e) {
    console.error('[DESIGN] Failed to parse JSON layout. Proceeding with default.');
    if (palette.length === 0) {
      palette.push('#222', '#ddd', '#0088ff');
    }
    if (layout.length === 0) {
      layout.push('single-column');
    }
  }
}

const paletteOut = path.join(outDir, 'palette.json');
const layoutOut = path.join(outDir, 'layout.md');
fs.writeFileSync(paletteOut, JSON.stringify(palette, null, 2));
fs.writeFileSync(layoutOut, layout.join('\n'));

console.log('[DESIGN] Palette + layout extracted.');
console.log('[DESIGN] Palette:', palette.join(', '));

const bgColor = palette[1] || '#f9f9f9';
const cardBgColor = palette[0] || '#ffffff';
const textColor = palette[2] || '#333';
const textSecondary = palette[3] || '#555';
const buttonColor = palette[4] || '#007bff';

const jsxTemplate = `
import React from "react";

export default function PreviewCard() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center" style={{ backgroundColor: "${bgColor}" }}>
      <div className="w-full max-w-md rounded-2xl shadow-lg p-6" style={{ backgroundColor: "${cardBgColor}" }}>
        <h1 className="text-2xl font-bold mb-4" style={{ color: "${textColor}" }}>Design Preview</h1>
        <p style={{ color: "${textSecondary}" }}>
          Auto-generated component from design reference.
        </p>
        <button className="mt-4 px-4 py-2 rounded-lg text-white" style={{ backgroundColor: "${buttonColor}" }}>
          Primary Action
        </button>
      </div>
    </div>
  );
}
`;

fs.writeFileSync(path.join(outDir, 'component.jsx'), jsxTemplate.trim());
console.log('[DESIGN] Component scaffold generated:', path.join(outDir, 'component.jsx'));

// emit design-report.json
const report = {
  ts: new Date().toISOString(),
  reference: ref,
  palette,
  layout,
  component_path: `${outDir}/component.jsx`
};
fs.writeFileSync(path.join(outDir, 'design-report.json'), JSON.stringify(report, null, 2));

console.log('[DESIGN] Design report written:', path.join(outDir, 'design-report.json'));
console.log('[DESIGN] Preview ready under:', outDir);

