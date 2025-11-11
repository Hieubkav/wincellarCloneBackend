## Working with This Skill

### For Beginners

Start with basic prompts:
1. Use `text()` for simple input
2. Add `required: true` for mandatory fields
3. Try `confirm()` for yes/no questions
4. Use `select()` for predefined choices

Example beginner command:
```php
$name = text('What is your name?', required: true);
$confirmed = confirm('Is this correct?');
if ($confirmed) {
    $this->info("Hello, {$name}!");
}
```

### For Intermediate Users

Combine multiple prompts and add validation:
1. Use the `form()` API for multi-step input
2. Add custom validation with closures
3. Use `search()` for database queries
4. Implement progress bars for long operations

Example intermediate command:
```php
$responses = form()
    ->text('Name', required: true, name: 'name')
    ->select('Role', options: ['Member', 'Admin'], name: 'role')
    ->confirm('Create user?')
    ->submit();

if ($responses) {
    progress(
        label: 'Creating user',
        steps: 5,
        callback: fn () => sleep(1)
    );
}
```

### For Advanced Users

Leverage advanced features:
1. Dynamic form fields based on previous responses
2. Complex validation with Laravel validation rules
3. Custom searchable prompts with database integration
4. Transformation functions for data normalization
5. Testing strategies for command prompts

Example advanced command:
```php
$responses = form()
    ->text('Email', validate: ['email' => 'required|email|unique:users'], name: 'email')
    ->add(function ($responses) {
        return search(
            label: 'Select manager',
            options: fn ($value) => User::where('email', 'like', "%{$value}%")
                ->where('email', '!=', $responses['email'])
                ->pluck('name', 'id')
                ->all()
        );
    }, name: 'manager_id')
    ->multiselect(
        label: 'Permissions',
        options: Permission::pluck('name', 'id'),
        validate: fn ($values) => count($values) === 0 ? 'Select at least one permission.' : null,
        name: 'permissions'
    )
    ->submit();
```

### Navigation Tips

- **Arrow keys** or **j/k** - Navigate options in select/multiselect
- **Space** - Select/deselect in multiselect
- **Enter** - Confirm selection or submit input
- **CTRL + U** - Go back to previous prompt (in forms)
- **Type to search** - In search/multisearch prompts
- **Tab** - Auto-complete in suggest prompts
