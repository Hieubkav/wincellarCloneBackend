## Common Patterns

### Repository Pattern
```php
interface PostRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function create(array $data);
}

class PostRepository implements PostRepositoryInterface
{
    public function all()
    {
        return Post::with('user')->latest()->get();
    }

    public function find(int $id)
    {
        return Post::with('user', 'comments')->findOrFail($id);
    }
}
```

### Action Classes (Single Responsibility)
```php
class CreatePost
{
    public function execute(array $data): Post
    {
        return DB::transaction(function () use ($data) {
            $post = Post::create($data);
            $post->tags()->attach($data['tag_ids']);
            event(new PostCreated($post));
            return $post;
        });
    }
}
```

### Query Scopes
```php
class Post extends Model
{
    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now());
    }

    public function scopeByAuthor($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}

// Usage
Post::published()->byAuthor($user)->get();
```
