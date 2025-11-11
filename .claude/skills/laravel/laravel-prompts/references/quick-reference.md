## Quick Reference

### Basic Text Input

```php
use function Laravel\Prompts\text;

// Simple text input
$name = text('What is your name?');

// With placeholder and validation
$name = text(
    label: 'What is your name?',
    placeholder: 'E.g. Taylor Otwell',
    required: true,
    validate: fn (string $value) => match (true) {
        strlen($value) < 3 => 'The name must be at least 3 characters.',
        strlen($value) > 255 => 'The name must not exceed 255 characters.',
        default => null
    }
);
```

### Password Input

```php
use function Laravel\Prompts\password;

$password = password(
    label: 'What is your password?',
    placeholder: 'password',
    hint: 'Minimum 8 characters.',
    required: true,
    validate: fn (string $value) => match (true) {
        strlen($value) < 8 => 'The password must be at least 8 characters.',
        default => null
    }
);
```

### Select (Single Choice)

```php
use function Laravel\Prompts\select;

// Simple select
$role = select(
    label: 'What role should the user have?',
    options: ['Member', 'Contributor', 'Owner']
);

// With associative array (returns key)
$role = select(
    label: 'What role should the user have?',
    options: [
        'member' => 'Member',
        'contributor' => 'Contributor',
        'owner' => 'Owner',
    ],
    default: 'owner'
);

// From database with custom scroll
$role = select(
    label: 'Which category would you like to assign?',
    options: Category::pluck('name', 'id'),
    scroll: 10
);
```

### Multiselect (Multiple Choices)

```php
use function Laravel\Prompts\multiselect;

$permissions = multiselect(
    label: 'What permissions should be assigned?',
    options: ['Read', 'Create', 'Update', 'Delete'],
    default: ['Read', 'Create'],
    hint: 'Permissions may be updated at any time.'
);

// With validation
$permissions = multiselect(
    label: 'What permissions should the user have?',
    options: [
        'read' => 'Read',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
    ],
    validate: fn (array $values) => ! in_array('read', $values)
        ? 'All users require the read permission.'
        : null
);
```

### Confirmation Dialog

```php
use function Laravel\Prompts\confirm;

// Simple yes/no
$confirmed = confirm('Do you accept the terms?');

// With custom labels
$confirmed = confirm(
    label: 'Do you accept the terms?',
    default: false,
    yes: 'I accept',
    no: 'I decline',
    hint: 'The terms must be accepted to continue.'
);

// Require "Yes"
$confirmed = confirm(
    label: 'Do you accept the terms?',
    required: true
);
```

### Search (Searchable Select)

```php
use function Laravel\Prompts\search;

$id = search(
    label: 'Search for the user that should receive the mail',
    placeholder: 'E.g. Taylor Otwell',
    options: fn (string $value) => strlen($value) > 0
        ? User::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
        : [],
    hint: 'The user will receive an email immediately.',
    scroll: 10
);
```

### Suggest (Auto-completion)

```php
use function Laravel\Prompts\suggest;

// Static options
$name = suggest('What is your name?', ['Taylor', 'Dayle']);

// Dynamic filtering
$name = suggest(
    label: 'What is your name?',
    options: fn ($value) => collect(['Taylor', 'Dayle'])
        ->filter(fn ($name) => Str::contains($name, $value, ignoreCase: true))
);
```

### Multi-step Forms

```php
use function Laravel\Prompts\form;

$responses = form()
    ->text('What is your name?', required: true, name: 'name')
    ->password(
        label: 'What is your password?',
        validate: ['password' => 'min:8'],
        name: 'password'
    )
    ->confirm('Do you accept the terms?')
    ->submit();

// Access named responses
echo $responses['name'];
echo $responses['password'];

// Dynamic forms with previous responses
$responses = form()
    ->text('What is your name?', required: true, name: 'name')
    ->add(function ($responses) {
        return text("How old are you, {$responses['name']}?");
    }, name: 'age')
    ->submit();
```

### Progress Bar

```php
use function Laravel\Prompts\progress;

// Simple usage
$users = progress(
    label: 'Updating users',
    steps: User::all(),
    callback: fn ($user) => $this->performTask($user)
);

// With dynamic labels
$users = progress(
    label: 'Updating users',
    steps: User::all(),
    callback: function ($user, $progress) {
        $progress
            ->label("Updating {$user->name}")
            ->hint("Created on {$user->created_at}");
        return $this->performTask($user);
    },
    hint: 'This may take some time.'
);
```

### Loading Spinner

```php
use function Laravel\Prompts\spin;

$response = spin(
    callback: fn () => Http::get('http://example.com'),
    message: 'Fetching response...'
);
```
