Dự án web rượu/bia/thịt nguội/bánh – **Bản chốt cuối cùng gửi khách (v1.2 – 27/10/2025)**

> Phiên bản: **1.2** (27/10/2025)
>
> Cập nhật: mô tả rõ **mối quan hệ hình ảnh (polymorphic)** giữa sản phẩm, bài viết, brand, settings, social link; hoàn thiện ERD và logic API.

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
4. **Hình ảnh – CHỐT (clarified)**

   • Ảnh dùng  **một bảng polymorphic `images`** , có thể gắn với `Product`, `Article`, `Brand`, `Setting`, `SocialLink`… qua `model_type` + `model_id`.

   • Sản phẩm có  **nhiều ảnh (gallery)** , `order=0` là ảnh cover.

   • Một số bảng (như `settings`, `social_links`) có **ảnh cố định 1-1** (logo, favicon, icon) qua FK trực tiếp (`logo_image_id`, `icon_image_id`).
5. **Badge hiển thị – OKE**

   • Enum chuẩn: `SALE`, `HOT`, `NEW`, `LIMITED`; cho phép label custom nếu cần.

   • FE hiển thị theo style thống nhất.
6. **Giá khuyến mãi & % giảm – "để sẵn trong API"**

   • Backend tính sẵn `discount_percent`; FE dùng tùy ý.

   • `discount_percent = (original_price - price)/original_price * 100` (round 0–1 chữ số).
7. **Analytics theo thời gian – CHỐT**

   • Dashboard chọn 7/30/90/all-time; export CSV.

   • Có thể dọn dữ liệu chi tiết >90 ngày.
8. **Ẩn sản phẩm/bài viết – CHỐT**

   • Khi `active=false`, bỏ qua ở FE & home component.
9. **Mega menu linh hoạt – OKE**

   • Block tùy biến: tiêu đề + danh sách link custom (ví dụ “Ưu đãi Tết”).
10. **Audit log trong Admin – OKE**

    • Có trang  **read-only** , tra cứu theo user/action/time, export CSV.

---

## B) Tóm tắt hệ thống

* Không bán trực tiếp; CTA “Liên hệ”.
* Trang: Trang chủ (qua `home_components`), Trang lọc, Trang chi tiết, Editorial, Liên hệ.
* Header: menu thường + mega menu. Footer: settings + social links.
* Admin (Filament 4.x): quản lý sản phẩm, danh mục, images (media library), homepage blocks, menu, bài viết, settings, analytics, audit log.
* Phân quyền: `admin`, `staff` (staff không chỉnh settings hoặc user khác).
* SEO: auto meta khi trống.
* Tracking: visitor/session/event.

---

## C) ERD (Mermaid) – cập

```

```

 nhật mô hình ảnh polymorphic

```mermaid
erDiagram
  USERS ||--o{ AUDIT_LOGS : performs
  USERS ||--o{ ARTICLES : writes

  PRODUCTS ||--o{ IMAGES : has_many (gallery)
  ARTICLES ||--o{ IMAGES : has_many (gallery)
  BRANDS ||--o{ IMAGES : has_many
  SETTINGS ||--o{ IMAGES : uses_logo_favicon
  SOCIAL_LINKS ||--o{ IMAGES : uses_icon

  PRODUCT_CATEGORIES ||--o{ PRODUCTS : groups
  PRODUCT_TYPES ||--o{ PRODUCTS : types
  BRANDS ||--o{ PRODUCTS : owns
  COUNTRIES ||--o{ REGIONS : contains
  REGIONS ||--o{ PRODUCTS : origin

  GRAPES ||--o{ PRODUCT_GRAPES : pivot
  PRODUCTS ||--o{ PRODUCT_GRAPES : pivot
  REGIONS ||--o{ PRODUCT_REGIONS : pivot
  PRODUCTS ||--o{ PRODUCT_REGIONS : pivot

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
```

---

## D) API/Controller – chuẩn cuối

* **GET /san-pham**

  Query: `brand[]`, `country[]`, `region[]`, `grape[]`, `type[]`, `price_min`, `price_max`, `alcohol_min`, `alcohol_max`.

  Trả kết quả có `discount_percent`, `main_image_url`, `gallery[]`.

  Sort mặc định: `created_at DESC`.
* **GET /san-pham/{slug}**

  Trả: product + gallery + breadcrumbs + `discount_percent`.
* **GET /bai-viet** , **GET /bai-viet/{slug}**

  FE hiển thị thumbnail từ `images` (`model_type='Article'`).
* **GET /home**

  Build từ `home_components` (bỏ qua inactive items).
* **Redirect middleware**

  Check `url_redirects.from_slug` → 301 → `to_slug`.

---

## E) Index/Performance

* `INDEX (brand_id, country_id, region_id, type_id, product_category_id)`.
* `INDEX (alcohol_percent)`, `INDEX (volume_ml)`, `INDEX (price)`.
* Pivot: PK composite + index nghịch.
* `tracking_events(product_id, created_at)`; `url_redirects.from_slug UNIQUE`.

---

## F) FE Convention

* `price>0` → hiển thị giá VND; `price=0` → “Liên hệ”.
* `original_price>price` → hiển thị giá gạch + % giảm.
* Ảnh: `order=0` = cover, placeholder khi rỗng.
* Slug tự sinh, có redirect.
* Home render `active=true` theo `order ASC`.

---

## G) Filament Resources

* Product, Category, Type, Brand, Country, Region, Grape, Product↔Grape, Product↔Region.
* Article, Image (media), Menu, MenuBlock, MenuBlockItem.
* HomeComponent, Settings (singleton), SocialLink.
* Tracking (read-only), AuditLog (read-only).

---

## H) Checklist nghiệm thu

* [X] Multi-filter hoạt động mượt (EXPLAIN OK).
* [X] Redirect slug 301 hoạt động.
* [X] Placeholder ảnh đúng loại.
* [X] API trả `discount_percent`.
* [X] Analytics 7/30/90/all-time ok.
* [X] Staff bị hạn chế Settings/User.
* [X] SEO meta auto + OG image.

---

### Phụ lục – Config `home_components`

* **HeroCarousel** : `{ "slides": [{"image_id":1,"alt":"..."}] }`
* **DualBanner** : `{ "banners": [{"image_id":1,"alt":"...","href":"/abc"}] }`
* **CategoryGrid** : `{ "categories": [{"name":"Vang đỏ","image_id":1,"href":"/..."}] }`
* **FavouriteProducts** : `{ "products": [{"product_id":1,"badge":"SALE"}] }`
* **BrandShowcase** : `{ "brands": [{"brand_id":1,"href":"/..."}] }`
* **CollectionShowcase** : `{ "title":"Rượu Vang","subtitle":"...","description":"...","ctaLabel":"Xem thêm","ctaHref":"/ruou-vang","tone":"wine|spirit","products":[{"product_id":1,"badge":"HOT"}] }`
* **EditorialSpotlight** : `{ "title":"Cẩm nang rượu","articles":[{"article_id":1}] }`
