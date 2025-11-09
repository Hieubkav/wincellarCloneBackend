# 📚 Documentation Index

Welcome to the project documentation! Tài liệu được organize theo topics và skill levels.

---

## 🎯 Quick Navigation

### Filament Admin Panel
1. **[filament/FILAMENT_RULES.md](filament/FILAMENT_RULES.md)** 📖 ⭐ **START HERE**
   - Coding standards & best practices
   - UI/UX guidelines
   - Quy tắc bắt buộc khi làm việc với Filament 4.x

2. **[filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md](filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md)** 🔥 **ADVANCED**
   - Deep dive vào `.allowHtml()` technique
   - Architecture & source code analysis
   - 6+ advanced patterns với real-world examples
   - Security, performance, best practices
   - **Level**: Intermediate to Advanced

3. **[filament/FILAMENT_SELECT_WITH_IMAGES.md](filament/FILAMENT_SELECT_WITH_IMAGES.md)** 🖼️ **PRACTICAL**
   - Hiển thị image preview trong Select dropdown
   - Step-by-step implementation guide
   - 4 use cases cụ thể (Image, Product, User, Category)
   - **Level**: Beginner to Intermediate

4. **[filament/COMPONENT_SETUP_GUIDE.md](filament/COMPONENT_SETUP_GUIDE.md)** 🧩 **PATTERN**
   - Dynamic Component Management pattern
   - Enum-based type system + JSON config
   - 8 component types examples (Hero, Banner, Footer...)
   - Common pitfalls & lessons learned
   - **Level**: Intermediate

### Image Management
5. **[IMAGE_MANAGEMENT.md](IMAGE_MANAGEMENT.md)** 📸
   - Hệ thống quản lý ảnh polymorphic
   - Upload, resize, WebP conversion
   - RelationManager patterns
   - **Level**: Intermediate

6. **[IMAGE_DELETE_PROTECTION.md](IMAGE_DELETE_PROTECTION.md)** 🛡️
   - Cascade delete & soft delete strategies
   - Reference protection mechanisms
   - **Level**: Intermediate

### Infrastructure
7. **[spatie_backup.md](spatie_backup.md)** 💾
   - Backup & restore configuration
   - Scheduled backups
   - **Level**: Beginner

---

## 📊 Documentation Structure

```
docs/
├── README.md (you are here)
├── filament/                            🎨 Filament Admin Panel docs
│   ├── FILAMENT_RULES.md                ⭐ Main reference
│   ├── FILAMENT_ALLOWHTML_DEEP_DIVE.md  🔥 Advanced technique
│   ├── FILAMENT_SELECT_WITH_IMAGES.md   🖼️ Practical guide
│   ├── COMPONENT_SETUP_GUIDE.md         🧩 Reusable pattern
│   └── CHANGELOG_ALLOWHTML.md           📝 AllowHTML changelog
├── IMAGE_MANAGEMENT.md                  📸 Media handling
├── IMAGE_DELETE_PROTECTION.md           🛡️ Data integrity
├── spatie_backup.md                     💾 Backup system
└── api/
    └── v1/
        ├── README.md                    API overview
        ├── home.md                      Home endpoints
        ├── products.md                  Product endpoints
        └── articles.md                  Article endpoints
```

---

## 🎓 Learning Path

### For New Developers:
1. ✅ Read **filament/FILAMENT_RULES.md** first
2. ✅ Try **filament/FILAMENT_SELECT_WITH_IMAGES.md** - quick win
3. ✅ Study **filament/COMPONENT_SETUP_GUIDE.md** - reusable pattern
4. ✅ Deep dive **filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md** - level up

### For Filament Masters:
1. 🔥 **filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md** - unlock new capabilities
2. 🧩 **filament/COMPONENT_SETUP_GUIDE.md** - apply to new domains
3. 📸 **IMAGE_MANAGEMENT.md** - polymorphic patterns

---

## 🔍 Find Documentation by Topic

### UI/UX Enhancement
- [filament/FILAMENT_SELECT_WITH_IMAGES.md](filament/FILAMENT_SELECT_WITH_IMAGES.md) - Dropdowns với thumbnails
- [filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md](filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md) - Rich content trong forms

### Architecture Patterns
- [filament/COMPONENT_SETUP_GUIDE.md](filament/COMPONENT_SETUP_GUIDE.md) - Dynamic components
- [IMAGE_MANAGEMENT.md](IMAGE_MANAGEMENT.md) - Polymorphic relations

### Security
- [filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md](filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md#-security-xss-prevention-strategy) - XSS prevention
- [IMAGE_DELETE_PROTECTION.md](IMAGE_DELETE_PROTECTION.md) - Cascade protection

### Performance
- [filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md](filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md#-performance-optimization) - Caching & lazy loading
- [filament/FILAMENT_SELECT_WITH_IMAGES.md](filament/FILAMENT_SELECT_WITH_IMAGES.md#-performance-considerations) - Limit & eager loading

---

## 🏆 Featured Techniques

### ⭐ `.allowHtml()` - Rich UI in Forms
Transform boring dropdowns into rich interactive components!

**Quick example:**
```php
Select::make('image_id')
    ->options(fn () => Image::get()->mapWithKeys(fn ($img) => [
        $img->id => '<img src="'.$img->url.'" style="width:50px"/> '.$img->alt
    ]))
    ->allowHtml()
    ->searchable()
```

**Learn more:**
- Quick start: [filament/FILAMENT_SELECT_WITH_IMAGES.md](filament/FILAMENT_SELECT_WITH_IMAGES.md)
- Deep dive: [filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md](filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md)

---

### 🧩 Dynamic Component Management
Build admin panels for dynamic content without JSON editing!

**Pattern:**
1. Enum cho component types
2. JSON config storage  
3. Dynamic form builder
4. API transformers

**Learn more:** [filament/COMPONENT_SETUP_GUIDE.md](filament/COMPONENT_SETUP_GUIDE.md)

---

## 📖 API Documentation

### Public API (v1)
Located in: `docs/api/v1/`

- **[README.md](api/v1/README.md)** - API overview & authentication
- **[home.md](api/v1/home.md)** - Home page components
- **[products.md](api/v1/products.md)** - Product catalog
- **[articles.md](api/v1/articles.md)** - Blog/articles

---

## 💡 Contributing to Docs

### When to add documentation:
- ✅ New pattern discovered
- ✅ Common pitfall encountered
- ✅ Non-obvious technique used
- ✅ Complex architecture decisions

### Documentation template:
```markdown
# Title

> One-line description

## Problem
[What problem does this solve?]

## Solution
[How to implement]

## Example
[Real code example]

## Common Mistakes
[What to avoid]

## References
[Related docs & external links]
```

---

## 🔗 External Resources

### Filament
- Official docs: https://filamentphp.com/docs/4.x
- Source code: `vendor/filament/`
- Community tricks: https://filamentphp.com/community/tricks

### Laravel
- Laravel docs: https://laravel.com/docs
- Eloquent ORM: https://laravel.com/docs/eloquent

### Frontend
- Livewire: https://livewire.laravel.com
- Alpine.js: https://alpinejs.dev
- Tailwind CSS: https://tailwindcss.com

---

## 📞 Need Help?

1. 🔍 Search in docs index (this file)
2. 📖 Check relevant .md file
3. 💻 Look at source code examples in `app/Filament/`
4. 🤔 Ask team members

---

## 📈 Recent Updates

- **2025-11-09**: Reorganized docs - Moved Filament files to `/docs/filament/` directory
- **2025-11-09**: Added `filament/FILAMENT_ALLOWHTML_DEEP_DIVE.md` - Advanced `.allowHtml()` techniques
- **2025-11-09**: Added `filament/FILAMENT_SELECT_WITH_IMAGES.md` - Image preview in dropdowns
- **2025-11-09**: Updated `filament/COMPONENT_SETUP_GUIDE.md` - Added allowHtml reference

---

Happy coding! 🚀
