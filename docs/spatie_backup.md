# Spatie Laravel Backup - Rules & Best Practices

> **QUAN TRỌNG**: Đây là tài liệu rule chính thức cho Database Backup trong dự án này.
> Luôn tuân thủ các quy tắc dưới đây để bảo vệ dữ liệu trước khi chạy migration.

## 📚 Tài liệu tham khảo
- **Package**: `spatie/laravel-backup`
- **Docs chính thức**: https://spatie.be/docs/laravel-backup/v9/introduction

---

## 🎯 Workflow Bắt Buộc

### ⚠️ Rule #1: LUÔN Backup Trước Migration
```bash
# 1. Backup database trước
php artisan backup:run --only-db

# 2. Kiểm tra backup đã tạo
php artisan backup:list

# 3. Chạy migration an toàn
php artisan migrate

# 4. Nếu có lỗi → Restore
# Giải nén file .zip trong database/backups/Laravel/
# Import file .sql vào MySQL
```

**Lý do**: Migration có thể thay đổi schema không thể undo. Backup là bảo hiểm duy nhất!

---

## 📁 Cấu trúc Backup

### Nơi lưu trữ
```
database/
└── backups/
    └── Laravel/
        ├── 2025-11-08-11-14-39_before-add-products-table.zip
        ├── 2025-11-09-09-30-12_before-update-users-schema.zip
        └── ... (tối đa 10 bản gần nhất)
```

### ✅ Quy tắc đặt tên
```bash
# Format: YYYY-MM-DD-HH-MM-SS_migration-description.zip
# VD: 2025-11-08-14-30-00_before-add-products-table.zip

# Tên file nên:
✅ Có timestamp (tự động) + mô tả migration
✅ Ngắn gọn, snake_case
✅ Không chứa ký tự đặc biệt

# Ví dụ tốt:
2025-11-08-11-14-39_before-add-products-table.zip
2025-11-09-09-30-12_before-update-users-schema.zip
2025-11-10-15-45-00_before-add-foreign-keys.zip

# ❌ Tránh:
backup.zip  (Không rõ thời điểm)
2025-11-08.zip  (Không biết backup cho migration nào)
```

**Cách đặt tên thủ công** (nếu cần):
Sau khi chạy `php artisan backup:run --only-db`, đổi tên file zip trong `database/backups/Laravel/` để thêm mô tả migration.

---

## 🔧 Cấu hình Quan Trọng

### File: `config/backup.php`

#### 1. Chỉ backup Database (không backup files)
```php
'source' => [
    'files' => [
        'include' => [
            // Để trống = không backup files
        ],
    ],
    'databases' => [
        env('DB_CONNECTION', 'mysql'),  // Backup DB hiện tại
    ],
],
```

#### 2. Lưu vào database/backups/
```php
'destination' => [
    'disks' => [
        'backup',  // Disk 'backup' → database/backups/
    ],
],
```

#### 3. Giữ tối đa 10 bản gần nhất
```php
'cleanup' => [
    'default_strategy' => [
        'keep_all_backups_for_days' => 3,  // Giữ 3 ngày gần nhất
        'keep_daily_backups_for_days' => 7,  // 7 backup daily
        'keep_weekly_backups_for_weeks' => 0,  // Không giữ weekly
        'keep_monthly_backups_for_months' => 0,  // Không giữ monthly
        'keep_yearly_backups_for_years' => 0,  // Không giữ yearly
        'delete_oldest_backups_when_using_more_megabytes_than' => 500,  // Giới hạn 500MB
    ],
],
```

**Giải thích**: Với config trên, sẽ tự động xóa backup cũ khi > 10 bản (3 ngày + 7 daily).

---

## 📝 Commands Thường Dùng

### Backup Database
```bash
# Backup chỉ database (không backup files)
php artisan backup:run --only-db

# Backup với output chi tiết
php artisan backup:run --only-db -vvv
```

### Quản lý Backups
```bash
# Liệt kê tất cả backups
php artisan backup:list

# Xóa backups cũ (giữ theo config)
php artisan backup:clean

# Monitor health của backups
php artisan backup:monitor
```

---

## 🚨 Troubleshooting

### Lỗi: "mysqldump not found"
**Nguyên nhân**: Backup cần tool `mysqldump` để export database.

**Giải pháp**:
1. **Windows + XAMPP**:
   ```php
   // config/database.php → mysql/mariadb connection
   'dump' => [
       'dump_binary_path' => 'C:/xampp/mysql/bin',
   ],
   ```
   Tạo symlink (nếu dùng MariaDB driver):
   ```bash
   copy "C:\xampp\mysql\bin\mysqldump.exe" "C:\xampp\mysql\bin\mariadb-dump.exe"
   ```

2. **Mac/Linux**: Thêm vào PATH hoặc config đường dẫn tương tự.

### Lỗi: "Backup disk không tồn tại"
**Giải pháp**: Kiểm tra `config/filesystems.php`:
```php
'disks' => [
    'backup' => [
        'driver' => 'local',
        'root' => database_path('backups'),
    ],
],
```

### Lỗi: "Zip creation failed"
**Nguyên nhân**: Extension PHP `zip` chưa bật.

**Giải pháp**:
- Bật extension `php_zip.dll` trong `php.ini`
- Restart web server

---

## 📊 Restore Database từ Backup

### Bước 1: Giải nén backup
```bash
# Tìm file backup trong database/backups/Laravel/
# VD: 2025-11-08-11-14-39_before-add-products-table.zip

# Giải nén → lấy file .sql (VD: mariadb-wincellar.sql)
```

### Bước 2: Import vào MySQL
```bash
# Qua phpMyAdmin:
1. Mở phpMyAdmin → Chọn database
2. Tab "Import" → Chọn file .sql
3. Click "Go"

# Hoặc dùng command line:
mysql -u root -p wincellar < mariadb-wincellar.sql
```

### Bước 3: Verify
```bash
# Kiểm tra dữ liệu đã restore đúng
php artisan tinker
>>> User::count();  // Kiểm tra số lượng records
```

---

## ✅ Checklist Trước Migration

```markdown
- [ ] Đọc migration files để hiểu thay đổi
- [ ] Chạy: php artisan backup:run --only-db
- [ ] Kiểm tra: php artisan backup:list (có file mới?)
- [ ] Đổi tên backup zip để thêm mô tả migration (optional)
- [ ] Chạy: php artisan migrate
- [ ] Test: Kiểm tra app hoạt động bình thường
- [ ] Nếu lỗi: Restore từ backup + rollback migration
```

---

## 🔄 Cập nhật Rules

**Nếu phát hiện best practice mới hoặc gặp lỗi**:
1. Research giải pháp đúng
2. **CẬP NHẬT** file `docs/spatie_backup.md` này
3. Thêm vào section "Troubleshooting" hoặc tạo section mới
4. Commit: `docs(backup): add rule về [vấn đề]`

→ File này là **LIVING DOCUMENT**, luôn cải thiện!

---

## 📌 Quick Reference

| Task | Command |
|------|---------|
| Backup DB | `php artisan backup:run --only-db` |
| List backups | `php artisan backup:list` |
| Clean old backups | `php artisan backup:clean` |
| Restore | Giải nén .zip → Import .sql vào MySQL |

**Remember**: Backup là bảo hiểm, không có backup = mạo hiểm mất data! 🛡️
