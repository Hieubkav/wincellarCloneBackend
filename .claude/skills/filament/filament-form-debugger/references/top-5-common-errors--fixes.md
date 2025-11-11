## Top 5 Common Errors & Fixes

### 1. Tabs/Grid/Section Not Found

**Error:**
```
Class "Filament\Forms\Components\Tabs" not found
```

**Fix:**
```php
// ❌ WRONG
use Filament\Forms\Components\Tabs;

// ✅ CORRECT
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
```

**Rule:** Layout components → `Schemas\Components\*`

### 2. TextInput/Select Not Found

**Error:**
```
Class "Filament\Schemas\Components\TextInput" not found
```

**Fix:**
```php
// ❌ WRONG
use Filament\Schemas\Components\TextInput;

// ✅ CORRECT
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
```

**Rule:** Form fields → `Forms\Components\*`

### 3. Get Utility Type Mismatch

**Error:**
```
Argument #1 ($get) must be of type ...
```

**Fix:**
```php
// ❌ WRONG
use Filament\Forms\Get;

// ✅ CORRECT (for Schema closures)
use Filament\Schemas\Components\Utilities\Get;

->visible(fn (Get $get) => $get('type') === 'special')
```

**Rule:** Get in Schema context → `Utilities\Get`

### 4. Form Type Mismatch

**Error:**
```
Argument #1 ($form) must be of type Form, Schema given
```

**Fix:**
```php
// ❌ WRONG
use Filament\Forms\Form;
public function form(Form $form): Form

// ✅ CORRECT
use Filament\Schemas\Schema;
public function form(Schema $schema): Schema
```

**Rule:** Settings pages & Resources use `Schema`

### 5. HasFormActions Trait Not Found

**Error:**
```
Trait "HasFormActions" not found
```

**Fix:**
```php
// ❌ WRONG
use HasFormActions;  // Doesn't exist!

// ✅ CORRECT
use InteractsWithForms;  // Only this trait

// Add button manually in blade:
<x-filament::button type="submit">Lưu</x-filament::button>
```

