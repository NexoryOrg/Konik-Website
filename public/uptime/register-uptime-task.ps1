param(
    [string]$TaskName = 'Konik-Uptime-Monitor',
    [string]$Url = 'http://localhost:8000/home/index.php',
    [int]$IntervalMinutes = 5
)

if ($IntervalMinutes -lt 1) {
    throw 'IntervalMinutes must be >= 1.'
}

$monitorScript = Join-Path $PSScriptRoot 'uptime-monitor.ps1'
if (-not (Test-Path -Path $monitorScript)) {
    throw "Monitor script not found: $monitorScript"
}

$taskAction = "powershell.exe -NoProfile -ExecutionPolicy Bypass -File `"$monitorScript`" -Url `"$Url`""

$createArgs = @(
    '/Create',
    '/TN', $TaskName,
    '/SC', 'MINUTE',
    '/MO', [string]$IntervalMinutes,
    '/TR', $taskAction,
    '/F'
)

$createResult = & schtasks @createArgs 2>&1
if ($LASTEXITCODE -ne 0) {
    throw "Failed to create task. schtasks output: $createResult"
}

$runResult = & schtasks /Run /TN $TaskName 2>&1
if ($LASTEXITCODE -ne 0) {
    throw "Task created but failed to start. schtasks output: $runResult"
}

Write-Output "Task '$TaskName' created and started. Interval: every $IntervalMinutes minute(s)."
Write-Output "Target URL: $Url"
Write-Output "Output file: $PSScriptRoot/../database/data/uptime.json"
