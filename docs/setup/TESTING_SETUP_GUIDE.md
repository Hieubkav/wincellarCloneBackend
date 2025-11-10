# Testing Setup Guide

## Database Setup for Tests

Tests đang sử dụng `RefreshDatabase` trait, cần setup test database trước.

### Option 1: Sử dụng SQLite cho testing (Recommended)

**1. Update phpunit.xml:**
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

**2. Run tests:**
```bash
php artisan test --filter=ErrorHandlingTest
php artisan test --filter=RateLimitingTest
```

### Option 2: Sử dụng MySQL/MariaDB test database

**1. Tạo test database:**
```sql
CREATE DATABASE wincellar_test;
```

**2. Update .env.testing:**
```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wincellar_test
DB_USERNAME=root
DB_PASSWORD=your_password
```

**3. Run migrations cho test database:**
```bash
php artisan migrate --env=testing
```

**4. Run tests:**
```bash
php artisan test --filter=ErrorHandlingTest
```

---

## Manual Testing (Không cần database setup)

### Test với server đang chạy

**1. Start server:**
```bash
php artisan serve
```

**2. Test error responses:**

#### Validation Error (422)
```bash
curl -X GET "http://localhost:8000/api/v1/san-pham?price_min=abc" -H "Accept: application/json" -i
```

Expected: 422 status với ValidationError

#### Not Found (404)
```bash
curl -X GET "http://localhost:8000/api/v1/san-pham/non-existent-product" -H "Accept: application/json" -i
```

Expected: 404 status với NotFound error

#### Bad Request (400)
```bash
curl -X GET "http://localhost:8000/api/v1/san-pham?price_min=5000000&price_max=1000000" -H "Accept: application/json" -i
```

Expected: 400 status với BadRequest error

#### Correlation ID
```bash
curl -X GET "http://localhost:8000/api/v1/home" -H "X-Correlation-ID: test-123" -H "Accept: application/json" -i | findstr "X-Correlation-ID"
```

Expected: Response header có X-Correlation-ID: test-123

#### Rate Limiting (429)
```powershell
# PowerShell script to test rate limiting
for ($i=1; $i -le 65; $i++) {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/home" -Method GET -Headers @{"Accept"="application/json"} -UseBasicParsing
    Write-Host "Request $i - Status: $($response.StatusCode)"
}
```

Expected: First 60 requests return 200, request 61+ return 429

#### CORS Headers
```bash
curl -X OPTIONS "http://localhost:8000/api/v1/home" -H "Origin: http://localhost:3000" -H "Access-Control-Request-Method: GET" -i
```

Expected: Response có Access-Control-Allow-Origin header

---

## Verification Checklist

### Error Handling
- [ ] Validation errors return 422 với standard format
- [ ] Not found errors return 404 với resource info
- [ ] Bad request errors return 400 với clear message
- [ ] All errors include correlation_id
- [ ] All errors include timestamp và path
- [ ] Error details are structured consistently

### Rate Limiting
- [ ] 60 requests per minute allowed
- [ ] 61st request returns 429
- [ ] Rate limit response includes retry_after
- [ ] Rate limit applies to all API endpoints
- [ ] Rate limit is per IP address

### CORS
- [ ] CORS headers present for api/* routes
- [ ] X-Correlation-ID exposed in CORS
- [ ] X-RateLimit-* headers exposed
- [ ] Preflight requests work correctly

### Correlation ID
- [ ] Every response has X-Correlation-ID header
- [ ] Custom correlation ID from request is preserved
- [ ] Correlation ID appears in error logs
- [ ] Correlation ID format is UUID

---

## Quick Verification Script

**PowerShell script:**
```powershell
# Save as test-api.ps1

Write-Host "Testing API Error Handling..." -ForegroundColor Green

# Test 1: Validation Error
Write-Host "`n1. Testing Validation Error (422)..."
$response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/san-pham?price_min=abc" -Method GET -Headers @{"Accept"="application/json"} -UseBasicParsing -SkipHttpErrorCheck
Write-Host "Status: $($response.StatusCode) - Expected: 422"
$json = $response.Content | ConvertFrom-Json
Write-Host "Error Type: $($json.error)"

# Test 2: Not Found
Write-Host "`n2. Testing Not Found (404)..."
$response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/san-pham/non-existent" -Method GET -Headers @{"Accept"="application/json"} -UseBasicParsing -SkipHttpErrorCheck
Write-Host "Status: $($response.StatusCode) - Expected: 404"

# Test 3: Bad Request
Write-Host "`n3. Testing Bad Request (400)..."
$response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/san-pham?price_min=5000000&price_max=1000000" -Method GET -Headers @{"Accept"="application/json"} -UseBasicParsing -SkipHttpErrorCheck
Write-Host "Status: $($response.StatusCode) - Expected: 400"

# Test 4: Correlation ID
Write-Host "`n4. Testing Correlation ID..."
$response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/home" -Method GET -Headers @{"Accept"="application/json"; "X-Correlation-ID"="test-123"} -UseBasicParsing
$correlationId = $response.Headers["X-Correlation-ID"]
Write-Host "Correlation ID: $correlationId - Expected: test-123"

# Test 5: Rate Limiting
Write-Host "`n5. Testing Rate Limiting (this may take a minute)..."
$limitHit = $false
for ($i=1; $i -le 65; $i++) {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/home" -Method GET -Headers @{"Accept"="application/json"} -UseBasicParsing -SkipHttpErrorCheck
    if ($response.StatusCode -eq 429) {
        Write-Host "Rate limit hit at request $i - Success!" -ForegroundColor Yellow
        $limitHit = $true
        break
    }
}
if (-not $limitHit) {
    Write-Host "Rate limit not hit after 65 requests - Check configuration" -ForegroundColor Red
}

Write-Host "`nAll tests completed!" -ForegroundColor Green
```

**Run:**
```powershell
.\test-api.ps1
```

---

## Troubleshooting

### Issue: Tests failing with "Table not found"
**Solution:** Setup test database (see Option 1 or 2 above)

### Issue: Rate limiting not working
**Solution:** 
1. Check routes/api.php has `throttle:api` middleware
2. Clear cache: `php artisan cache:clear`
3. Check config/cache.php

### Issue: CORS headers not present
**Solution:**
1. Check config/cors.php configuration
2. Clear config cache: `php artisan config:clear`
3. Verify frontend URL in FRONTEND_URLS env variable

### Issue: Correlation ID not in response
**Solution:**
1. Check bootstrap/app.php middleware configuration
2. Verify AddCorrelationId middleware is registered
3. Clear route cache: `php artisan route:clear`

---

## Production Deployment Checklist

Before deploying to production:

- [ ] Run full test suite: `php artisan test`
- [ ] Verify error responses don't expose sensitive data
- [ ] Check rate limiting configuration appropriate for production
- [ ] Ensure CORS configured for production frontend URL
- [ ] Set up error monitoring (Sentry/Bugsnag)
- [ ] Configure log aggregation for correlation IDs
- [ ] Test API with production-like load
- [ ] Document API error responses for frontend team
- [ ] Set up alerts for high error rates
- [ ] Configure proper cache backend (Redis)

---

**Note:** Tests sử dụng RefreshDatabase trait nên mỗi test sẽ reset database. Điều này đảm bảo tests isolated và không ảnh hưởng lẫn nhau.
