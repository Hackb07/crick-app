#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Initialize Kavin45$ Rules System in a new project

.DESCRIPTION
    This script automatically sets up the required files and folders
    when you copy rules_structured/ to a new project.

    Creates:
    - MISTAKES_LOG.md (in project root)
    - logs-and-errors/ folder (in project root)
    - .cursorrules (optional, for Cursor IDE)

.EXAMPLE
    .\setup.ps1

.EXAMPLE
    .\setup.ps1 -SkipCursorRules
#>

param(
    [switch]$SkipCursorRules = $false
)

# Colors for output
$Green = "Green"
$Yellow = "Yellow"
$Red = "Red"
$Cyan = "Cyan"

Write-Host "`n🚀 Kavin45$ Rules System - Auto Setup`n" -ForegroundColor $Cyan

# Determine project root (parent of rules_structured folder)
$ScriptDir = $PSScriptRoot
$ProjectRoot = Split-Path -Parent $ScriptDir

Write-Host "📁 Project Root: $ProjectRoot" -ForegroundColor $Cyan
Write-Host "📁 Rules Folder: $ScriptDir`n" -ForegroundColor $Cyan

# Track what was created
$CreatedFiles = @()
$CreatedFolders = @()
$SkippedFiles = @()

# ============================================================================
# 1. Create MISTAKES_LOG.md (Hybrid AI Protocol)
# ============================================================================

$MistakesLogPath = Join-Path $TargetDir "MISTAKES_LOG.md"
if (-not (Test-Path $MistakesLogPath)) {
    Write-Host "Creating MISTAKES_LOG.md..." -ForegroundColor Cyan
    $MistakesContent = @"
# MISTAKES_LOG & LEARNING PROTOCOL

> **AI PROTOCOL**: ACTIVE-HYBRID
> **INSTRUCTIONS**: This file is the project's "Immune System".
> 1. **READ**: The AI *must* scan this file before planning complex changes.
> 2. **WRITE**: The AI *must* propose appending an entry here if a mistake costs significant time or breaks the build.
> 3. **ENFORCE**: Entries here override general training. If a pattern matches, the Fix *must* be applied.

---

## 🚨 CRITICAL ANTI-PATTERNS (Never Repeat)
*These are high-priority/fatal errors encountered previously. Strict adherence required.*

### [Category Name]
- **Mistake**: (Example) Describe the error here.
- **Consequence**: What broke?
- **Rule**: How to prevent it.

---

## ⚠️ INTERACTION & WORKFLOW OPTIMIZATIONS
*Adjustments to how the AI and User collaborate for efficiency.*

- **Context**: (Example) When to ask for testing.
- **Adjustment**: Check server status first.

---

## 🧠 REPOSITORY SPECIFIC QUIRKS
*Unique behaviors of this codebase that defy standard conventions.*

- **Component**: Details on unique component behavior.
"@
    Set-Content -Path $MistakesLogPath -Value $MistakesContent
    Write-Host "MISTAKES_LOG.md created." -ForegroundColor Green
} else {
    Write-Host "MISTAKES_LOG.md already exists. Skipping." -ForegroundColor Yellow
}

# ============================================================================
# 2. Create logs-and-errors/ folder
# ============================================================================

$LogsErrorsPath = Join-Path $ProjectRoot "logs-and-errors"

if (Test-Path $LogsErrorsPath) {
    Write-Host "⏭️  logs-and-errors/ folder already exists - skipping" -ForegroundColor $Yellow
    $SkippedFolders += "logs-and-errors/"
} else {
    New-Item -ItemType Directory -Path $LogsErrorsPath -Force | Out-Null
    Write-Host "✅ Created logs-and-errors/ folder" -ForegroundColor $Green
    $CreatedFolders += "logs-and-errors/"

    # Create README in logs-and-errors folder
    $LogsReadmePath = Join-Path $LogsErrorsPath "README.md"
    $LogsReadmeContent = @"
---
title: "Logs and Errors Directory"
version: "1.0.0"
purpose: "Centralized storage for logs, errors, and audit reports"
---

# 📂 Logs and Errors

**Purpose**: This folder stores all logs, error reports, and audit data.

---

## 📋 Naming Convention

All files and folders in this directory MUST follow this format:

``````
[type]-[name]-[YYYY-MM-DD]-[HHMM].[ext]

Examples:
- mistakes-archive-2025-12-04-2304.md
- compliance-audit-2025-12-04-2315.md
- error-log-2025-12-04-1430.txt
- security-scan-2025-12-04-0900.json
``````

---

## 📁 Folder Structure

``````
logs-and-errors/
├── README.md                           ← You are here
├── mistakes-archive-YYYY-MM-DD-HHMM.md ← Archived mistakes
├── compliance-audit-YYYY-MM-DD-HHMM/   ← Audit reports
├── error-log-YYYY-MM-DD-HHMM.txt       ← Error logs
└── security-scan-YYYY-MM-DD-HHMM.json  ← Security scans
``````

---

## 🔄 Archive Policy

### MISTAKES_LOG.md
- **Frequency**: Monthly
- **Action**: Move entries older than 30 days to ``mistakes-archive-YYYY-MM-DD-HHMM.md``

### Compliance Audits
- **Frequency**: Weekly
- **Action**: Create new folder ``compliance-audit-YYYY-MM-DD-HHMM/``

### Error Logs
- **Frequency**: As needed
- **Action**: Create new file ``error-log-YYYY-MM-DD-HHMM.txt``

---

**Created**: $(Get-Date -Format "yyyy-MM-dd HH:mm")
"@

    Set-Content -Path $LogsReadmePath -Value $LogsReadmeContent -Encoding UTF8
    Write-Host "✅ Created logs-and-errors/README.md" -ForegroundColor $Green
    $CreatedFiles += "logs-and-errors/README.md"
}

# ============================================================================
# 3. Create .cursorrules (optional)
# ============================================================================

if (-not $SkipCursorRules) {
    $CursorRulesPath = Join-Path $ProjectRoot ".cursorrules"

    if (Test-Path $CursorRulesPath) {
        Write-Host "⏭️  .cursorrules already exists - skipping" -ForegroundColor $Yellow
        $SkippedFiles += ".cursorrules"
    } else {
        $CursorRulesContent = @"
# Kavin45$ Rules System
# Auto-generated by setup.ps1

# Import unified rules
@import rules_structured/UNIFIED_RULES.md

# Priority levels (always apply)
@core
@arch
@sec
@ai

# Context-dependent (apply as needed)
# @quality
# @test
# @ops

# Project-specific overrides (add below)
# ----------------------------------------

"@

        Set-Content -Path $CursorRulesPath -Value $CursorRulesContent -Encoding UTF8
        Write-Host "✅ Created .cursorrules" -ForegroundColor $Green
        $CreatedFiles += ".cursorrules"
    }
} else {
    Write-Host "⏭️  Skipping .cursorrules creation (--SkipCursorRules flag)" -ForegroundColor $Yellow
}

# ============================================================================
# Summary
# ============================================================================

Write-Host "`n" + "="*60 -ForegroundColor $Cyan
Write-Host "📊 Setup Summary" -ForegroundColor $Cyan
Write-Host "="*60 -ForegroundColor $Cyan

if ($CreatedFiles.Count -gt 0) {
    Write-Host "`n✅ Created Files ($($CreatedFiles.Count)):" -ForegroundColor $Green
    foreach ($file in $CreatedFiles) {
        Write-Host "   - $file" -ForegroundColor $Green
    }
}

if ($CreatedFolders.Count -gt 0) {
    Write-Host "`n✅ Created Folders ($($CreatedFolders.Count)):" -ForegroundColor $Green
    foreach ($folder in $CreatedFolders) {
        Write-Host "   - $folder" -ForegroundColor $Green
    }
}

if ($SkippedFiles.Count -gt 0 -or $SkippedFolders.Count -gt 0) {
    Write-Host "`n⏭️  Skipped (Already Exists):" -ForegroundColor $Yellow
    foreach ($file in $SkippedFiles) {
        Write-Host "   - $file" -ForegroundColor $Yellow
    }
    foreach ($folder in $SkippedFolders) {
        Write-Host "   - $folder" -ForegroundColor $Yellow
    }
}

Write-Host "`n" + "="*60 -ForegroundColor $Cyan
Write-Host "✅ Setup Complete!" -ForegroundColor $Green
Write-Host "="*60 -ForegroundColor $Cyan

Write-Host "`n📚 Next Steps:" -ForegroundColor $Cyan
Write-Host "   1. Review MISTAKES_LOG.md before starting any task" -ForegroundColor $Cyan
Write-Host "   2. Check rules_structured/GETTING_STARTED.md for usage guide" -ForegroundColor $Cyan
Write-Host "   3. Start coding with enterprise-grade standards!`n" -ForegroundColor $Cyan
