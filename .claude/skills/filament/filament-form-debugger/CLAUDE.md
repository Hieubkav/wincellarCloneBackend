# Filament Form Debugger - Complete Error Catalog

Comprehensive troubleshooting guide for Filament 4.x namespace issues, type mismatches, and common errors in Laravel 12 project.

## Complete Error Catalog

### Category 1: Namespace Errors

#### Error 1.1: Layout Components in Wrong Namespace

**Symptoms:**
```
Class "Filament\Forms\Components\Tabs" not found
Class "Filament\Forms\Components\Grid" not found  
Class "Filament\Forms\Components\Section" not found
Class "Filament\Forms\Components\Fieldset" not found
```

**Root cause:** Layout components belong to Schemas namespace, not Forms

**Complete fix:**
```php
// ❌ WRONG - Will cause "not found" errors
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;

// ✅ CORRECT - Use Schemas for layouts
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
```

#### Error 1.2: Form Fields in Wrong Namespace

**Symptoms:**
```
Class "Filament\Schemas\Components\TextInput" not found
Class "Filament\Schemas\Components\Select" not found
Class "Filament\Schemas\Components\Toggle" not found
```

**Root cause:** Form input fields belong to Forms namespace, not Schemas

**Complete fix:**
```php
// ❌ WRONG
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Toggle;

// ✅ CORRECT  
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\MarkdownEditor;
```

#### Error 1.3: Get Utility Type Mismatch

**Symptoms:**
```
Argument #1 ($get) must be of type Filament\Forms\Get,
Filament\Schemas\Components\Utilities\Get given

or

Argument #1 ($get) must be of type Filament\Schemas\Components\Utilities\Get,
Filament\Forms\Get given
```

**Root cause:** Different contexts use different Get classes

**Rule:**
- Schema closures → `Filament\Schemas\Components\Utilities\Get`
- Form component closures → `Filament\Forms\Get`

**Complete fix:**
```php
// For Schema context (Resources, Settings pages)
use Filament\Schemas\Components\Utilities\Get;

TextInput::make('field')
    ->visible(fn (Get $get) => $get('type') === 'special')
    ->schema(fn (Get $get): array => self::getFields($get('category')))

// For pure Form context (rare in this project)
use Filament\Forms\Get;
```

### Category 2: Type Mismatch Errors

#### Error 2.1: Form vs Schema Type

**Symptoms:**
```
Argument #1 ($form) must be of type Filament\Forms\Form,
Filament\Schemas\Schema given
```

**Root cause:** Settings pages and Resources use Schema, not Form

**Complete fix:**
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

**In Resources:**
```php
public static function form(Schema $schema): Schema
{
    return $schema->schema([...]);
}
```

**In Settings Pages:**
```php
public function form(Schema $schema): Schema
{
    return $schema->schema([...]);
}
```

### Category 3: Trait Errors

#### Error 3.1: HasFormActions Trait Not Found

**Symptoms:**
```
Trait "Filament\Pages\Concerns\HasFormActions" not found
```

**Root cause:** This trait doesn't exist in Filament 4.x

**Complete fix:**

**In Page class:**
```php
<?php

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class SettingsPage extends Page implements HasForms
{
    // ✅ CORRECT - Only this trait
    use InteractsWithForms;
    
    // ❌ DO NOT ADD HasFormActions - it doesn't exist!
    
    protected static string $view = 'filament.pages.settings';
    
    public $settings = [];
    
    public function mount(): void
    {
        $this->form->fill($this->getSettings());
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        $this->saveSettings($data);
        
        Notification::make()
            ->title('Đã lưu cài đặt')
            ->success()
            ->send();
    }
}
```

**In blade view:**
```blade
<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        
        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" size="lg">
                <x-filament::icon icon="heroicon-m-check" class="w-5 h-5 mr-2" />
                Lưu cài đặt
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
```

### Category 4: Enum Namespace Errors

#### Error 4.1: GridDirection Not Found

**Symptoms:**
```
Class "Filament\Forms\Components\GridDirection" not found
Class "Filament\Schemas\Components\GridDirection" not found
```

**Root cause:** Enums are in Support namespace

**Complete fix:**
```php
// ❌ WRONG
use Filament\Forms\Components\GridDirection;
use Filament\Schemas\Components\GridDirection;

// ✅ CORRECT
use Filament\Support\Enums\GridDirection;

// Usage
CheckboxList::make('permissions')
    ->columns(3)
    ->gridDirection(GridDirection::Column)
```

**All enums locations:**
```php
use Filament\Support\Enums\GridDirection;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\VerticalAlignment;
```

### Category 5: Actions Namespace Errors

#### Error 5.1: Actions in Wrong Namespace

**Symptoms:**
```
Class "Filament\Tables\Actions\EditAction" not found
Class "Filament\Tables\Actions\DeleteAction" not found
```

**Root cause:** Project uses Actions namespace, not Tables\Actions

**Complete fix:**
```php
// ❌ WRONG (Standard Filament - but NOT this project)
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

// ✅ CORRECT (This project's custom namespace)
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
```

**Usage in table:**
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->recordActions([
            ViewAction::make()->iconButton(),
            EditAction::make()->iconButton(),
            DeleteAction::make()->iconButton(),
        ])
        ->bulkActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
}
```

---

## Systematic Debugging Process

### Step 1: Read Error Message Carefully

Identify error category:
- "Class not found" → Namespace issue (Category 1)
- "Argument must be of type" → Type mismatch (Category 2)
- "Trait not found" → Trait usage error (Category 3)
- Unexpected behavior → Logic or enum issue (Category 4-5)

### Step 2: Locate Problem File

Error stack trace shows file path:
```
/app/Filament/Resources/Products/ProductResource.php:15
```

Open that file and go to line number.

### Step 3: Check Import Statements

Review ALL `use` statements at top of file:
```php
// Check each import matches correct namespace:
// - Tabs, Grid, Section → Schemas\Components\
// - TextInput, Select → Forms\Components\
// - Get → Schemas\Components\Utilities\
// - Actions → Filament\Actions\
```

### Step 4: Check Method Signatures

For Resource classes:
```php
// ✅ Must be Schema
public static function form(Schema $schema): Schema
```

For Page classes:
```php
// ✅ Must be Schema
public function form(Schema $schema): Schema
```

### Step 5: Check Closure Type Hints

```php
// ✅ Get must be from Utilities
->visible(fn (Get $get) => $get('active'))
```

### Step 6: Apply Fix

Based on error category, apply appropriate fix from catalog above.

### Step 7: Clear Caches

```bash
php artisan optimize:clear
php artisan filament:clear-cache
composer dump-autoload
```

### Step 8: Test

Reload admin panel and verify:
- Page loads without errors
- Form displays correctly
- Actions work as expected

---

## Complete Namespace Reference

### Layout Components (Schemas\Components)

```php
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Split;
```

### Form Fields (Forms\Components)

**Text inputs:**
```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\MarkdownEditor;
```

**Selections:**
```php
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Toggle;
```

**Dates & Times:**
```php
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TimePicker;
```

**Files:**
```php
use Filament\Forms\Components\FileUpload;
```

**Complex:**
```php
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\ColorPicker;
```

### Utilities

```php
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
```

### Actions

```php
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
```

### Enums

```php
use Filament\Support\Enums\GridDirection;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\VerticalAlignment;
```

---

## Prevention Checklist

Before committing any Filament code:

**Imports:**
- [ ] Tabs/Grid/Section from `Schemas\Components\*`
- [ ] TextInput/Select/Toggle from `Forms\Components\*`
- [ ] Get from `Schemas\Components\Utilities\Get`
- [ ] GridDirection from `Support\Enums\*`
- [ ] Actions from `Filament\Actions\*`

**Method signatures:**
- [ ] `form(Schema $schema): Schema` (not Form)
- [ ] Return type is `Schema` (not Form)

**Traits:**
- [ ] Only `InteractsWithForms` in Pages
- [ ] No `HasFormActions` anywhere

**Closures:**
- [ ] Type hint `Get` in visible/schema closures
- [ ] Get is from `Utilities` namespace

---

## Quick Command Reference

```bash
# Clear all caches (do this after fixing imports)
php artisan optimize:clear

# Clear Filament cache specifically
php artisan filament:clear-cache

# Rebuild composer autoload
composer dump-autoload

# Clear config cache
php artisan config:clear

# Clear view cache
php artisan view:clear

# Clear route cache
php artisan route:clear

# Nuclear option (clears everything)
php artisan optimize:clear && composer dump-autoload
```

---

## Source-Based Insights

**All insights extracted from Filament 4.x source code analysis.**

### Understanding Component Hierarchy

From Filament source code structure:

**Schemas Package (`packages/schemas/src/Components/`):**
- Base: `Component.php` - All schema components extend this
- Layout components: Tabs, Grid, Section, Fieldset, Group, Flex
- Display components: Text, Icon, Image, Html, EmptyState
- Special: Wizard, Builder (form builder)
- Utilities: Get class for closures

**Forms Package (`packages/forms/src/Components/`):**
- Base: `Field.php` - All form fields extend this
- Input fields: TextInput, Textarea, Select, etc.
- File handling: FileUpload, BaseFileUpload
- Complex: Repeater, Builder, KeyValue
- Each component has its own directory with related classes

**Key insight:** The separation is intentional:
- **Schemas** = Structure/Layout (how things are arranged)
- **Forms** = Data input (what users fill in)

### Common Concerns/Traits

From source analysis, components use these traits:

**Section (Schemas):**
```php
use CanBeCollapsed;      // ->collapsible(), ->collapsed()
use CanBeCompact;        // ->compact()
use HasDescription;      // ->description()
use HasHeaderActions;    // ->headerActions()
use HasFooterActions;    // ->footerActions()
use HasHeading;          // ->heading()
use HasIcon;            // ->icon()
use HasIconColor;       // ->iconColor()
```

**TextInput (Forms):**
```php
use CanBeReadOnly;      // ->readOnly()
use HasAffixes;         // ->prefix(), ->suffix()
use HasPlaceholder;     // ->placeholder()
use HasExtraInputAttributes; // ->extraInputAttributes()
```

**Select (Forms):**
```php
use CanBeSearchable;    // ->searchable()
use CanBePreloaded;     // ->preload()
use HasOptions;         // ->options()
use HasPlaceholder;     // ->placeholder()
```

### Error Pattern Recognition

#### Pattern 1: Missing Imports After Copy-Paste

**Symptom:** Works in one file, breaks in another

**Cause:** Copied code but not imports

**Fix:**
1. Find ALL `use` statements in working file
2. Copy ALL imports (not just the ones you think you need)
3. Let IDE remove unused imports later

#### Pattern 2: Autocomplete Suggests Wrong Namespace

**Symptom:** IDE autocompletes with wrong namespace

**Cause:** IDE doesn't know project convention

**Prevention:**
- Always verify imported namespace
- Layout? → Must be `Schemas\Components\`
- Field? → Must be `Forms\Components\`
- Don't trust autocomplete blindly

#### Pattern 3: Mixed V3 and V4 Code

**Symptom:** Code from tutorial/documentation doesn't work

**Cause:** Mixing Filament v3 patterns with v4 structure

**V3 vs V4 differences:**
```php
// V3 (DON'T USE)
use Filament\Forms\Form;
public function form(Form $form): Form

// V4 (USE THIS)
use Filament\Schemas\Schema;
public function form(Schema $schema): Schema
```

### Advanced Debugging Techniques

#### Technique 1: Check Filament Documentation

When unsure about a component:
1. Check component class namespace in your IDE
2. Hover over the class to see its full path
3. Verify it matches the correct namespace:
   - Layout? → `Filament\Schemas\Components\`
   - Field? → `Filament\Forms\Components\`
   - Action? → `Filament\Actions\`
4. Consult official Filament docs at filamentphp.com

#### Technique 2: Trace Error Stack

Read error from bottom to top:
```
1. YourResource.php:25 → Your code (fix here)
   ↓
2. Schema.php:150 → Schema trying to render
   ↓
3. Component.php:88 → Component not found
```

Fix at level 1 (your code), not framework code.

#### Technique 3: Binary Search Debugging

If complex form breaks:
1. Comment out HALF of schema
2. Test - still broken?
3. Broken half? Comment out HALF of that
4. Keep halving until you find the problematic component
5. Fix that component's imports

#### Technique 4: Fresh File Comparison

Create minimal test:
```php
// TestResource.php
public static function form(Schema $schema): Schema
{
    return $schema->schema([
        Tabs::make()->tabs([
            Tabs\Tab::make('Test')->schema([
                TextInput::make('test'),
            ]),
        ]),
    ]);
}
```

If this works, compare imports with broken file.

### IDE Configuration Tips

**VS Code settings.json:**
```json
{
    "php.suggest.basic": false,
    "php.suggest.useAutocompletionNamespaces": false,
    "intelephense.completion.insertUseDeclaration": false,
    // Force manual import verification
}
```

**PHPStorm:**
```
Settings → PHP → Code Style → Imports
☐ Add use statements automatically
☑ Ask before adding use statements
```

### Real-World Error Examples

#### Example 1: After Upgrade

```
Error: Class "Filament\Forms\Form" not found
```

**Analysis:**
- Project upgraded from v3 to v4
- Old code still uses `Form`
- Search all files: `findstr /s "use Filament\\Forms\\Form" *.php`
- Replace with: `use Filament\Schemas\Schema`

#### Example 2: Third-Party Package

```
Error: Argument #1 must be of type Schema, Form given
```

**Analysis:**
- Using third-party Filament plugin
- Plugin still uses v3 patterns
- **Solution:** Update plugin or fork and fix namespaces

#### Example 3: Custom Component

```
Error: Class "CustomSelect" not found
```

**Check:**
1. Is CustomSelect in correct namespace?
2. Does it extend correct base class?
3. Is it registered in ServiceProvider?

**Fix:**
```php
// app/Forms/Components/CustomSelect.php
namespace App\Forms\Components;

use Filament\Forms\Components\Select;

class CustomSelect extends Select
{
    // Custom logic
}

// Usage
use App\Forms\Components\CustomSelect;

CustomSelect::make('field')...
```

---

## Quick Reference Card

Print this for your desk:

```
┌─────────────────────────────────────────────────┐
│          FILAMENT 4.X NAMESPACE GUIDE           │
├─────────────────────────────────────────────────┤
│ LAYOUT (Structure)                              │
│   Filament\Schemas\Components\                  │
│   - Tabs, Grid, Section, Fieldset, Group        │
│                                                 │
│ FIELDS (Input)                                  │
│   Filament\Forms\Components\                    │
│   - TextInput, Select, Toggle, etc.             │
│                                                 │
│ UTILITIES                                       │
│   Filament\Schemas\Components\Utilities\Get     │
│                                                 │
│ ACTIONS                                         │
│   Filament\Actions\                             │
│   - EditAction, DeleteAction, etc.              │
│                                                 │
│ ENUMS                                           │
│   Filament\Support\Enums\                       │
│   - GridDirection, Alignment, etc.              │
│                                                 │
│ SCHEMA TYPE                                     │
│   form(Schema $schema): Schema                  │
│   NOT form(Form $form): Form                    │
└─────────────────────────────────────────────────┘
```

---

For related documentation:
- Filament standards: `read .claude/skills/filament-rules/SKILL.md`
- Resource generation: `read .claude/skills/filament-resource-generator/SKILL.md`
