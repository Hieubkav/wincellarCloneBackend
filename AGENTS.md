Trả lời bằng tiếng việt

## 📁 Project Structure
- Đừng để logic hoặc file quá 500 dòng
- Hãy gọi các file để chia logic và kế thừa hợp lý
- **Tham khảo**: `PLAN.md` để hiểu dự án làm gì, chức năng gì

## 🎨 Filament 4.x Rules
**⚠️ QUAN TRỌNG**: Khi làm việc với Filament, LUÔN tham khảo:
- **📖 File rule chính**: `docs/filament/FILAMENT_RULES.md` - Chi tiết đầy đủ về:
  - List/Create/Edit/RelationManager pages
  - Observer patterns (SEO, alt, order tự sinh)
  - Reorderable cho table có order column
  - Storage & File upload (WebP conversion)
  - Common mistakes & solutions
- **🎨 Filament docs folder**: `docs/filament/` - Tất cả tài liệu Filament:
  - `FILAMENT_RULES.md` - Main reference
  - `FILAMENT_ALLOWHTML_DEEP_DIVE.md` - Advanced techniques
  - `FILAMENT_SELECT_WITH_IMAGES.md` - Image preview patterns
  - `COMPONENT_SETUP_GUIDE.md` - Dynamic component management
- **🖼️ Image Management**: `docs/IMAGE_MANAGEMENT.md` - Hệ thống quản lý ảnh:
  - Polymorphic images table (single source of truth)
  - CheckboxList cho image picker
  - WebP conversion & optimization
  - Pattern cho Products/Articles/Settings
- **📚 Source code**: `vendor/filament/` - Đọc để hiểu sâu
- **🌐 Docs**: https://filamentphp.com/docs/4.x

### Quick Summary:
- ✅ Mọi resource quan trọng: Navigation badge (số lượng)
- ✅ Mọi cột: `->sortable()`, Có order → `->reorderable()`
- ✅ Mọi list: Bulk delete, Mọi record: Edit + Delete
- ✅ SEO fields: Tự sinh bằng Observer, ẨN khỏi form
- ✅ Image: Observer auto alt/order/delete + WebP 85%
- ✅ Eager load: `->modifyQueryUsing()`

### ❌ KHÔNG dùng Alpine.js trong dự án này
**⚠️ CRITICAL**: Filament đã có Alpine.js tích hợp, ĐỪNG viết custom Alpine code:
- ❌ **ĐỪNG** dùng `x-data`, `x-model`, `x-show`, `x-on:click`
- ❌ **ĐỪNG** tạo custom ViewField với Alpine.js
- ✅ **LUÔN** dùng Filament components có sẵn (CheckboxList, Select, Toggle...)
- ✅ **NẾU CẦN** JavaScript: Dùng vanilla JS với addEventListener
- ✅ **NẾU CẦN** interactivity: Dùng Livewire wire:model, wire:click

**Lý do**:
1. Filament components đã có Alpine.js binding sẵn
2. Custom Alpine code dễ conflict với Filament internals
3. Dùng built-in components → UI consistent, less bugs
4. ViewField chỉ dùng cho read-only displays, KHÔNG dùng cho forms

**Examples:**
```php
// ❌ SAI - Custom ViewField với Alpine.js
ViewField::make('images')
    ->view('filament.forms.custom-picker')  // có x-data, x-model

// ✅ ĐÚNG - Dùng CheckboxList có sẵn
CheckboxList::make('images')
    ->options($options)
    ->searchable()
    ->bulkToggleable()
    ->allowHtml()  // cho preview ảnh
```

### 🔄 Cập nhật Rules khi cần:
**Nếu gặp lỗi/hiểu sai về Filament**:
1. Research đúng solution
2. **CẬP NHẬT** `docs/filament/FILAMENT_RULES.md` với fix + example
3. Thêm vào section "Common Mistakes"
4. Commit: `docs(filament): fix rule về [vấn đề]`

→ File rules là **LIVING DOCUMENT**, luôn cải thiện!



## 🗄️ Database Schema Management
- **Luôn sync `mermaid.rb`** khi tạo/sửa migration
- Phản ánh chính xác: tables, columns, types, constraints, indexes, FKs
- Format giống Rails schema.rb

## 💾 Database Backup Rules
**⚠️ QUAN TRỌNG**: Trước mỗi migration, LUÔN backup database!
- **📖 File rule chính**: `docs/spatie_backup.md` - Chi tiết đầy đủ về:
  - Workflow bắt buộc: Backup → Migration → Restore (nếu lỗi)
  - Commands: `php artisan backup:run --only-db`
  - Quy tắc đặt tên backup (với mô tả migration)
  - Cấu hình: Giữ tối đa 10 bản gần nhất
  - Troubleshooting: mysqldump, restore, v.v.

### Quick Summary:
- ✅ **Backup trước migration**: `php artisan backup:run --only-db`
- ✅ **Giữ tối đa 10 bản**: Tự động xóa backup cũ
- ✅ **Lưu tại**: `database/backups/Laravel/`
- ✅ **Đặt tên**: `YYYY-MM-DD-HH-MM-SS_migration-description.zip`
- ✅ **Restore**: Giải nén .zip → Import .sql vào MySQL

### 🔄 Cập nhật Rules khi cần:
**Nếu gặp lỗi/best practice mới về backup**:
1. Research đúng solution
2. **CẬP NHẬT** `docs/spatie_backup.md` với fix + example
3. Thêm vào section "Troubleshooting"
4. Commit: `docs(backup): fix rule về [vấn đề]`

→ File rules là **LIVING DOCUMENT**, luôn cải thiện!
