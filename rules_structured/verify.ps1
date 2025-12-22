#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Quick-fix script to standardize rules_structured system

.DESCRIPTION
    This script performs the following standardization tasks:
    1. Verifies all directory structures exist
    2. Validates all file paths
    3. Checks automation script naming
    4. Generates status report

.NOTES
    Version: 1.0.0
    Date: 2025-12-04
    Author: Architecture Governance Team
#>

# Color output functions
function Write-Success { 
    param($Message) 
    Write-Host "[OK] $Message" -ForegroundColor Green 
}
function Write-Warning { 
    param($Message) 
    Write-Host "[WARN] $Message" -ForegroundColor Yellow 
}
function Write-Error { 
    param($Message) 
    Write-Host "[ERROR] $Message" -ForegroundColor Red 
}
function Write-Info { 
    param($Message) 
    Write-Host "[INFO] $Message" -ForegroundColor Cyan 
}
function Write-Header { 
    param($Message) 
    Write-Host "`n=======================================" -ForegroundColor Magenta
    Write-Host "  $Message" -ForegroundColor Magenta
    Write-Host "=======================================`n" -ForegroundColor Magenta 
}

# Configuration
$RulesRoot = "e:\xampp\htdocs\final Set\rules_structured"
$ErrorCount = 0
$WarningCount = 0
$FixCount = 0

Write-Header "Rules System Quick-Fix Script"
Write-Info "Starting standardization checks..."
Write-Info "Root: $RulesRoot`n"

# ═══════════════════════════════════════
# Task 1: Verify Directory Structure
# ═══════════════════════════════════════
Write-Header "Task 1: Directory Structure Verification"

$RequiredDirs = @(
    "core",
    "architecture",
    "code-quality",
    "security",
    "testing",
    "workflow",
    "ai-governance",
    "operations",
    "automation",
    "deprecated",
    "automation\legacy"
)

foreach ($dir in $RequiredDirs) {
    $fullPath = Join-Path $RulesRoot $dir
    if (Test-Path $fullPath) {
        Write-Success "Directory exists: $dir"
    } else {
        Write-Warning "Creating missing directory: $dir"
        New-Item -ItemType Directory -Path $fullPath -Force | Out-Null
        $FixCount++
    }
}

# ═══════════════════════════════════════
# Task 2: Verify Core Files
# ═══════════════════════════════════════
Write-Header "Task 2: Core Files Verification"

$CoreFiles = @(
    "RULES_MAIN_INDEX.md",
    "README.md",
    "QUICK_REFERENCE.md",
    "MIGRATION.md",
    "EXECUTIVE_SUMMARY.md",
    "COMPLETION_REPORT.md",
    "CURRENT_STATUS.md",
    "IMPLEMENTATION_PLAN.md",
    "DIRECTORY_TREE.txt",
    "PATH_AUDIT_REPORT.md",
    "deprecated\INDEX.md",
    "automation\README.md",
    "automation\legacy\README.md"
)

foreach ($file in $CoreFiles) {
    $fullPath = Join-Path $RulesRoot $file
    if (Test-Path $fullPath) {
        Write-Success "File exists: $file"
    } else {
        Write-Error "Missing file: $file"
        $ErrorCount++
    }
}

# ═══════════════════════════════════════
# Task 3: Verify Rule Files
# ═══════════════════════════════════════
Write-Header "Task 3: Rule Files Verification"

$RuleCategories = @{
    "core" = 4
    "architecture" = 9
    "code-quality" = 6
    "security" = 5
    "testing" = 4
    "workflow" = 5
    "ai-governance" = 7
    "operations" = 4
}

$TotalExpected = 0
$TotalFound = 0

foreach ($category in $RuleCategories.Keys) {
    $expectedCount = $RuleCategories[$category]
    $categoryPath = Join-Path $RulesRoot $category
    
    if (Test-Path $categoryPath) {
        $files = Get-ChildItem -Path $categoryPath -Filter "*.md" | Measure-Object
        $actualCount = $files.Count
        $TotalExpected += $expectedCount
        $TotalFound += $actualCount
        
        if ($actualCount -eq $expectedCount) {
            Write-Success "$category/: $actualCount/$expectedCount files"
        } elseif ($actualCount -gt $expectedCount) {
            Write-Warning "$category/: $actualCount/$expectedCount files (extra files found)"
            $WarningCount++
        } else {
            Write-Error "$category/: $actualCount/$expectedCount files (missing files)"
            $ErrorCount++
        }
    } else {
        Write-Error "$category/: Directory not found"
        $ErrorCount++
    }
}

Write-Info "`nTotal: $TotalFound/$TotalExpected rule files"

# ═══════════════════════════════════════
# Task 4: Verify Automation Scripts
# ═══════════════════════════════════════
Write-Header "Task 4: Automation Scripts Verification"

$automationPath = Join-Path $RulesRoot "automation"
$scripts = Get-ChildItem -Path $automationPath -Filter "*.js"

Write-Info "Found $($scripts.Count) JavaScript files"

# Check for -v7 suffix
$v7Scripts = $scripts | Where-Object { $_.Name -like "*-v7.js" }
if ($v7Scripts.Count -gt 0) {
    Write-Warning "Found $($v7Scripts.Count) scripts with -v7 suffix:"
    foreach ($script in $v7Scripts) {
        Write-Warning "  - $($script.Name)"
    }
    $WarningCount += $v7Scripts.Count
} else {
    Write-Success "All scripts have standardized names (no -v7 suffix)"
}

# List all scripts
Write-Info "`nAutomation scripts:"
$scripts | Sort-Object Name | ForEach-Object {
    Write-Host "  • $($_.Name)" -ForegroundColor Gray
}

# ═══════════════════════════════════════
# Task 5: Validate Path References
# ═══════════════════════════════════════
Write-Header "Task 5: Path References Validation"

# Check for broken internal references
$mdFiles = Get-ChildItem -Path $RulesRoot -Filter "*.md" -Recurse

Write-Info "Scanning $($mdFiles.Count) markdown files for path references..."

$brokenLinks = @()
foreach ($file in $mdFiles) {
    $content = Get-Content $file.FullName -Raw
    
    # Extract markdown links [text](path)
    $links = [regex]::Matches($content, '\[([^\]]+)\]\(([^\)]+)\)')
    
    foreach ($link in $links) {
        $linkPath = $link.Groups[2].Value
        
        # Skip external URLs
        if ($linkPath -match '^https?://') { continue }
        if ($linkPath -match '^#') { continue }
        
        # Check if file exists
        $fullLinkPath = Join-Path (Split-Path $file.FullName) $linkPath
        if (-not (Test-Path $fullLinkPath)) {
            $brokenLinks += @{
                File = $file.Name
                Link = $linkPath
            }
        }
    }
}

if ($brokenLinks.Count -eq 0) {
    Write-Success "No broken internal links found"
} else {
    Write-Warning "Found $($brokenLinks.Count) potentially broken links:"
    foreach ($broken in $brokenLinks) {
        Write-Warning "  $($broken.File): $($broken.Link)"
    }
    $WarningCount += $brokenLinks.Count
}

# ═══════════════════════════════════════
# Task 6: Generate Status Report
# ═══════════════════════════════════════
Write-Header "Task 6: Status Report"

$totalIssues = $ErrorCount + $WarningCount

Write-Host "`n[SUMMARY]" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Directories verified:  $($RequiredDirs.Count)" -ForegroundColor White
Write-Host "  Core files verified:   $($CoreFiles.Count)" -ForegroundColor White
Write-Host "  Rule files found:      $TotalFound/$TotalExpected" -ForegroundColor White
Write-Host "  Automation scripts:    $($scripts.Count)" -ForegroundColor White
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Fixes applied:         $FixCount" -ForegroundColor Green
Write-Host "  Warnings:              $WarningCount" -ForegroundColor Yellow
Write-Host "  Errors:                $ErrorCount" -ForegroundColor Red
Write-Host "========================================`n" -ForegroundColor Cyan

# Overall status
if ($ErrorCount -eq 0 -and $WarningCount -eq 0) {
    Write-Success "System is fully standardized! No issues found."
    exit 0
} elseif ($ErrorCount -eq 0) {
    Write-Warning "System is functional but has $WarningCount warnings."
    exit 0
} else {
    Write-Error "System has $ErrorCount critical errors that need attention."
    exit 1
}
