<?php

use App\Exceptions\ApiException;
use App\Http\Responses\ErrorResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add correlation ID and performance monitoring middleware for API routes
        $middleware->api(prepend: [
            \App\Http\Middleware\AddCorrelationId::class,
            \App\Http\Middleware\PerformanceMonitor::class,
        ]);
        
        // Add cache headers for static assets (images, fonts, etc.)
        $middleware->web(append: [
            \App\Http\Middleware\AddStaticAssetCacheHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom API exceptions
        $exceptions->render(function (ApiException $e) {
            return $e->render();
        });

        // Validation exceptions for API routes
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return ErrorResponse::validation($e->errors());
            }
        });

        // Not found exceptions for API routes
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                $resource = 'Resource';
                $identifier = $request->path();

                // Try to extract resource name from path
                if (preg_match('#api/v\d+/([^/]+)#', $request->path(), $matches)) {
                    $resource = ucfirst($matches[1]);
                }

                return ErrorResponse::notFound($resource, $identifier);
            }
        });

        // Rate limit exceptions for API routes
        $exceptions->render(function (TooManyRequestsHttpException $e, $request) {
            if ($request->is('api/*')) {
                $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;
                return ErrorResponse::rateLimitExceeded((int) $retryAfter);
            }
        });

        // Generic exceptions for API routes (production only)
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->is('api/*') && !app()->environment('local')) {
                // Log error with correlation ID
                logger()->error('API Error', [
                    'correlation_id' => $request->header('X-Correlation-ID'),
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return ErrorResponse::internalError();
            }
        });
    })->create();
