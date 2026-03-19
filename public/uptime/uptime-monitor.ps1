param(
    [string]$Url = "http://localhost:8000/home/index.php",
    [string]$OutputFile = "$PSScriptRoot/../datenbank/data/uptime.json",
    [int]$RetentionHours = 24
)

$ErrorActionPreference = 'Stop'

$status = 0
$httpCode = 0
$errorMessage = $null
$startedAt = Get-Date

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
$existingData[$sampleKey] = $status

$cutoff = $now.AddHours(-1 * $RetentionHours)
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

$result = [pscustomobject]@{
    ok = $true
    timestamp = $sampleKey
    status = $status
    http_code = $httpCode
    response_ms = $responseMs
    error = $errorMessage
}

$result | ConvertTo-Json -Compress
