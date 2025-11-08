Trả lời bằng tiếng việt

## 📁 Project Structure
- Đừng để logic hoặc file quá 500 dòng
- Hãy gọi các file để chia logic và kế thừa hợp lý
- **Tham khảo**: `PLAN.md` để hiểu dự án làm gì, chức năng gì

## 🎨 Filament 4.x Rules
**⚠️ QUAN TRỌNG**: Khi làm việc với Filament, LUÔN tham khảo:
- **📖 File rule chính**: `docs/FILAMENT_RULES.md` - Chi tiết đầy đủ về:
  - List/Create/Edit/RelationManager pages
  - Observer patterns (SEO, alt, order tự sinh)
  - Reorderable cho table có order column
  - Storage & File upload (WebP conversion)
  - Common mistakes & solutions
- **📚 Source code**: `vendor/filament/` - Đọc để hiểu sâu
- **🌐 Docs**: https://filamentphp.com/docs/4.x

### Quick Summary:
- ✅ Mọi resource quan trọng: Navigation badge (số lượng)
- ✅ Mọi cột: `->sortable()`, Có order → `->reorderable()`
- ✅ Mọi list: Bulk delete, Mọi record: Edit + Delete
- ✅ SEO fields: Tự sinh bằng Observer, ẨN khỏi form
- ✅ Image: Observer auto alt/order/delete + WebP 85%
- ✅ Eager load: `->modifyQueryUsing()`

### 🔄 Cập nhật Rules khi cần:
**Nếu gặp lỗi/hiểu sai về Filament**:
1. Research đúng solution
2. **CẬP NHẬT** `docs/FILAMENT_RULES.md` với fix + example
3. Thêm vào section "Common Mistakes"
4. Commit: `docs(filament): fix rule về [vấn đề]`

→ File rules là **LIVING DOCUMENT**, luôn cải thiện!



## 🗄️ Database Schema Management
- **Luôn sync `mermaid.rb`** khi tạo/sửa migration
- Phản ánh chính xác: tables, columns, types, constraints, indexes, FKs
- Format giống Rails schema.rb
