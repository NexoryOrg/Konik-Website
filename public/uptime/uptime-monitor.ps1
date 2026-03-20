param(
    [string]$Url = "http://localhost:8000/home/index.php",
    [string]$OutputFile = "$PSScriptRoot/../database/data/uptime.json",
    [int]$RetentionHours = 24,
    [int]$MaxFillMinutes = 120,
    [int]$SampleIntervalMinutes = 5,
    [string]$LogFile = "$PSScriptRoot/../../logs/uptime-monitor.log"
)

$ErrorActionPreference = 'Stop'

function Write-HealthLog {
    param(
        [ValidateSet('INFO', 'WARN', 'ERROR')]
        [string]$Level,
        [string]$Message,
        [string]$UrlValue,
        [int]$HttpCodeValue = 0,
        [string]$ErrorValue = $null
    )

    try {
        $logDir = Split-Path -Parent $LogFile
        if (-not (Test-Path -Path $logDir)) {
            New-Item -ItemType Directory -Path $logDir -Force | Out-Null
        }

        $line = [pscustomobject]@{
            timestamp = (Get-Date).ToString('yyyy-MM-dd HH:mm:ss')
            level = $Level
            message = $Message
            url = $UrlValue
            http_code = $HttpCodeValue
            error = $ErrorValue
        } | ConvertTo-Json -Compress

        Add-Content -Path $LogFile -Value $line -Encoding utf8
    } catch {
        # Logging must never break uptime collection.
    }
}

$status = 0
$httpCode = 0
$errorMessage = $null
$startedAt = Get-Date

try {

try {
    $response = Invoke-WebRequest `
        -Uri $Url `
        -Method Get `
        -MaximumRedirection 5 `
        -TimeoutSec 15 `
        -UseBasicParsing `
        -Headers @{ 'User-Agent' = 'Konik-Uptime/1.0' }

    $httpCode = [int]$response.StatusCode
    if ($httpCode -ge 200 -and $httpCode -lt 400) {
        $status = 1
    }
} catch {
    if ($null -ne $_.Exception.Response) {
        try {
            $httpCode = [int]$_.Exception.Response.StatusCode.value__
        } catch {
            $httpCode = 0
        }
    }
    $errorMessage = $_.Exception.Message
}

$responseMs = [int]((Get-Date) - $startedAt).TotalMilliseconds

$outputDir = Split-Path -Parent $OutputFile
if (-not (Test-Path -Path $outputDir)) {
    New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
}

$existingData = [ordered]@{}
if (Test-Path -Path $OutputFile) {
    try {
        $raw = Get-Content -Path $OutputFile -Raw -ErrorAction Stop
        if ($raw.Trim().Length -gt 0) {
            $parsedJson = ConvertFrom-Json -InputObject $raw -ErrorAction Stop
            if ($parsedJson -is [System.Collections.IDictionary]) {
                foreach ($key in $parsedJson.Keys) {
                    $existingData[[string]$key] = [int]$parsedJson[$key]
                }
            } elseif ($null -ne $parsedJson) {
                foreach ($prop in $parsedJson.PSObject.Properties) {
                    $existingData[[string]$prop.Name] = [int]$prop.Value
                }
            }
        }
    } catch {
        $existingData = [ordered]@{}
    }
}

$now = Get-Date
$sampleKey = $now.ToString('yyyy-MM-dd HH:mm')

$cutoff = $now.AddHours(-1 * $RetentionHours)
$fillMinutes = [Math]::Min($RetentionHours * 60, $MaxFillMinutes)
if ($fillMinutes -le 0) {
    $fillMinutes = 120
}

if ($SampleIntervalMinutes -lt 1) {
    $SampleIntervalMinutes = 5
}

$roundedMinute = [Math]::Floor($now.Minute / $SampleIntervalMinutes) * $SampleIntervalMinutes
$sampleTime = Get-Date -Date $now -Hour $now.Hour -Minute $roundedMinute -Second 0
$sampleKey = $sampleTime.ToString('yyyy-MM-dd HH:mm')

$fillStart = $now.AddMinutes(-1 * $fillMinutes)
if ($fillStart -lt $cutoff) {
    $fillStart = $cutoff
}

$fillStartRoundedMinute = [Math]::Floor($fillStart.Minute / $SampleIntervalMinutes) * $SampleIntervalMinutes
$fillStart = Get-Date -Date $fillStart -Hour $fillStart.Hour -Minute $fillStartRoundedMinute -Second 0

$cursor = $fillStart
while ($cursor -le $now) {
    $minuteKey = $cursor.ToString('yyyy-MM-dd HH:mm')
    if (-not $existingData.Contains($minuteKey)) {
        $existingData[$minuteKey] = 0
    }
    $cursor = $cursor.AddMinutes($SampleIntervalMinutes)
}

$existingData[$sampleKey] = $status

$filtered = [ordered]@{}
$acceptedFormats = @('yyyy-MM-dd HH:mm', 'yyyy-MM-dd HH:00')

foreach ($entry in ($existingData.GetEnumerator() | Sort-Object Name)) {
    try {
        $parsedDate = $null
        $parsed = $false
        foreach ($format in $acceptedFormats) {
            try {
                $parsedDate = [DateTime]::ParseExact(
                    [string]$entry.Key,
                    $format,
                    [System.Globalization.CultureInfo]::InvariantCulture,
                    [System.Globalization.DateTimeStyles]::None
                )
                $parsed = $true
                break
            } catch {
                continue
            }
        }

        if ($parsed -and $parsedDate -ge $cutoff) {
            $filtered[[string]$entry.Key] = [int]$entry.Value
        }
    } catch {
        continue
    }
}

$json = $filtered | ConvertTo-Json
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
[System.IO.File]::WriteAllText($OutputFile, $json, $utf8NoBom)

if ($status -ne 1 -or $null -ne $errorMessage) {
    Write-HealthLog -Level 'WARN' -Message 'Uptime check failed' -UrlValue $Url -HttpCodeValue $httpCode -ErrorValue $errorMessage
}

$result = [pscustomobject]@{
    ok = $true
    timestamp = $sampleKey
    status = $status
    http_code = $httpCode
    response_ms = $responseMs
    error = $errorMessage
}

$result | ConvertTo-Json -Compress
} catch {
    $fatalError = $_.Exception.Message
    Write-HealthLog -Level 'ERROR' -Message 'Uptime task execution failed' -UrlValue $Url -HttpCodeValue $httpCode -ErrorValue $fatalError
    throw
}
