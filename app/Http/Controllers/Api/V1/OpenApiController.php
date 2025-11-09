<?php

namespace App\Http\Controllers\Api\V1;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Wincellar API Documentation",
 *     description="API documentation for Wincellar - Wine, Beer, and Food E-commerce Platform",
 *     @OA\Contact(
 *         email="api@wincellar.com"
 *     ),
 *     @OA\License(
 *         name="Proprietary",
 *         url="https://wincellar.com/license"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\Tag(
 *     name="Products",
 *     description="Product catalog endpoints - list, filter, search products"
 * )
 *
 * @OA\Tag(
 *     name="Articles",
 *     description="Blog articles and editorial content"
 * )
 *
 * @OA\Tag(
 *     name="Health",
 *     description="Health check and system status"
 * )
 *
 * @OA\Tag(
 *     name="Home",
 *     description="Homepage data and components"
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     required={"error", "message", "timestamp", "path", "correlation_id"},
 *     @OA\Property(
 *         property="error",
 *         type="string",
 *         example="ValidationError",
 *         description="Error type"
 *     ),
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="Request validation failed"
 *     ),
 *     @OA\Property(
 *         property="timestamp",
 *         type="string",
 *         format="date-time",
 *         example="2025-11-09T15:30:00Z"
 *     ),
 *     @OA\Property(
 *         property="path",
 *         type="string",
 *         example="api/v1/san-pham"
 *     ),
 *     @OA\Property(
 *         property="correlation_id",
 *         type="string",
 *         format="uuid",
 *         example="550e8400-e29b-41d4-a716-446655440000"
 *     ),
 *     @OA\Property(
 *         property="details",
 *         type="object",
 *         nullable=true
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     @OA\Property(property="page", type="integer", example=1),
 *     @OA\Property(property="per_page", type="integer", example=24),
 *     @OA\Property(property="total", type="integer", example=100),
 *     @OA\Property(property="last_page", type="integer", example=5),
 *     @OA\Property(property="has_more", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="Link",
 *     type="object",
 *     @OA\Property(property="href", type="string", example="http://localhost/api/v1/san-pham"),
 *     @OA\Property(property="method", type="string", example="GET")
 * )
 *
 * @OA\Parameter(
 *     parameter="correlation_id",
 *     name="X-Correlation-ID",
 *     in="header",
 *     required=false,
 *     description="Correlation ID for request tracking",
 *     @OA\Schema(type="string", format="uuid")
 * )
 *
 * @OA\Response(
 *     response="ValidationError",
 *     description="Validation Error",
 *     @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 *
 * @OA\Response(
 *     response="NotFound",
 *     description="Resource Not Found",
 *     @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 *
 * @OA\Response(
 *     response="RateLimitExceeded",
 *     description="Rate Limit Exceeded (60 requests/minute)",
 *     @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 */
class OpenApiController
{
    // This class is only used for OpenAPI annotations
}
