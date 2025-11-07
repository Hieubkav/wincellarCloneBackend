<?php

namespace Database\Seeders;

use App\Models\Visitor;
use App\Models\VisitorSession;
use App\Models\TrackingEvent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TrackingDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Tạo dữ liệu tracking mẫu...');
        
        $periods = [
            'today' => [Carbon::now()->startOfDay(), Carbon::now()],
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'this_week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'last_week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        ];

        foreach ($periods as $period => [$start, $end]) {
            $this->createTrackingData($start, $end, $period);
        }

        $this->command->info('Hoàn thành!');
    }

    private function createTrackingData(Carbon $start, Carbon $end, string $period): void
    {
        $visitorCount = match($period) {
            'today' => rand(50, 100),
            'yesterday' => rand(40, 80),
            'this_week' => rand(200, 300),
            'last_week' => rand(150, 250),
            'this_month' => rand(500, 800),
            default => rand(50, 100),
        };

        for ($i = 0; $i < $visitorCount; $i++) {
            $firstSeen = Carbon::instance(fake()->dateTimeBetween($start, $end));
            $lastSeen = $firstSeen->copy()->addMinutes(rand(1, 60));

            $visitor = Visitor::create([
                'anon_id' => Str::uuid(),
                'ip_hash' => hash('sha256', fake()->ipv4()),
                'user_agent' => fake()->userAgent(),
                'first_seen_at' => $firstSeen,
                'last_seen_at' => $lastSeen,
            ]);

            $sessionCount = rand(1, 3);
            for ($j = 0; $j < $sessionCount; $j++) {
                $sessionStart = $firstSeen->copy()->addMinutes($j * 20);
                $sessionEnd = $sessionStart->copy()->addMinutes(rand(5, 30));

                $session = VisitorSession::create([
                    'visitor_id' => $visitor->id,
                    'started_at' => $sessionStart,
                    'ended_at' => $sessionEnd,
                ]);

                $eventCount = rand(3, 10);
                for ($k = 0; $k < $eventCount; $k++) {
                    $eventTime = $sessionStart->copy()->addMinutes($k * 2);

                    $eventType = fake()->randomElement([
                        TrackingEvent::TYPE_PRODUCT_VIEW,
                        TrackingEvent::TYPE_PRODUCT_VIEW,
                        TrackingEvent::TYPE_PRODUCT_VIEW,
                        TrackingEvent::TYPE_ARTICLE_VIEW,
                        TrackingEvent::TYPE_CTA_CONTACT,
                    ]);

                    TrackingEvent::create([
                        'visitor_id' => $visitor->id,
                        'session_id' => $session->id,
                        'event_type' => $eventType,
                        'occurred_at' => $eventTime,
                        'metadata' => [
                            'placement' => fake()->randomElement(['grid', 'detail', 'home']),
                        ],
                    ]);
                }
            }
        }

        $this->command->info("Tạo {$visitorCount} visitors cho period: {$period}");
    }
}
