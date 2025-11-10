# Tests Directory

## 📋 Purpose

This directory contains all test files for the application.

## 🚨 Important Rules

### ⚠️ Test Files Location

**ALL test/debug files MUST be in this directory or subdirectories.**

```
❌ WRONG - Root directory
/check_something.php
/test_feature.php
/debug_issue.php

✅ CORRECT - Tests directory
/tests/Feature/SomethingTest.php
/tests/Unit/FeatureTest.php
/tests/Debug/IssueDebugTest.php
```

### 🗑️ Cleanup After Use

**ALWAYS delete temporary test files after use!**

```bash
# Run cleanup script
.\tests\cleanup-root-tests.ps1

# Or manual cleanup
Remove-Item test_*.php, check_*.php, debug_*.php, fix_*.php
```

## 📁 Directory Structure

```
tests/
├── Feature/          # Feature tests (API, Controllers)
├── Unit/             # Unit tests (Models, Services)
├── Debug/            # Debug/investigation scripts (delete after use!)
├── cleanup-root-tests.ps1  # Cleanup script
└── README.md         # This file
```

## 🧪 Running Tests

### PHPUnit Tests
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ProductTest.php

# Run with coverage
php artisan test --coverage

# Run specific test method
php artisan test --filter testProductCreation
```

### Debug Scripts (Temporary)
```bash
# Create temporary debug script
# tests/Debug/debug_issue.php

# Run it
php tests/Debug/debug_issue.php

# DELETE after debugging!
rm tests/Debug/debug_issue.php
```

## 📝 Writing Tests

### Feature Test Example
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        $response = $this->get('/api/v1/san-pham');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'meta' => ['pagination']
        ]);
    }
}
```

### Unit Test Example
```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;

class ProductModelTest extends TestCase
{
    public function test_product_has_cover_image(): void
    {
        $product = Product::factory()->create();
        
        $this->assertNotNull($product->cover_image_url);
    }
}
```

### Debug Script Example (Temporary!)
```php
<?php
// tests/Debug/check_images.php

require __DIR__.'/../../vendor/autoload.php';
$app = require_once __DIR__.'/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Debug code here
$images = \App\Models\Image::whereNull('deleted_at')->count();
echo "Total images: {$images}\n";

// DELETE THIS FILE AFTER DEBUGGING!
```

## 🎯 Best Practices

### DO ✅
- Create tests in appropriate subdirectory
- Use PHPUnit for automated tests
- Delete debug scripts immediately after use
- Run cleanup script regularly
- Document findings in /docs if important

### DON'T ❌
- Put test files in project root
- Commit temporary debug scripts
- Leave test files after debugging
- Use production database for tests
- Skip test cleanup

## 🧹 Cleanup Commands

### PowerShell
```powershell
# Run cleanup script
.\tests\cleanup-root-tests.ps1

# Manual cleanup from root
Get-ChildItem -Filter "*test*.php","*check*.php","*debug*.php","*fix*.php" | 
    Where-Object { $_.DirectoryName -notmatch "\\tests\\?" } | 
    Remove-Item -Force
```

### Bash
```bash
# Remove all test files from root
rm -f test_*.php check_*.php debug_*.php fix_*.php
```

## 📚 Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- Project Guidelines: `/AGENTS.md`

---

**Remember:** Clean tests = Clean codebase! 🧹✨
