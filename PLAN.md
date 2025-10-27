# Dự án web rượu/bia/thịt nguội/bánh – **Bản chốt theo phản hồi khách**

> Phiên bản: **1.1** (27/10/2025)
>
> Thay đổi chính: cập nhật các **quyết định đã chốt** theo phản hồi 1→10; điều chỉnh API và ERD tương ứng.

---

## A) Quyết định đã chốt (1 → 10)

1. **Giống nho / Vùng sản xuất – CHỐT**

   • Hỗ trợ **nhiều giống nho** và **nhiều vùng** qua pivot `product_grapes`, `product_regions`.

   • Có thể đánh dấu **“chính”** bằng `order=0` trên pivot để hiển thị marketing.
2. **Filter sản phẩm nhiều giá trị – CHỐT**

   • UI  **multi-select** ; backend `WHERE IN`/join pivot.

   • Hỗ trợ lọc: loại, thương hiệu, quốc gia, vùng, giống nho, tầm giá, dung tích, nồng độ.
3. **Đổi slug & Redirect – CHỐT**

   • Tự động tạo redirect 301 khi slug đổi.

   • Bảng `url_redirects(from_slug → to_slug)`; không giới hạn TTL.
4. **Hình ảnh – OKE**

   • Không bắt buộc có ảnh cover khi publish; nếu thiếu, FE dùng **placeholder** theo loại.

   • Admin được cảnh báo khi lưu/publish không có ảnh.
5. **Badge hiển thị – OKE**

   • Dùng **enum** chuẩn: `SALE`, `HOT`, `NEW`, `LIMITED`.

   • Cho phép **label tùy chỉnh** (text ngắn) nếu cần; enum vẫn ưu tiên để thống nhất style.
6. **Giá khuyến mãi & % giảm – "để sẵn trong API"**

   • Backend **tính sẵn** `discount_percent` (round 0–1 chữ số thập phân).

   • FE **tùy** sử dụng: hiển thị `%` hoặc chỉ giá gạch.

   • Logic: nếu `price>0` & `original_price>price` ⇒ `discount_percent = (original_price-price)/original_price*100`.
7. **Analytics theo thời gian – CHỐT**

   • Dashboard chọn  **7/30/90 ngày & all-time** ; export CSV.

   • Có thể dọn `tracking_events` >90 ngày, vẫn giữ tổng quan.
8. **Ẩn sản phẩm/bài viết – CHỐT**

   • Khi `active='false'`, mọi block/homepage bỏ qua item đó, không báo lỗi.
9. **Mega menu linh hoạt – OKE**

   • Cho phép block tùy biến (ví dụ “Ưu đãi Tết”), tiêu đề + danh sách link custom, không giới hạn vào quốc gia/giống nho.
10. **Audit log trong Admin – OKE**

    • Có trang **read-only** để tra cứu theo thời gian/action/user,  **export CSV** .

---

## B) Tóm tắt hệ thống (không đổi trọng yếu)

* **Không bán trực tiếp** ; CTA  **Liên hệ** .
* **Trang** : Trang chủ (qua `home_components`), Trang lọc, Trang chi tiết, Editorial, Liên hệ.
* **Header** : menu thường + mega menu;  **Footer** : settings + social links.
* **Admin (Filament 4.x)** : quản lý sản phẩm/thuộc tính/danh mục, images, homepage blocks, menu, bài viết, settings, analytics, audit log.
* **Phân quyền** : `admin`, `staff` (staff không chỉnh settings và không chỉnh/xóa user khác).
* **SEO** : meta auto khi để trống.
* **Tracking** : visitor, session, event.

---

## C) ERD (Mermaid) – cập nhật theo bản chốt

```mermaid
erDiagram
  USERS ||--o{ AUDIT_LOGS : performs
  USERS ||--o{ ARTICLES : writes

  SETTINGS ||--o{ IMAGES : uses
  SOCIAL_LINKS ||--|| IMAGES : icon

  PRODUCT_CATEGORIES ||--o{ PRODUCTS : groups
  PRODUCT_TYPES ||--o{ PRODUCTS : types
  BRANDS ||--o{ PRODUCTS : owns
  COUNTRIES ||--o{ REGIONS : contains
  REGIONS ||--o{ PRODUCTS : origin

  GRAPES ||--o{ PRODUCT_GRAPES : pivot
  PRODUCTS ||--o{ PRODUCT_GRAPES : pivot
  REGIONS ||--o{ PRODUCT_REGIONS : pivot
  PRODUCTS ||--o{ PRODUCT_REGIONS : pivot

  PRODUCTS ||--o{ IMAGES : gallery
  ARTICLES ||--o{ IMAGES : gallery

  MENUS ||--o{ MENU_BLOCKS : has
  MENU_BLOCKS ||--o{ MENU_BLOCK_ITEMS : has

  HOME_COMPONENTS }o--o{ PRODUCTS : via_config
  HOME_COMPONENTS }o--o{ ARTICLES : via_config

  VISITORS ||--o{ VISITOR_SESSIONS : has
  VISITOR_SESSIONS ||--o{ TRACKING_EVENTS : groups
  VISITORS ||--o{ TRACKING_EVENTS : triggers
  PRODUCTS ||--o{ TRACKING_EVENTS : viewed

  URL_REDIRECTS }o--|| PRODUCTS : to_product
  URL_REDIRECTS }o--|| ARTICLES : to_article

  USERS {
    bigint id PK
    string name
    string email UNIQUE
    string password
    enum role('admin','staff')
    enum active('true','false')
    timestamps
  }

  AUDIT_LOGS {
    bigint id PK
    bigint user_id FK NULL
    string action
    longtext details_json
    datetime created_at
  }

  SETTINGS {
    bigint id PK // =1
    bigint logo_image_id FK NULL
    bigint favicon_image_id FK NULL
    string site_name
    string hotline
    string address
    string hours
    string email
    string meta_default_title
    string meta_default_description
    string meta_default_keywords
    timestamps
  }

  SOCIAL_LINKS {
    bigint id PK
    string name
    string url
    bigint icon_image_id FK
    int order
    enum active('true','false')
    timestamps
  }

  IMAGES {
    bigint id PK
    string url
    string alt_text NULL
    text caption NULL
    string model_type
    bigint model_id
    int order
    timestamps
  }

  PRODUCT_CATEGORIES {
    bigint id PK
    string name
    string slug UNIQUE
    text description NULL
    int order
    enum active('true','false')
    timestamps
  }

  PRODUCT_TYPES {
    bigint id PK
    string name
    string slug UNIQUE
    text description NULL
    int order
    enum active('true','false')
    timestamps
  }

  BRANDS {
    bigint id PK
    string name
    string slug UNIQUE
    text description NULL
    int order
    enum active('true','false')
    timestamps
  }

  COUNTRIES {
    bigint id PK
    string name
    string slug UNIQUE
    text description NULL
    int order
    enum active('true','false')
    timestamps
  }

  REGIONS {
    bigint id PK
    string name
    string slug UNIQUE
    bigint country_id FK
    text description NULL
    int order
    enum active('true','false')
    timestamps
  }

  GRAPES {
    bigint id PK
    string name
    string slug UNIQUE
    text description NULL
    int order
    enum active('true','false')
    timestamps
  }

  PRODUCTS {
    bigint id PK
    string name
    string slug UNIQUE
    bigint product_category_id FK
    bigint type_id FK NULL
    bigint brand_id FK NULL
    bigint country_id FK NULL
    bigint region_id FK NULL // region chính (tùy)
    bigint grape_id FK NULL  // grape chính (tùy)
    longtext description
    int volume_ml NULL
    decimal(5,2) alcohol_percent NULL
    decimal(15,2) price DEFAULT 0
    decimal(15,2) original_price DEFAULT 0
    enum active('true','false') DEFAULT 'true'
    int order
    string meta_title NULL
    string meta_description NULL
    string meta_keywords NULL
    timestamps
  }

  PRODUCT_GRAPES {
    bigint product_id FK
    bigint grape_id FK
    int order DEFAULT 0
    PK(product_id, grape_id)
  }

  PRODUCT_REGIONS {
    bigint product_id FK
    bigint region_id FK
    int order DEFAULT 0
    PK(product_id, region_id)
  }

  ARTICLES {
    bigint id PK
    string title
    string slug UNIQUE
    longtext content
    enum active('true','false') DEFAULT 'true'
    string meta_title NULL
    string meta_description NULL
    string meta_keywords NULL
    timestamps
  }

  MENUS {
    bigint id PK
    string title
    enum type('normal','mega')
    string href NULL
    int order
    enum active('true','false')
    timestamps
  }

  MENU_BLOCKS {
    bigint id PK
    bigint menu_id FK
    string title
    int order
    enum active('true','false')
    timestamps
  }

  MENU_BLOCK_ITEMS {
    bigint id PK
    bigint menu_block_id FK
    string label
    string href
    string badge NULL
    int order
    enum active('true','false')
    timestamps
  }

  HOME_COMPONENTS {
    bigint id PK
    enum type('HeroCarousel','DualBanner','CategoryGrid','FavouriteProducts','BrandShowcase','CollectionShowcase','EditorialSpotlight')
    longtext config_json
    int order
    enum active('true','false')
    timestamps
  }

  VISITORS {
    bigint id PK
    string ip_address
    string user_agent
    string device
    string country NULL
    datetime first_seen_at
    datetime last_seen_at
    timestamps
  }

  VISITOR_SESSIONS {
    bigint id PK
    bigint visitor_id FK
    string session_key
    datetime start_time
    datetime end_time NULL
    int pages_viewed DEFAULT 0
    timestamps
  }

  TRACKING_EVENTS {
    bigint id PK
    bigint visitor_id FK
    bigint visitor_session_id FK
    enum event_type('page_view','click','scroll','contact_view','other')
    string page_url
    bigint product_id FK NULL
    string country_snapshot NULL
    longtext data_json NULL
    datetime created_at
  }

  URL_REDIRECTS {
    bigint id PK
    string model_type // 'Product' | 'Article'
    bigint model_id
    string from_slug UNIQUE
    string to_slug
    enum active('true','false') DEFAULT 'true'
    datetime created_at
  }
```

---

## D) API/Controller – điều chỉnh theo bản chốt

* **Danh sách sản phẩm** `GET /san-pham`
  * Query: `brand[]`, `country[]`, `region[]`, `grape[]`, `type[]`, `price_min`, `price_max`, `alcohol_min`, `alcohol_max`…
  * Mặc định sort `created_at DESC`; phân trang.
  * Join pivot khi có `region[]`/`grape[]` nhiều giá trị.
* **Chi tiết sản phẩm** `GET /san-pham/{slug}`
  * Trả: product + gallery + breadcrumbs + **`discount_percent`** (tính sẵn).
  * FE **có thể** dùng `%` hoặc bỏ qua, tùy UI.
* **Bài viết** `GET /bai-viet`, `GET /bai-viet/{slug}`.
* **Trang chủ** `GET /home`
  * Build từ `home_components`; tự bỏ qua item inactive/404.
* **Redirect**
  * Middleware check `url_redirects.from_slug` → 301 đến `to_slug`.

---

## E) Index/Performance

* **Sản phẩm** : `INDEX (brand_id, country_id, region_id, type_id, product_category_id)`, `INDEX (alcohol_percent)`, `INDEX (volume_ml)`, `INDEX (price)`.
* **Pivot** : PK composite + index nghịch `grape_id`, `region_id`.
* **Tracking** : `tracking_events(product_id, created_at)`, `tracking_events(visitor_id, visitor_session_id)`.
* **Slug/Redirect** : `UNIQUE(slug)`; `url_redirects.from_slug UNIQUE`.

---

## F) Quy ước FE (không đổi)

* **Giá** : `price>0` → hiển thị VND; nếu `original_price>price` → giá gạch + **% giảm** (nếu FE dùng). `price=0` → "Liên hệ".
* **Sort list** : `created_at DESC` (trừ nơi dùng `order`).
* **Slug** : tự sinh; cập nhật theo tên;  **có redirect** .
* **Ảnh** : cover = `order=0`; vắng cover dùng placeholder.
* **Homepage** : render `active='true'` theo `order ASC, created_at DESC`.

---

## G) Filament Resources

* Product, Category, Type, Brand, Country, Region, Grape,  **Product↔Grape (pivot)** ,  **Product↔Region (pivot)** .
* Article, Image (Media), Menu, MenuBlock, MenuBlockItem.
* HomeComponent (form JSON theo type).
* Settings (singleton), SocialLink.
* Tracking (read-only),  **AuditLog (read-only)** .

---

## H) Checklist gửi khách nghiệm thu

* [ ] Filter multi-select chạy nhanh (EXPLAIN OK).
* [ ] Redirect slug cũ hoạt động 301.
* [ ] Placeholder ảnh nhất quán.
* [ ] `discount_percent` có trong API; FE hiển thị tùy chọn.
* [ ] Analytics 7/30/90/all + export.
* [ ] Staff bị khóa Settings & User management.
* [ ] SEO auto meta + OG từ ảnh cover.

---

### Phụ lục – Cấu hình `home_components` (tham khảo)

* **HeroCarousel** : `{ "slides": [{"image_id":1,"alt":"..."}] }`
* **DualBanner** : `{ "banners": [{"image_id":1,"alt":"...","href":"/abc"}] }`
* **CategoryGrid** : `{ "categories": [{"name":"Vang đỏ","image_id":1,"href":"/..."}] }`
* **FavouriteProducts** : `{ "products": [{"product_id":1,"badge":"SALE"}] }`
* **BrandShowcase** : `{ "brands": [{"brand_id":1,"href":"/..."}] }`
* **CollectionShowcase** : `{ "title":"Rượu Vang","subtitle":"...","description":"...","ctaLabel":"Xem thêm","ctaHref":"/ruou-vang","tone":"wine|spirit","products":[{"product_id":1,"badge":"HOT"}] }`
* **EditorialSpotlight** : `{ "title":"Cẩm nang rượu","articles":[{"article_id":1}] }`
