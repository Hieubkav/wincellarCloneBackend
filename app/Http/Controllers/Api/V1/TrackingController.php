<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\TrackingEvent;

class TrackingController extends Controller
{
    public function __construct(
        private readonly TrackingService $trackingService
    ) {}

    /**
     * Track visitor and create/update session
     * POST /api/v1/track/visitor
     */
    public function trackVisitor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'anon_id' => ['required', 'string', 'uuid'],
            'user_agent' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $visitor = $this->trackingService->findOrCreateVisitor(
                $request->input('anon_id'),
                $request->ip(),
                $request->input('user_agent') ?? $request->userAgent()
            );

            $session = $this->trackingService->getOrCreateSession($visitor);

            return response()->json([
                'success' => true,
                'data' => [
                    'visitor_id' => $visitor->id,
                    'session_id' => $session->id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track visitor',
            ], 500);
        }
    }

    /**
     * Track an event (product view, article view, CTA click, etc.)
     * POST /api/v1/track/event
     */
    public function trackEvent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'anon_id' => ['required', 'string', 'uuid'],
            'event_type' => [
                'required',
                'string',
                Rule::in([
                    TrackingEvent::TYPE_PRODUCT_VIEW,
                    TrackingEvent::TYPE_ARTICLE_VIEW,
                    TrackingEvent::TYPE_CTA_CONTACT,
                ]),
            ],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'article_id' => ['nullable', 'integer', 'exists:articles,id'],
            'metadata' => ['nullable', 'array'],
            'user_agent' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $event = $this->trackingService->recordEvent([
                'anon_id' => $request->input('anon_id'),
                'event_type' => $request->input('event_type'),
                'product_id' => $request->input('product_id'),
                'article_id' => $request->input('article_id'),
                'metadata' => $request->input('metadata'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->input('user_agent') ?? $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'event_id' => $event->id,
                    'event_type' => $event->event_type,
                    'occurred_at' => $event->occurred_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track event',
            ], 500);
        }
    }

    /**
     * Generate a new anonymous ID for client
     * GET /api/v1/track/generate-id
     */
    public function generateId(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'anon_id' => TrackingService::generateAnonId(),
            ],
        ]);
    }
}
