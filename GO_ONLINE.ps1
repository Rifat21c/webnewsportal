# GO_ONLINE.ps1 - Instant Website Deployment
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "   IUB NEWS PORTAL - INSTANT DEPLOY" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# 1. Start PHP Server in Background
Write-Host "[1/2] Starting Local Server on Port 3000..." -ForegroundColor Yellow
try {
    # Check if a PHP server is already running on this port to improve robustness
    $process = Start-Process php -ArgumentList "-S localhost:3000" -PassThru -WindowStyle Hidden
    if ($process.Id) {
        Write-Host "      Server started successfully (PID: $($process.Id))" -ForegroundColor Green
    } else {
        Write-Host "      Warning: Could not track server process. It might be running." -ForegroundColor Yellow
    }
} catch {
    Write-Host "      Error: PHP not found or failed to start. Make sure PHP is installed." -ForegroundColor Red
    Pause
    Exit
}

# 2. Start SSH Tunnel
Write-Host "`n[2/2] Connecting to World Wide Web..." -ForegroundColor Yellow
Write-Host "      Your site will be available at a URL ending in '.serveo.net'" -ForegroundColor Gray
Write-Host "      Don't close this window!" -ForegroundColor Red

# Use localhost.run as serveo.net is currently down
Write-Host "      Attempting connection to localhost.run..." -ForegroundColor Yellow
ssh -o StrictHostKeyChecking=no -R 80:localhost:3000 nokey@localhost.run

# Cleanup when SSH closes
Stop-Process -Id $process.Id -ErrorAction SilentlyContinue
