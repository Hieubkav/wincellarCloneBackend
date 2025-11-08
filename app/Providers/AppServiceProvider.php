<?php

namespace App\Providers;

use App\Models\CatalogAttributeGroup;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\RichEditorMedia;
use App\Observers\CatalogAttributeGroupObserver;
use App\Observers\ImageObserver;
use App\Observers\ProductCategoryObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductTypeObserver;
use App\Observers\RichEditorMediaObserver;
use App\Support\Security\IpHasher;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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
        // Đăng ký Observer
        CatalogAttributeGroup::observe(CatalogAttributeGroupObserver::class);
        Image::observe(ImageObserver::class);
        Product::observe(ProductObserver::class);
        ProductCategory::observe(ProductCategoryObserver::class);
        ProductType::observe(ProductTypeObserver::class);
        RichEditorMedia::observe(RichEditorMediaObserver::class);

        $this->normalizeLoopbackAppUrl();

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

    private function normalizeLoopbackAppUrl(): void
    {
        $appUrl = config('app.url');

        if (!$appUrl) {
            return;
        }

        $normalized = $this->coerceLocalhostToIpv4($appUrl);

        if ($normalized === $appUrl) {
            return;
        }

        config()->set('app.url', $normalized);
        URL::forceRootUrl($normalized);
    }

    private function coerceLocalhostToIpv4(string $value): string
    {
        try {
            $parts = parse_url($value);

            if ($parts === false) {
                return $value;
            }

            if (!isset($parts['host']) || strtolower($parts['host']) !== 'localhost') {
                return $value;
            }

            $parts['host'] = '127.0.0.1';

            $scheme = $parts['scheme'] ?? 'http';
            $user = $parts['user'] ?? '';
            $pass = $parts['pass'] ?? '';
            $auth = $user !== '' ? $user.($pass !== '' ? ':'.$pass : '').'@' : '';
            $host = $parts['host'];
            $port = isset($parts['port']) ? ':'.$parts['port'] : '';
            $path = $parts['path'] ?? '';
            $query = isset($parts['query']) ? '?'.$parts['query'] : '';
            $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

            return sprintf('%s://%s%s%s%s%s%s', $scheme, $auth, $host, $port, $path, $query, $fragment);
        } catch (\Throwable) {
            return $value;
        }
    }
}
