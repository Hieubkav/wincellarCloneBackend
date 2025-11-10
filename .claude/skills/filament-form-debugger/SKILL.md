---
name: filament-form-debugger
description: Diagnose and fix common Filament 4.x form errors - namespace issues (Tabs/Grid/Get), type mismatch, trait errors. USE WHEN encountering 'Class not found', 'Argument must be of type', namespace errors, or Filament compilation/runtime errors.
---

# Filament Form Debugger - Quick Fixes

Fix common Filament 4.x namespace and type errors in this Laravel 12 project.

## When to Use This Skill

- Error: "Class ... not found"
- Error: "Argument must be of type ..."
- Error: "Trait not found"
- Namespace-related Filament errors
- Form not displaying correctly

---

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

---

## Quick Namespace Map

| Type | Namespace | Examples |
|------|-----------|----------|
| **Layout** | `Schemas\Components\` | Tabs, Grid, Section |
| **Fields** | `Forms\Components\` | TextInput, Select, Toggle |
| **Get** | `Schemas\...Utilities\Get` | fn (Get $get) => |
| **Schema** | `Schemas\Schema` | form(Schema $schema) |
| **Actions** | `Actions\` | EditAction, DeleteAction |
| **Enums** | `Support\Enums\` | GridDirection |

---

## Quick Debug Process

1. **Read error** → Identify type (namespace/type/trait)
2. **Check imports** → Verify `use` statements
3. **Check signature** → `form(Schema $schema): Schema`
4. **Apply fix** → Use correct namespace
5. **Clear cache** → `php artisan optimize:clear`
6. **Test** → Reload page

---

## Complete Import Template

```php
<?php

// Layout (Schemas)
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

// Fields (Forms)
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

// Utilities
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

// Actions
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

// Enums
use Filament\Support\Enums\GridDirection;
```

---

## Prevention Checklist

Before saving:
- [ ] Tabs/Grid/Section from `Schemas\Components`
- [ ] TextInput/Select from `Forms\Components`
- [ ] Get from `Utilities\Get`
- [ ] Method signature: `form(Schema $schema)`
- [ ] Only `InteractsWithForms` trait
- [ ] Actions from `Filament\Actions`

---

## Quick Commands

```bash
# Clear caches after fixing
php artisan optimize:clear
php artisan filament:clear-cache

# Rebuild autoload
composer dump-autoload
```

---

## Complete Error Catalog

For full error list, detailed troubleshooting, and advanced fixes:

`read .claude/skills/filament-form-debugger/CLAUDE.md`

**Related skills:**
- Filament standards: `read .claude/skills/filament-rules/SKILL.md`
- Resource generation: `read .claude/skills/filament-resource-generator/SKILL.md`
