<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\VisitorSession;
use App\Models\TrackingEvent;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TrackingService
{
    private const SESSION_TIMEOUT_MINUTES = 30;

    /**
     * Find or create a visitor based on anonymous ID
     */
    public function findOrCreateVisitor(string $anonId, ?string $ipAddress = null, ?string $userAgent = null): Visitor
    {
        $visitor = Visitor::where('anon_id', $anonId)->first();

        if (!$visitor) {
            $visitor = Visitor::create([
                'anon_id' => $anonId,
                'ip_hash' => $ipAddress ? hash('sha256', $ipAddress) : null,
                'user_agent' => $userAgent,
                'first_seen_at' => now(),
                'last_seen_at' => now(),
            ]);
        } else {
            // Update last_seen_at
            $visitor->update([
                'last_seen_at' => now(),
                'ip_hash' => $ipAddress ? hash('sha256', $ipAddress) : $visitor->ip_hash,
                'user_agent' => $userAgent ?? $visitor->user_agent,
            ]);
        }

        return $visitor;
    }

    /**
     * Get current session or create a new one
     */
    public function getOrCreateSession(Visitor $visitor): VisitorSession
    {
        $latestSession = $visitor->sessions()
            ->latest('started_at')
            ->first();

        // Create new session if no session exists or last session expired
        if (!$latestSession || $this->shouldCreateNewSession($latestSession)) {
            return VisitorSession::create([
                'visitor_id' => $visitor->id,
                'started_at' => now(),
                'ended_at' => null,
            ]);
        }

        // Update ended_at of current session
        $latestSession->update(['ended_at' => now()]);

        return $latestSession;
    }

    /**
     * Check if we should create a new session
     */
    private function shouldCreateNewSession(VisitorSession $session): bool
    {
        if (!$session->ended_at) {
            // If ended_at is null, check started_at
            return $session->started_at->diffInMinutes(now()) > self::SESSION_TIMEOUT_MINUTES;
        }

        // If ended_at exists, check if it's older than timeout
        return $session->ended_at->diffInMinutes(now()) > self::SESSION_TIMEOUT_MINUTES;
    }

    /**
     * Record a tracking event
     */
    public function recordEvent(array $data): TrackingEvent
    {
        $visitor = $this->findOrCreateVisitor(
            $data['anon_id'],
            $data['ip_address'] ?? null,
            $data['user_agent'] ?? null
        );

        $session = $this->getOrCreateSession($visitor);

        return TrackingEvent::create([
            'visitor_id' => $visitor->id,
            'session_id' => $session->id,
            'event_type' => $data['event_type'],
            'product_id' => $data['product_id'] ?? null,
            'article_id' => $data['article_id'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Generate a new anonymous ID
     */
    public static function generateAnonId(): string
    {
        return (string) Str::uuid();
    }
}
