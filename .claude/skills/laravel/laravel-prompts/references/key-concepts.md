## Key Concepts

### Input Types

Laravel Prompts provides several input types for different use cases:

- **text()** - Single-line text input with optional placeholder and validation
- **textarea()** - Multi-line text input for longer content
- **password()** - Masked text input for sensitive data
- **confirm()** - Yes/No confirmation dialog
- **select()** - Single selection from a list of options
- **multiselect()** - Multiple selections from a list
- **suggest()** - Text input with auto-completion suggestions
- **search()** - Searchable single selection with dynamic options
- **multisearch()** - Searchable multiple selections
- **pause()** - Pause execution until user presses ENTER

### Output Types

For displaying information without input:

- **info()** - Display informational message
- **note()** - Display a note
- **warning()** - Display warning message
- **error()** - Display error message
- **alert()** - Display alert message
- **table()** - Display tabular data

### Validation

Three ways to validate prompts:

1. **Closure validation**: Custom logic with match expressions
   ```php
   validate: fn (string $value) => match (true) {
       strlen($value) < 3 => 'Too short.',
       default => null
   }
   ```

2. **Laravel validation rules**: Standard Laravel validation
   ```php
   validate: ['email' => 'required|email|unique:users']
   ```

3. **Required flag**: Simple requirement check
   ```php
   required: true
   ```

### Transformation

Use the `transform` parameter to modify input before validation:

```php
$name = text(
    label: 'What is your name?',
    transform: fn (string $value) => trim($value),
    validate: fn (string $value) => strlen($value) < 3
        ? 'The name must be at least 3 characters.'
        : null
);
```

### Terminal Features

- **Scrolling**: Configure visible items with `scroll` parameter (default: 5)
- **Navigation**: Use arrow keys, j/k keys, or vim-style navigation
- **Forms**: Press CTRL + U in forms to return to previous prompts
- **Width**: Keep labels under 74 characters for 80-character terminals
