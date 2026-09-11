$ErrorActionPreference = 'Stop'
$base = 'http://localhost/WordPress/my_first_store_wordpress'
$paths = @('/', '/boutique/', '/a-propos/', '/contact/', '/panier/', '/mon-compte/', '/product/aura-mini-speaker/')
$failures = 0

foreach ($path in $paths) {
    try {
        $response = Invoke-WebRequest -Uri ($base + $path) -MaximumRedirection 5 -UseBasicParsing
        $ok = $response.StatusCode -eq 200 -and $response.Content -match 'SEEF STORE'
        if ($ok) {
            Write-Output "[PASS] $path - HTTP $($response.StatusCode)"
        } else {
            Write-Output "[FAIL] $path - HTTP $($response.StatusCode)"
            $failures++
        }
    } catch {
        Write-Output "[FAIL] $path - $($_.Exception.Message)"
        $failures++
    }
}

$headers = Invoke-WebRequest -Uri ($base + '/') -UseBasicParsing
foreach ($name in @('X-Content-Type-Options', 'X-Frame-Options', 'Referrer-Policy', 'Permissions-Policy')) {
    if ($headers.Headers[$name]) { Write-Output "[PASS] Security header: $name" } else { Write-Output "[FAIL] Security header: $name"; $failures++ }
}

if ($headers.Content -match 'class="seef-icon' -and $headers.Content -match '"@type":"OnlineStore"') {
    Write-Output '[PASS] Self-contained SVG icons and store schema are rendered'
} else {
    Write-Output '[FAIL] SVG icons or store schema are missing'
    $failures++
}

$accountPage = Invoke-WebRequest -Uri ($base + '/mon-compte/') -UseBasicParsing
$themeCss = Invoke-WebRequest -Uri ($base + '/wp-content/themes/seef-store/assets/css/store.css?ver=1.2.0') -UseBasicParsing
if ($accountPage.Content -match 'type="password"' -and $accountPage.Content -match 'assets/js/frontend/woocommerce\.js' -and $themeCss.Content -match 'show-password-input\.display-password') {
    Write-Output '[PASS] Password visibility control is available and styled'
} else {
    Write-Output '[FAIL] Password visibility control is incomplete'
    $failures++
}

if ($themeCss.Content -match 'woocommerce-message:before' -and $themeCss.Content -match 'seef-notice--error:before') {
    Write-Output '[PASS] Notification states have independent icons'
} else {
    Write-Output '[FAIL] Notification icons are missing'
    $failures++
}

$rtl = Invoke-WebRequest -Uri ($base + '/?seef_lang=ar') -UseBasicParsing
if ($rtl.Content -match '<html lang="ar" dir="rtl">') { Write-Output '[PASS] Arabic RTL document' } else { Write-Output '[FAIL] Arabic RTL document'; $failures++ }

$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$catalog = Invoke-WebRequest -Uri ($base + '/boutique/') -WebSession $session -UseBasicParsing
if ($catalog.Content -match '<link rel="canonical" href="[^\"]+/boutique/">') {
    Write-Output '[PASS] Shop archive exposes a canonical URL'
} else {
    Write-Output '[FAIL] Shop archive canonical URL is missing'
    $failures++
}
if ($catalog.Content -match 'add-to-cart=(\d+)') {
    $productId = $Matches[1]
    Invoke-WebRequest -Uri ($base + '/?add-to-cart=' + $productId) -WebSession $session -UseBasicParsing | Out-Null
    $checkout = Invoke-WebRequest -Uri ($base + '/commande/') -WebSession $session -MaximumRedirection 5 -UseBasicParsing
    if ($checkout.StatusCode -eq 200 -and $checkout.Content -match 'wc-block-checkout|woocommerce-checkout') {
        Write-Output '[PASS] Session cart reaches checkout'
    } else {
        Write-Output '[FAIL] Session cart reaches checkout'
        $failures++
    }
} else {
    Write-Output '[FAIL] Add-to-cart product link not found'
    $failures++
}

exit $(if ($failures -eq 0) { 0 } else { 1 })
