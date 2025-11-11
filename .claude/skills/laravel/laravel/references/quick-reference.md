## Quick Reference

### Basic Routing

```php
// Basic routes
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);

// Route parameters
Route::get('/users/{id}', function ($id) {
    return User::find($id);
});

// Named routes
Route::get('/profile', ProfileController::class)->name('profile');

// Route groups with middleware
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('posts', PostController::class);
});
```

### Eloquent Model Basics

```php
// Define a model with relationships
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'user_id'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

### Database Migrations

```php
// Create a migration
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

### Form Validation

```php
// Controller validation
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
        'email' => 'required|email|unique:users',
        'tags' => 'array|min:1',
        'tags.*' => 'string|max:50',
    ]);

    return Post::create($validated);
}

// Form Request validation
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|max:255',
            'content' => 'required|min:100',
        ];
    }
}
```

### Eloquent Query Builder

```php
// Common query patterns
// Eager loading to avoid N+1 queries
$posts = Post::with(['user', 'comments'])
    ->where('published_at', '<=', now())
    ->orderBy('published_at', 'desc')
    ->paginate(15);

// Conditional queries
$query = Post::query();

if ($request->has('search')) {
    $query->where('title', 'like', "%{$request->search}%");
}

if ($request->has('author')) {
    $query->whereHas('user', function ($q) use ($request) {
        $q->where('name', $request->author);
    });
}

$posts = $query->get();
```

### API Resource Controllers

```php
namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        return PostResource::collection(
            Post::with('user')->latest()->paginate()
        );
    }

    public function store(Request $request)
    {
        $post = Post::create($request->validated());

        return new PostResource($post);
    }

    public function show(Post $post)
    {
        return new PostResource($post->load('user', 'comments'));
    }

    public function update(Request $request, Post $post)
    {
        $post->update($request->validated());

        return new PostResource($post);
    }
}
```

### API Resources (Transformers)

```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->when($request->routeIs('posts.show'), $this->content),
            'author' => new UserResource($this->whenLoaded('user')),
            'comments_count' => $this->when($this->comments_count, $this->comments_count),
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
```

### Authentication with Sanctum

```php
// API token authentication setup
// In config/sanctum.php - configure stateful domains

// Issue tokens
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}

// Login endpoint
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $request->user()->createToken('api-token')->plainTextToken;

    return response()->json(['token' => $token]);
}

// Protect routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $r) => $r->user());
});
```

### Jobs and Queues

```php
// Create a job
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProcessVideo implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public function __construct(
        public Video $video
    ) {}

    public function handle(): void
    {
        // Process the video
        $this->video->process();
    }
}

// Dispatch jobs
ProcessVideo::dispatch($video);
ProcessVideo::dispatch($video)->onQueue('videos')->delay(now()->addMinutes(5));
```

### Service Container and Dependency Injection

```php
// Bind services in AppServiceProvider
use App\Services\PaymentService;

public function register(): void
{
    $this->app->singleton(PaymentService::class, function ($app) {
        return new PaymentService(
            config('services.stripe.secret')
        );
    });
}

// Use dependency injection in controllers
public function __construct(
    protected PaymentService $payment
) {}

public function charge(Request $request)
{
    return $this->payment->charge(
        $request->user(),
        $request->amount
    );
}
```
