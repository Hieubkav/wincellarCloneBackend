<?php

namespace App\Providers;

use App\Support\Security\IpHasher;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(IpHasher::class, function ($app): IpHasher {
            $config = $app['config']->get('security.pii', []);

            $algorithm = (string) ($config['ip_hash_algo'] ?? 'sha256');
            $salt = $config['ip_hash_salt'] ?? $app['config']->get('app.key');

            return new IpHasher($algorithm, $salt);
        });
    }

    public function boot(): void
    {
        HttpRequest::macro('ipHash', function (?string $ip = null): ?string {
            /** @var HttpRequest $this */
            $value = $ip ?? $this->ip();

            return app(IpHasher::class)->hash($value);
        });

        RateLimiter::for('api', function (HttpRequest $request) {
            $config = config('security.rate_limit');
            $limit = (int) ($config['api_per_minute'] ?? 60);
            $decay = (int) ($config['api_decay_minutes'] ?? 1);

            $key = $request->user()?->getAuthIdentifier()
                ? 'user:'.$request->user()->getAuthIdentifier()
                : 'ip:'.($request->ipHash() ?? 'guest');

            return Limit::perMinutes($decay, $limit)->by($key);
        });
    }
}
