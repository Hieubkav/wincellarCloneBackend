# TODO Tổng Quan Wincellar Backend

## 1. Chuẩn hóa yêu cầu & kiến trúc

- [X] Đọc kỹ PLAN.md và thống nhất mục tiêu CTA, slug/redirect, phân quyền
- [X] Ghi lại scope API/FE/Admin và các nguyên tắc bắt buộc để cả team nắm được
- [X] Xác nhận với stakeholders về ưu tiên release, ràng buộc SEO/hiệu năng và quy tắc Filament

## 2. Thiết lập môi trường & cấu hình hệ thống

- [X] Hoàn thiện `.env`, kết nối DB/queue/cache, cấu hình log channel
- [X] Cấu hình CORS whitelist, rate limit 60 req/min, bảo mật PII (hash IP)
- [X] T?o d? b?ng danh m?c, products, taxonomy (catalog_attribute_groups/terms/pivot), polymorphic `images`, `home_components`, tracking, redirects
- [X] Th�m index/constraint (slug unique, is_primary unique theo pivot, FK on delete, partial cover) theo t�i li?u
## 3. Thiết kế migrations + seed dữ liệu

- [X] T?o model + relation cho Product, Article, CatalogAttributeGroup/CatalogTerm/ProductTermAssignment, Tracking*, UrlRedirect, HomeComponent, Setting...
- [X] Thêm index/constraint (slug unique, order=0 unique, FK on delete, partial cover) theo tài liệu
- [X] Viết seeder mẫu + seed hiệu năng (dataset lớn) để QA benchmark và FE test

## 4. Khai báo models/relations/pivot

- [ ] Tạo model + relation cho Product, Article, Brand, Country, Region, Grape, Tracking*, UrlRedirect, HomeComponent, Setting...
- [ ] Tách trait/service (slug trait, filter builder, media helper) để giữ mỗi file <500 dòng
- [ ] Bổ sung casts, scope, factory hỗ trợ API filter/sort và testing

## 5. Xây service slug & redirect tự sinh

- [ ] Viết slug generator normalize + đảm bảo unique + test case
- [ ] Tự động tạo redirect khi slug đổi, chặn duplicate, log cảnh báo
- [ ] Middleware redirect 301 (giữ query/fragment, <=5 hop) + job flatten chain định kỳ

## 6. Module media & logo/icon

- [ ] Implement gallery polymorphic, enforce `order=0` là cover duy nhất
- [ ] Xử lý logo/icon thông qua FK trực tiếp, nullify + toast khi xóa media đang được tham chiếu
- [ ] Job dọn media orphan và cấu hình placeholder fallback cho từng loại resource
- [ ] `GET /san-pham`: multi-filter terms[brand]/terms[origin.country]/terms[origin.region]/terms[grape]/type + price/alcohol range, sort, DISTINCT, meta pagination
## 7. Nghiệp vụ giá & khuyến mãi

- [ ] Service tính `discount_percent` (round 0 chữ số, trả `null` khi không giảm)
- [ ] Validation price/original_price + rule hiển thị CTA Liên hệ khi price=0
- [ ] Test case cho ngoại lệ (price<0, chia 0, discount âm, product inactive)

## 8. API sản phẩm

- [ ] `GET /san-pham`: multi-filter terms[brand]/terms[origin.country]/terms[origin.region]/terms[grape]/type + price/alcohol range, sort, DISTINCT, meta pagination
- [ ] `GET /san-pham/{slug}`: breadcrumb, gallery, badges, 404 khi inactive hoặc không tồn tại
- [ ] Tối ưu hiệu năng: caching nếu cần, kiểm tra EXPLAIN/index, đo P95/P99

## 9. API nội dung khác & home

- [ ] `GET /bai-viet` + `GET /bai-viet/{slug}` (thumbnail cover từ images order=0)
- [ ] `GET /home`: load `home_components`, bỏ qua resource inactive/bị xóa, log cảnh báo
- [ ] Endpoint tracking CTA/contact và các API hỗ trợ FE khác (social links, settings, contact info)

## 10. Filament Admin

- [ ] Tạo resource CRUD cho tất cả bảng, tách form/table component để dễ bảo trì
- [ ] Áp dụng policy + RBAC (`admin` vs `staff`), chặn staff truy cập settings/users/redirects
- [ ] Tích hợp audit log read-only + export CSV, toast cảnh báo khi hành động nhạy cảm

## 11. Tracking & batch jobs

- [ ] Model và ingestion cho visitor/session/event (`product_view`, `article_view`, `cta_contact`)
- [ ] API/dashboard analytics 7/30/90/all-time + cho phép export csv
- [ ] Schedule jobs: nightly build `tracking_event_aggregates_daily`, purge raw >90d, weekly media GC, flatten redirect chain

## 12. QA & hardening

- [ ] Viết unit/feature test bao phủ slug, giá/discount, API filter, permission Filament
- [ ] Chạy checklist nghiệm thu (redirect <=5 hop, canonical đúng, discount null, sitemap bỏ inactive, staff bị chặn)
- [ ] Benchmark hiệu năng (dataset lớn, GET /san-pham P95 <100ms) và chuẩn bị tài liệu bàn giao/cuối sprint
