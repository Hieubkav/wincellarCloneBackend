---
name: filament-form-debugger
description: Diagnose and fix common Filament 4.x form errors in Laravel 12 project - namespace issues (Tabs/Grid/Get from wrong package), class not found, type mismatch (Schema vs Form), argument errors, trait not found. USE WHEN encountering 'Class not found', 'Argument must be of type', 'Trait not found', namespace errors, or any Filament-related compilation/runtime errors.
---

# Filament Form Debugger - Error Diagnosis & Fixes

## When to Activate This Skill

- Error: "Class 'Filament\Forms\Components\Tabs' not found"
- Error: "Class 'Filament\Schemas\Components\TextInput' not found"
- Error: "Argument #1 ($form) must be of type Filament\Forms\Form, Filament\Schemas\Schema given"
- Error: "Argument #1 ($get) must be of type Filament\Forms\Get"
- Error: "Trait 'Filament\Pages\Concerns\HasFormActions' not found"
- Any namespace-related Filament error
- Form not displaying correctly
- Components not rendering

## 🔧 Common Error Patterns & Fixes

### Error 1: Tabs/Grid/Section Not Found

**Error message:**
```
Class "Filament\Forms\Components\Tabs" not found
Class "Filament\Forms\Components\Grid" not found
Class "Filament\Forms\Components\Section" not found
```

**Cause:** Using Forms\Components for layout components

**Fix:**
```php
// ❌ WRONG
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

// ✅ CORRECT
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
```

**Rule:** Layout components → `Filament\Schemas\Components\*`

### Error 2: TextInput/Select/Toggle Not Found

**Error message:**
```
Class "Filament\Schemas\Components\TextInput" not found
Class "Filament\Schemas\Components\Select" not found
```

**Cause:** Using Schemas\Components for form fields

**Fix:**
```php
// ❌ WRONG
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Select;

// ✅ CORRECT
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\CheckboxList;
```

**Rule:** Form fields → `Filament\Forms\Components\*`

### Error 3: Get Utility Type Mismatch

**Error message:**
```
Argument #1 ($get) must be of type Filament\Forms\Get, 
Filament\Schemas\Components\Utilities\Get given
```

**Cause:** Wrong Get import for Schema closures

**Fix:**
```php
// ❌ WRONG
use Filament\Forms\Get;

// ✅ CORRECT
use Filament\Schemas\Components\Utilities\Get;

// Usage in Schema closures
->visible(fn (Get $get) => $get('type') === 'special')
->helperText(fn (Get $get) => self::getDescription($get('type')))
->schema(fn (Get $get): array => self::getFields($get('type')))
```

**Rule:** Get in Schema context → `Filament\Schemas\Components\Utilities\Get`

### Error 4: Form Type Mismatch in Settings Page

**Error message:**
```
Argument #1 ($form) must be of type Filament\Forms\Form, 
Filament\Schemas\Schema given
```

**Cause:** Using Form type hint instead of Schema

**Fix:**
```php
// ❌ WRONG
use Filament\Forms\Form;

public function form(Form $form): Form
{
    return $form->schema([...]);
}

// ✅ CORRECT
use Filament\Schemas\Schema;

public function form(Schema $schema): Schema
{
    return $schema->schema([...]);
}
```

**Rule:** Settings pages use `Schema` not `Form`

### Error 5: HasFormActions Trait Not Found

**Error message:**
```
Trait "Filament\Pages\Concerns\HasFormActions" not found
```

**Cause:** Trying to use non-existent trait in custom Page

**Fix:**
```php
// ❌ WRONG
use Filament\Pages\Concerns\HasFormActions;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;
    use HasFormActions;  // ← Doesn't exist!
}

// ✅ CORRECT
class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;  // ← Only this trait

    // Add button manually in view blade
}
```

**View blade:**
```blade
<x-filament-panels::page>
<form wire:submit="save">
    {{ $this->form }}
    
    <div class="mt-6">
        <x-filament::button type="submit" size="lg">
            Lưu cài đặt
        </x-filament::button>
    </div>
</form>
</x-filament-panels::page>
```

**Rule:** Custom pages don't have HasFormActions - use button in blade

### Error 6: GridDirection Not Found

**Error message:**
```
Class "Filament\Forms\Components\GridDirection" not found
```

**Cause:** Wrong namespace for enum

**Fix:**
```php
// ❌ WRONG
use Filament\Forms\Components\GridDirection;
use Filament\Schemas\Components\GridDirection;

// ✅ CORRECT
use Filament\Support\Enums\GridDirection;

// Usage
CheckboxList::make('items')
    ->columns(3)
    ->gridDirection(GridDirection::Column)
```

**Rule:** Enums → `Filament\Support\Enums\*`

### Error 7: Actions Namespace Wrong

**Error message:**
```
Class "Filament\Tables\Actions\EditAction" not found
```

**Cause:** Using Tables\Actions instead of Filament\Actions

**Fix:**
```php
// ❌ WRONG (dự án này KHÔNG dùng)
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

// ✅ CORRECT (dự án này dùng)
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\CreateAction;
```

**Rule:** Actions → `Filament\Actions\*` (NOT Tables\Actions)

## 🔍 Debugging Workflow

### Step 1: Identify Error Type

Read error message carefully:
- "Class not found" → Namespace issue
- "Argument must be of type" → Type mismatch
- "Trait not found" → Using non-existent trait

### Step 2: Check Imports

Look at `use` statements at top of file:
```php
// ✅ Check these are CORRECT
use Filament\Schemas\Components\Tabs;          // Layout
use Filament\Schemas\Components\Grid;          // Layout
use Filament\Forms\Components\TextInput;       // Form field
use Filament\Forms\Components\Select;          // Form field
use Filament\Schemas\Components\Utilities\Get; // Get utility
use Filament\Support\Enums\GridDirection;      // Enum
use Filament\Actions\EditAction;               // Action
```

### Step 3: Check Method Signatures

For Settings pages:
```php
// ✅ CORRECT signature
public function form(Schema $schema): Schema
{
    return $schema->schema([...]);
}
```

For Resources:
```php
// ✅ CORRECT signature
public static function form(Schema $schema): Schema
{
    return $schema->schema([...]);
}
```

### Step 4: Check Trait Usage

For custom Pages:
```php
// ✅ CORRECT traits
class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;  // ← Only this
    // NO HasFormActions!
}
```

### Step 5: Test Fix

Clear cache and test:
```bash
php artisan filament:clear-cache
php artisan optimize:clear
```

Reload page and verify error gone.

## Quick Reference: Namespace Map

| Component Type | Namespace | Examples |
|----------------|-----------|----------|
| **Layout** | `Schemas\Components\*` | Tabs, Grid, Section, Fieldset, Group |
| **Form Fields** | `Forms\Components\*` | TextInput, Select, Toggle, Textarea, FileUpload, CheckboxList |
| **Get Utility** | `Schemas\Components\Utilities\Get` | fn (Get $get) => ... |
| **Enums** | `Support\Enums\*` | GridDirection |
| **Actions** | `Actions\*` | EditAction, DeleteAction, CreateAction |
| **Schema Class** | `Schemas\Schema` | public function form(Schema $schema) |

## Complete Import Template

Copy this as starting point for new files:

```php
<?php

namespace App\Filament\Resources\YourModel;

use App\Models\YourModel;
use Filament\Resources\Resource;

// Layout Components (Schemas)
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;

// Form Fields (Forms)
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;

// Get Utility (Schemas\Utilities)
use Filament\Schemas\Components\Utilities\Get;

// Table Components
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;

// Actions (Filament\Actions)
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\CreateAction;

// Schema Class
use Filament\Schemas\Schema;

// Support Enums
use Filament\Support\Enums\GridDirection;

class YourModelResource extends Resource
{
    protected static ?string $model = YourModel::class;
    
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make()->tabs([
                Tabs\Tab::make('Tab 1')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name'),
                        Select::make('category'),
                    ]),
                ]),
            ]),
        ]);
    }
}
```

## Prevention Checklist

Before saving any Filament file:

- [ ] All layout components from `Schemas\Components\*`
- [ ] All form fields from `Forms\Components\*`
- [ ] Get utility from `Schemas\Components\Utilities\Get`
- [ ] Enums from `Support\Enums\*`
- [ ] Actions from `Actions\*` (not Tables\Actions)
- [ ] Method signature: `form(Schema $schema): Schema`
- [ ] Only `InteractsWithForms` trait in Pages
- [ ] No `HasFormActions` trait anywhere

## Common Gotchas

1. **Tabs is NOT a form field** → Use Schemas\Components\Tabs
2. **TextInput is NOT a layout** → Use Forms\Components\TextInput
3. **Get has two versions** → Use Utilities\Get for Schema closures
4. **Settings pages use Schema** → Not Form
5. **No HasFormActions trait** → Use button in blade
6. **Actions namespace unique** → Filament\Actions not Tables\Actions

## Supplementary Resources

**Full Filament rules:**
```
read .claude/skills/filament-rules/SKILL.md
```

**Resource generation:**
```
read .claude/skills/filament-resource-generator/SKILL.md
```

**Image management:**
```
read .claude/skills/image-management/SKILL.md
```

## Quick Fix Commands

```bash
# Clear all caches
php artisan optimize:clear

# Clear Filament cache
php artisan filament:clear-cache

# Rebuild autoload
composer dump-autoload
```

Follow these patterns → No more namespace errors! 🐛✅
