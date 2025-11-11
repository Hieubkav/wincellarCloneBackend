## Quick Reference

### 1. Basic Browser Test

```php
public function testBasicExample(): void
{
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->type('email', 'user@example.com')
            ->type('password', 'password')
            ->press('Login')
            ->assertPathIs('/home');
    });
}
```

### 2. Using Dusk Selectors (Recommended)

```html
<!-- In your Blade template -->
<button dusk="login-button">Login</button>
<input dusk="email-input" name="email" />
```

```php
// In your test - use @ prefix for dusk selectors
$browser->type('@email-input', 'user@example.com')
    ->click('@login-button');
```

### 3. Testing Multiple Browsers

```php
public function testMultiUserInteraction(): void
{
    $this->browse(function (Browser $first, Browser $second) {
        $first->loginAs(User::find(1))
            ->visit('/home');

        $second->loginAs(User::find(2))
            ->visit('/home');
    });
}
```

### 4. Waiting for Elements

```php
// Wait for element to appear
$browser->waitFor('.modal')
    ->assertSee('Confirmation Required');

// Wait for text to appear
$browser->waitForText('Hello World');

// Wait for JavaScript condition
$browser->waitUntil('App.data.servers.length > 0');

// Wait when element is available
$browser->whenAvailable('.modal', function (Browser $modal) {
    $modal->assertSee('Delete Account')
        ->press('OK');
});
```

### 5. Form Interactions

```php
// Text input
$browser->type('email', 'user@example.com')
    ->append('notes', 'Additional text')
    ->clear('description');

// Dropdown selection
$browser->select('size', 'Large')
    ->select('categories', ['Art', 'Music']); // Multiple

// Checkboxes and radio buttons
$browser->check('terms')
    ->radio('gender', 'male');

// File upload
$browser->attach('photo', __DIR__.'/photos/profile.jpg');
```

### 6. Page Object Pattern

```php
// Generate page object
// php artisan dusk:page Login

// app/tests/Browser/Pages/Login.php
class Login extends Page
{
    public function url(): string
    {
        return '/login';
    }

    public function elements(): array
    {
        return [
            '@email' => 'input[name=email]',
            '@password' => 'input[name=password]',
            '@submit' => 'button[type=submit]',
        ];
    }

    public function login(Browser $browser, $email, $password): void
    {
        $browser->type('@email', $email)
            ->type('@password', $password)
            ->press('@submit');
    }
}

// Use in test
$browser->visit(new Login)
    ->login('user@example.com', 'password')
    ->assertPathIs('/dashboard');
```

### 7. Browser Macros (Reusable Methods)

```php
// In AppServiceProvider or DuskServiceProvider
use Laravel\Dusk\Browser;

Browser::macro('scrollToElement', function (string $element) {
    $this->script("$('html, body').animate({
        scrollTop: $('{$element}').offset().top
    }, 0);");

    return $this;
});

// Use in tests
$browser->scrollToElement('#footer')
    ->assertSee('Copyright 2024');
```

### 8. Database Management in Tests

```php
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class ExampleTest extends DuskTestCase
{
    // Option 1: Run migrations before each test (slower)
    use DatabaseMigrations;

    // Option 2: Truncate tables after first migration (faster)
    use DatabaseTruncation;

    // Exclude specific tables from truncation
    protected $exceptTables = ['migrations'];
}
```

### 9. JavaScript Execution

```php
// Execute JavaScript
$browser->script('document.documentElement.scrollTop = 0');

// Get JavaScript return value
$path = $browser->script('return window.location.pathname');

// Wait for reload after action
$browser->waitForReload(function (Browser $browser) {
    $browser->press('Submit');
})->assertSee('Success');
```

### 10. Common Assertions

```php
// Page assertions
$browser->assertPathIs('/dashboard')
    ->assertRouteIs('dashboard')
    ->assertTitle('Dashboard')
    ->assertSee('Welcome Back')
    ->assertDontSee('Error');

// Form assertions
$browser->assertInputValue('email', 'user@example.com')
    ->assertChecked('remember')
    ->assertSelected('role', 'admin')
    ->assertEnabled('submit-button');

// Element assertions
$browser->assertVisible('.success-message')
    ->assertMissing('.error-alert')
    ->assertPresent('button[type=submit]');

// Authentication assertions
$browser->assertAuthenticated()
    ->assertAuthenticatedAs($user);
```
