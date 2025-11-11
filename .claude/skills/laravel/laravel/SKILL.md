---
name: laravel
description: Laravel v12 - The PHP Framework For Web Artisans
---
## When to Use This Skill

This skill should be triggered when:
- Building Laravel applications or APIs
- Working with Eloquent models, relationships, and queries
- Setting up authentication, authorization, or API tokens
- Creating database migrations, seeders, or factories
- Implementing middleware, service providers, or events
- Using Laravel's built-in features (queues, cache, validation, etc.)
- Troubleshooting Laravel errors or performance issues
- Following Laravel best practices and conventions
- Implementing RESTful APIs with Laravel Sanctum or Passport
- Working with Laravel Mix, Vite, or frontend assets

## Reference Files

This skill includes comprehensive documentation in `references/`:

- **other.md** - Laravel 12.x installation guide and core documentation

Use the reference files for detailed information about:
- Installation and configuration
- Framework architecture and concepts
- Advanced features and packages
- Deployment and optimization

## Key Concepts

### MVC Architecture
Laravel follows the Model-View-Controller pattern:
- **Models**: Eloquent ORM classes representing database tables
- **Views**: Blade templates for rendering HTML
- **Controllers**: Handle HTTP requests and return responses

### Eloquent ORM
Laravel's powerful database abstraction layer:
- **Active Record pattern**: Each model instance represents a database row
- **Relationships**: belongsTo, hasMany, belongsToMany, morphMany, etc.
- **Query Builder**: Fluent interface for building SQL queries
- **Eager Loading**: Prevent N+1 query problems with `with()`

### Routing
Define application endpoints:
- **Route methods**: get, post, put, patch, delete
- **Route parameters**: Required `{id}` and optional `{id?}`
- **Route groups**: Share middleware, prefixes, namespaces
- **Resource routes**: Auto-generate RESTful routes

### Middleware
Filter HTTP requests:
- **Built-in**: auth, throttle, verified, signed
- **Custom**: Create your own request/response filters
- **Global**: Apply to all routes
- **Route-specific**: Apply to specific routes or groups

### Service Container
Laravel's dependency injection container:
- **Automatic resolution**: Type-hint dependencies in constructors
- **Binding**: Register class implementations
- **Singletons**: Share single instance across requests

### Artisan Commands
Laravel's CLI tool:
```bash
php artisan make:model Post -mcr  # Create model, migration, controller, resource
php artisan migrate               # Run migrations
php artisan db:seed              # Seed database
php artisan queue:work           # Process queue jobs
php artisan optimize:clear       # Clear all caches
```

## Working with This Skill

### For Beginners
Start with:
1. **Installation**: Set up Laravel using Composer
2. **Routing**: Learn basic route definitions in `routes/web.php`
3. **Controllers**: Create controllers with `php artisan make:controller`
4. **Models**: Understand Eloquent basics and relationships
5. **Migrations**: Define database schema with migrations
6. **Blade Templates**: Create views with Laravel's templating engine

### For Intermediate Users
Focus on:
- **Form Requests**: Validation and authorization in dedicated classes
- **API Resources**: Transform models for JSON responses
- **Authentication**: Implement with Laravel Breeze or Sanctum
- **Relationships**: Master eager loading and complex relationships
- **Queues**: Offload time-consuming tasks to background jobs
- **Events & Listeners**: Decouple application logic

### For Advanced Users
Explore:
- **Service Providers**: Register application services
- **Custom Middleware**: Create reusable request filters
- **Package Development**: Build reusable Laravel packages
- **Testing**: Write feature and unit tests with PHPUnit
- **Performance**: Optimize queries, caching, and response times
- **Deployment**: CI/CD pipelines and production optimization

### Navigation Tips
- Check **Quick Reference** for common code patterns
- Reference the official docs at https://laravel.com/docs/12.x
- Use `php artisan route:list` to view all registered routes
- Use `php artisan tinker` for interactive debugging
- Enable query logging to debug database performance

## Resources

### Official Documentation
- Laravel Docs: https://laravel.com/docs/12.x
- API Reference: https://laravel.com/api/12.x
- Laracasts: https://laracasts.com (video tutorials)

### Community
- Laravel News: https://laravel-news.com
- Laravel Forums: https://laracasts.com/discuss
- GitHub: https://github.com/laravel/laravel

### Tools
- Laravel Telescope: Debugging and monitoring
- Laravel Horizon: Queue monitoring
- Laravel Debugbar: Development debugging
- Laravel IDE Helper: IDE autocompletion

## Best Practices

1. **Use Form Requests**: Separate validation logic from controllers
2. **Eager Load Relationships**: Avoid N+1 query problems
3. **Use Resource Controllers**: Follow RESTful conventions
4. **Type Hints**: Leverage PHP type declarations for better IDE support
5. **Database Transactions**: Wrap related database operations
6. **Queue Jobs**: Offload slow operations to background workers
7. **Cache Queries**: Cache expensive database queries
8. **API Resources**: Transform data consistently for APIs
9. **Events**: Decouple application logic with events and listeners
10. **Tests**: Write tests for critical application logic

## Notes

- Laravel 12.x requires PHP 8.2 or higher
- Uses Composer for dependency management
- Includes Vite for asset compilation (replaces Laravel Mix)
- Supports multiple database systems (MySQL, PostgreSQL, SQLite, SQL Server)
- Built-in support for queues, cache, sessions, and file storage
- Excellent ecosystem with first-party packages (Sanctum, Horizon, Telescope, etc.)


---

## References

**Quick Reference:** `read .claude/skills/laravel/references/quick-reference.md`
**Common Patterns:** `read .claude/skills/laravel/references/common-patterns.md`
