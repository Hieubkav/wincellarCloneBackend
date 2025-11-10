# Cleanup Test/Debug Files from Root Directory
# 
# Purpose: Remove all temporary test/debug files that should not be in project root
# Usage: Run after debugging sessions to clean up

Write-Host "🧹 Cleaning up test/debug files from root..." -ForegroundColor Cyan

$rootPath = Split-Path -Parent $PSScriptRoot

$patterns = @(
    "test_*.php",
    "check_*.php", 
    "debug_*.php",
    "fix_*.php"
)

$deletedFiles = @()

foreach ($pattern in $patterns) {
    $files = Get-ChildItem -Path $rootPath -Filter $pattern -File -ErrorAction SilentlyContinue
    
    foreach ($file in $files) {
        Write-Host "  🗑️  Deleting: $($file.Name)" -ForegroundColor Yellow
        Remove-Item $file.FullName -Force
        $deletedFiles += $file.Name
    }
}

if ($deletedFiles.Count -eq 0) {
    Write-Host "✅ No test files found in root - Already clean!" -ForegroundColor Green
} else {
    Write-Host "`n✅ Deleted $($deletedFiles.Count) file(s):" -ForegroundColor Green
    $deletedFiles | ForEach-Object { Write-Host "   - $_" -ForegroundColor Gray }
    Write-Host "`n💡 Remember: Use /tests directory for test files!" -ForegroundColor Cyan
}
