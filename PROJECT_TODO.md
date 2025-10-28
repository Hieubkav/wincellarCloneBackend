# TODO Tổng Quan Wincellar Backend

## 1. Chuẩn hoá yêu cầu & kiến trúc

- [X] Đọc kỹ PLAN.md và thống nhất mục tiêu CTA, slug/404 (không redirect auto), phân quyền
- [X] Ghi lại scope API/FE/Admin và các nguyên tắc bắt buộc để cả team nắm được
- [X] Xác nhận với stakeholders về ưu tiên release, ràng buộc SEO/hiệu năng và quy tắc Filament

## 2. Thiết lập môi trường & cấu hình hệ thống

- [X] Hoàn thiện .env, kết nối DB/queue/cache, cấu hình log channel
- [X] Cấu hình CORS whitelist, rate limit 60 req/min, bảo mật PII (hash IP)
- [X] Tạo đủ bảng danh mục, products, taxonomy (catalog_attribute_groups/terms/pivot), polymorphic images, home_components, tracking, redirects
- [X] Thêm index/constraint (slug unique, is_primary unique theo pivot, FK on delete, partial cover) theo tài liệu

## 3. Thiết kế migrations + seed dữ liệu

- [X] Tạo model + relation cho Product, Article, CatalogAttributeGroup/CatalogTerm/ProductTermAssignment, Tracking*, UrlRedirect, HomeComponent, Setting...
- [X] Thêm index/constraint (slug unique, order=0 unique, FK on delete, partial cover) theo tài liệu
- [X] Viết seeder mẫu + seed hiệu năng (dataset lớn) để QA benchmark và FE test

## 4. Module media & logo/icon

- [X] Implement gallery polymorphic, enforce order=0 là cover duy nhất
- [X] Xử lý logo/icon thông qua FK trực tiếp, nullify + toast khi xoá media đang được tham chiếu
- [X] Job dọn media orphan và cấu hình placeholder fallback cho từng loại resource
- [X] GET /san-pham: multi-filter terms[brand]/terms[origin.country]/terms[origin.region]/terms[grape]/type + price/alcohol range, sort, DISTINCT, meta pagination

## 5. Nghiệp vụ giá & khuyến mại

- [X] Service tính discount_percent (round 0 chữ số, trả `null` khi không giảm)
- [X] Validation price/original_price + rule hiển thị CTA Liên hệ khi price=0
- [X] Test case cho ngoại lệ (price<0, chia 0, discount âm, product inactive)

## 6. API sản phẩm

- [X] GET /san-pham: multi-filter terms[brand]/terms[origin.country]/terms[origin.region]/terms[grape]/type + price/alcohol range, sort, DISTINCT, meta pagination
- [X] GET /san-pham/{slug}: breadcrumb, gallery, badges, 404 khi inactive hoặc không tồn tại
- [X] Tối ưu hiệu năng: caching nếu cần, kiểm tra EXPLAIN/index, đo P95/P99

## 7. API nội dung khác & home

- [X] GET /bai-viet + GET /bai-viet/{slug} (thumbnail cover từ images order=0)
- [X] GET /home: load home_components, bỏ qua resource inactive/đã xoá, log cảnh báo
- [X] Endpoint tracking CTA/contact và các API hỗ trợ FE khác (social links, settings, contact info)

## 8. Filament Admin

- [ ] Tạo resource CRUD cho tất cả bảng, tách form/table component để dễ bảo trì
- [ ] Áp dụng policy + RBAC (`admin` vs `staff`), chặn staff truy cập settings/users/redirects
- [ ] Tích hợp audit log read-only + export CSV, toast cảnh báo khi hành động nhạy cảm

## 9. Tracking & batch jobs

- [ ] Model và ingestion cho visitor/session/event (`product_view`, `article_view`, `cta_contact`)
- [ ] API/dashboard analytics 7/30/90/all-time + cho phép export csv
- [ ] Schedule jobs: nightly build `tracking_event_aggregates_daily`, purge raw >90d, weekly media GC
