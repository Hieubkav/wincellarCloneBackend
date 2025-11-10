# Quick Start: API Improvements Implementation

**Thời gian ước tính:** 1-2 tuần cho Phase 1 (Critical)
**Mục tiêu:** Standardize API theo best practices

---

## 🚀 Priority #1: Standardized Error Handling (2 days)

### Step 1: Tạo Error Response Classes

**File: `app/Http/Responses/ErrorType.php`**
```php
<?php

namespace App\Http\Responses;

enum ErrorType: string
{
    case VALIDATION = 'ValidationError';
    case NOT_FOUND = 'NotFound';
    case CONFLICT = 'Conflict';
    case BAD_REQUEST = 'BadRequest';
    case UNAUTHORIZED = 'Unauthorized';
    case FORBIDDEN = 'Forbidden';
    case INTERNAL_ERROR = 'InternalServerError';
    case RATE_LIMIT = 'RateLimitExceeded';
}
```

**File: `app/Http/Responses/ErrorResponse.php`**
```php
<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ErrorResponse
{
    public static function make(
        ErrorType $type,
        string $message,
        ?array $details = null,
        int $statusCode = 500
    ): JsonResponse {
        return response()->json([
            'error' => $type->value,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
            'path' => request()->path(),
            'correlation_id' => request()->header('X-Correlation-ID') ?? request()->id(),
            'details' => $details,
        ], $statusCode);
    }

    public static function validation(array $errors): JsonResponse
    {
        $formatted = [];
        foreach ($errors as $field => $messages) {
            foreach ((array) $messages as $message) {
                $formatted[] = [
                    'field' => $field,
                    'message' => $message,
                    'value' => request()->input($field),
                ];
            }
        }

        return self::make(
            ErrorType::VALIDATION,
            'Request validation failed',
            ['errors' => $formatted],
            422
        );
    }

    public static function notFound(string $resource, string $identifier): JsonResponse
    {
        return self::make(
            ErrorType::NOT_FOUND,
            "$resource not found",
            ['identifier' => $identifier],
            404
        );
    }

    public static function badRequest(string $message, ?array $details = null): JsonResponse
    {
        return self::make(
            ErrorType::BAD_REQUEST,
            $message,
            $details,
            400
        );
    }

    public static function conflict(string $message, ?array $details = null): JsonResponse
    {
        return self::make(
            ErrorType::CONFLICT,
            $message,
            $details,
            409
        );
    }

    public static function internalError(string $message = 'An unexpected error occurred'): JsonResponse
    {
        return self::make(
            ErrorType::INTERNAL_ERROR,
            $message,
            null,
            500
        );
    }
}
```

### Step 2: Custom Exception Classes

**File: `app/Exceptions/ApiException.php`**
```php
<?php

namespace App\Exceptions;

use App\Http\Responses\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ErrorResponse;

class ApiException extends Exception
{
    public function __construct(
        public ErrorType $type,
        string $message,
        public ?array $details = null,
        public int $statusCode = 500
    ) {
        parent::__construct($message, $statusCode);
    }

    public function render(): JsonResponse
    {
        return ErrorResponse::make(
            $this->type,
            $this->getMessage(),
            $this->details,
            $this->statusCode
        );
    }

    public static function notFound(string $resource, string $identifier): self
    {
        return new self(
            ErrorType::NOT_FOUND,
            "$resource not found",
            ['identifier' => $identifier],
            404
        );
    }

    public static function badRequest(string $message, ?array $details = null): self
    {
        return new self(
            ErrorType::BAD_REQUEST,
            $message,
            $details,
            400
        );
    }
}
```

### Step 3: Correlation ID Middleware

**File: `app/Http/Middleware/AddCorrelationId.php`**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AddCorrelationId
{
    public function handle(Request $request, Closure $next)
    {
        $correlationId = $request->header('X-Correlation-ID') ?? Str::uuid()->toString();
        
        $request->headers->set('X-Correlation-ID', $correlationId);
        
        $response = $next($request);
        
        if (method_exists($response, 'header')) {
            $response->header('X-Correlation-ID', $correlationId);
        }
        
        return $response;
    }
}
```

### Step 4: Global Exception Handler

**Update: `bootstrap/app.php`**
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Responses\ErrorResponse;
use App\Exceptions\ApiException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \App\Http\Middleware\AddCorrelationId::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API exceptions
        $exceptions->render(function (ApiException $e) {
            return $e->render();
        });
        
        // Validation exceptions
        $exceptions->render(function (ValidationException $e) {
            if (request()->is('api/*')) {
                return ErrorResponse::validation($e->errors());
            }
        });
        
        // Not found exceptions
        $exceptions->render(function (NotFoundHttpException $e) {
            if (request()->is('api/*')) {
                return ErrorResponse::notFound(
                    'Resource',
                    request()->path()
                );
            }
        });
        
        // Generic exceptions for API
        $exceptions->render(function (Throwable $e) {
            if (request()->is('api/*') && !app()->isLocal()) {
                // Log error với correlation ID
                logger()->error('API Error', [
                    'correlation_id' => request()->header('X-Correlation-ID'),
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                
                return ErrorResponse::internalError();
            }
        });
    })
    ->create();
```

### Step 5: Update Controllers

**Update: `app/Http/Controllers/Api/V1/Products/ProductController.php`**
```php
<?php

namespace App\Http\Controllers\Api\V1\Products;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductIndexRequest;
use App\Http\Responses\ErrorResponse;
use App\Models\Product;
use App\Support\Product\ProductOutput;
use App\Support\Product\ProductPaginator;
use App\Support\Product\ProductSearchBuilder;
use App\Support\Product\ProductSorts;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(ProductIndexRequest $request): JsonResponse
    {
        // Validation đã được handle bởi FormRequest + global handler
        
        try {
            $filters = $request->validated();
            
            // Check invalid ranges (nếu cần custom logic)
            if (($filters['price_min'] ?? 0) > ($filters['price_max'] ?? PHP_INT_MAX)) {
                return ErrorResponse::badRequest(
                    'Invalid price range',
                    ['price_min' => $filters['price_min'], 'price_max' => $filters['price_max']]
                );
            }

            // ... existing logic ...
            
            return response()->json([
                'data' => $mapped,
                'meta' => $meta,
            ]);
            
        } catch (\Exception $e) {
            // Log và throw
            logger()->error('Product index error', [
                'correlation_id' => request()->header('X-Correlation-ID'),
                'exception' => $e->getMessage(),
            ]);
            
            throw $e; // Will be caught by global handler
        }
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::query()
            ->with([
                'coverImage',
                'images' => fn ($relation) => $relation->orderBy('order'),
                'terms.group',
                'productCategory',
                'type',
            ])
            ->active()
            ->where('slug', $slug)
            ->first();

        if (!$product) {
            throw ApiException::notFound('Product', $slug);
        }

        return response()->json([
            'data' => ProductOutput::detail($product),
        ]);
    }
}
```

### Step 6: Test Error Responses

**Tạo test cases:**
```bash
php artisan test --filter=ErrorHandlingTest
```

**File: `tests/Feature/Api/ErrorHandlingTest.php`**
```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    public function test_validation_error_returns_422_with_standard_format()
    {
        $response = $this->getJson('/api/v1/san-pham?price_min=abc');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error',
                'message',
                'timestamp',
                'path',
                'correlation_id',
                'details' => [
                    'errors' => [
                        '*' => ['field', 'message', 'value']
                    ]
                ]
            ])
            ->assertJson([
                'error' => 'ValidationError',
            ]);
    }

    public function test_not_found_returns_404_with_standard_format()
    {
        $response = $this->getJson('/api/v1/san-pham/non-existent-slug');

        $response->assertStatus(404)
            ->assertJsonStructure([
                'error',
                'message',
                'timestamp',
                'path',
                'correlation_id',
                'details'
            ])
            ->assertJson([
                'error' => 'NotFound',
            ]);
    }

    public function test_bad_request_returns_400()
    {
        $response = $this->getJson('/api/v1/san-pham?price_min=5000000&price_max=1000000');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'BadRequest',
            ]);
    }

    public function test_correlation_id_is_present_in_response()
    {
        $response = $this->getJson('/api/v1/san-pham');

        $response->assertHeader('X-Correlation-ID');
    }
}
```

---

## 🚀 Priority #2: Rate Limiting (1 day)

### Step 1: Configure Rate Limiting

**Update: `routes/api.php`**
```php
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

// Define rate limiter
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)
        ->by($request->ip())
        ->response(function () {
            return ErrorResponse::make(
                ErrorType::RATE_LIMIT,
                'Too many requests. Please slow down.',
                ['retry_after' => 60],
                429
            );
        });
});

// Apply to routes
Route::middleware(['api', 'throttle:api'])
    ->prefix('v1')
    ->as('api.v1.')
    ->group(function (): void {
        require __DIR__.'/api/home.php';
        require __DIR__.'/api/products.php';
        require __DIR__.'/api/articles.php';
    });
```

### Step 2: Custom Rate Limit Headers

**File: `app/Http/Middleware/AddRateLimitHeaders.php`**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AddRateLimitHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        $key = 'api:' . $request->ip();
        $maxAttempts = 60;
        
        $remaining = RateLimiter::remaining($key, $maxAttempts);
        $retryAfter = RateLimiter::availableIn($key);
        
        if (method_exists($response, 'header')) {
            $response->header('X-RateLimit-Limit', $maxAttempts);
            $response->header('X-RateLimit-Remaining', max(0, $remaining));
            
            if ($retryAfter > 0) {
                $response->header('X-RateLimit-Reset', now()->addSeconds($retryAfter)->timestamp);
            }
        }
        
        return $response;
    }
}
```

### Step 3: Test Rate Limiting

```php
public function test_rate_limiting_works()
{
    for ($i = 0; $i < 60; $i++) {
        $response = $this->getJson('/api/v1/home');
        $response->assertSuccessful();
    }

    $response = $this->getJson('/api/v1/home');
    $response->assertStatus(429)
        ->assertJson(['error' => 'RateLimitExceeded']);
}
```

---

## 🚀 Priority #3: CORS Configuration (1 hour)

### Update: `config/cors.php`

```php
<?php

return [
    'paths' => ['api/*'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
        env('FRONTEND_URL_PROD', 'https://wincellar.com'),
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [
        'X-Correlation-ID',
        'X-RateLimit-Limit',
        'X-RateLimit-Remaining',
        'X-RateLimit-Reset',
    ],
    
    'max_age' => 600,
    
    'supports_credentials' => false,
];
```

### Update: `.env`
```env
FRONTEND_URL=http://localhost:3000
FRONTEND_URL_PROD=https://wincellar.com
```

---

## ✅ Verification Checklist

### Error Handling
- [ ] Validation errors return 422 với standard format
- [ ] Not found returns 404 với standard format
- [ ] Bad request returns 400
- [ ] Internal errors return 500 (in production)
- [ ] Correlation ID present trong mọi response
- [ ] Errors được log với correlation ID

### Rate Limiting
- [ ] 60 requests/minute/IP enforced
- [ ] Rate limit headers present (X-RateLimit-*)
- [ ] 429 response có retry_after
- [ ] Rate limit bypass cho health check

### CORS
- [ ] Frontend có thể call API
- [ ] Preflight requests work
- [ ] Custom headers exposed properly
- [ ] Credentials policy correct

---

## 🧪 Manual Testing

### Test Error Handling
```bash
# Validation error (422)
curl -X GET "http://localhost:8000/api/v1/san-pham?price_min=abc" -H "Accept: application/json" -v

# Not found (404)
curl -X GET "http://localhost:8000/api/v1/san-pham/non-existent" -H "Accept: application/json" -v

# Bad request (400)
curl -X GET "http://localhost:8000/api/v1/san-pham?price_min=5000000&price_max=1000000" -H "Accept: application/json" -v

# Correlation ID
curl -X GET "http://localhost:8000/api/v1/home" -H "X-Correlation-ID: test-123" -v
```

### Test Rate Limiting
```bash
# Send 61 requests rapidly
for i in {1..61}; do
  curl -X GET "http://localhost:8000/api/v1/home" -H "Accept: application/json" -w "\nStatus: %{http_code}\n"
done
```

### Test CORS
```bash
# Preflight request
curl -X OPTIONS "http://localhost:8000/api/v1/home" \
  -H "Origin: http://localhost:3000" \
  -H "Access-Control-Request-Method: GET" \
  -v
```

---

## 📚 Next Steps

After completing Priority #1-3:

1. **Week 3-4:** Migrate to Laravel API Resources
2. **Week 5:** Add OpenAPI/Swagger documentation
3. **Week 6:** Implement monitoring & logging

**Documentation:**
- Update API docs với error response examples
- Document correlation ID usage
- Document rate limiting policy
- Update CORS configuration in README
