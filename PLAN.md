## 0. Tóm tắt tính năng web

* Web giới thiệu sản phẩm rượu/bia/thịt nguội/bánh. Không bán trực tiếp, nút kêu gọi hành động là liên hệ.
* Trang chính:
  * Trang chủ (cấu hình bằng các block động `home_components`).
  * Trang lọc sản phẩm (filter theo các thuộc tính).
  * Trang chi tiết sản phẩm.
  * Trang bài viết / editorial.
  * Trang liên hệ.
* Header có menu thường + mega menu (cấu hình động).
* Footer hiển thị thông tin công ty (settings) + mạng xã hội (social_links).
* Admin (Filament 4.x):
  * Quản lý sản phẩm, danh mục, thuộc tính lọc.
  * Quản lý homepage block.
  * Quản lý menu.
  * Quản lý bài viết.
  * Quản lý settings chung.
  * Xem analytics traffic.
  * Nhật ký thao tác (audit_logs).
* Phân quyền: `admin` và `staff`. Staff KHÔNG được chỉnh `settings` và KHÔNG được chỉnh/xoá user khác.
* SEO: tự sinh meta nếu admin không nhập.
* Tracking: đếm visitor, session, event → có thể xem top sản phẩm xem nhiều / top quốc gia.

---

## Quy ước chung

* Tất cả bảng public ra FE đều có:
  * `id` PK (bigint AI)
  * `order` int default 0 (dùng cho ưu tiên hiển thị thủ công)
  * `active` enum('true','false') default 'true' (ẩn/hiện)
  * `created_at`, `updated_at`
* KHÔNG dùng `softDeletes()` của Laravel.
* FE list mặc định sort theo `created_at DESC`. `order` chỉ là ưu tiên thủ công trong 1 số view đặc biệt (ví dụ block trên trang chủ), không phải sort global.
* SEO tự sinh: nếu meta_* null thì backend sẽ generate dựa theo name/title/description khi lưu.
* Slug: luôn có ở entity public. Slug sẽ **được cập nhật lại** theo tên mới mỗi lần admin đổi tên.
* Ảnh: dùng bảng `images` duy nhất. Ảnh có `order`, và **ảnh có `order = 0` được xem là ảnh chính/cover** cho entity đó.
* Giá:
  * `price` (giá bán hiện tại, VND)
  * `original_price` (giá gốc trước khuyến mãi)
  * Nếu `price = 0` thì FE hiển thị "Liên hệ".

---

## 1. Quản trị người dùng

### users

* id
* name
* email (unique)
* password
* role enum('admin','staff')
* active enum('true','false') default 'true'
* created_at, updated_at

Quyền:

* staff không được chỉnh `settings`.
* staff không được chỉnh/xoá user khác.

### audit_logs (log thao tác quản trị)

* id
* user_id FK -> users.id nullable (null nếu system job)
* action (enum hoặc string hẹp):
  * `CREATE_PRODUCT`, `UPDATE_PRODUCT`, `DELETE_PRODUCT`
  * `UPDATE_SETTINGS`
  * `UPDATE_MENU`
  * `UPDATE_HOME_COMPONENT`
  * `CREATE_ARTICLE`, `UPDATE_ARTICLE`, `DELETE_ARTICLE`
* details_json longtext // lưu payload/diff để truy vết
* created_at

Chính sách lưu trữ:

* Cron tự xoá các bản ghi >90 ngày.

---

## 2. Thiết lập hệ thống / footer / mạng xã hội

### settings (singleton id=1)

* id (hardcode = 1)
* site_name
* logo_image_id FK -> images.id (nullable)
* favicon_image_id FK -> images.id (nullable)
* hotline
* address
* hours (giờ mở cửa)
* email
* meta_default_title
* meta_default_description
* meta_default_keywords
* created_at, updated_at

> Admin chỉ có 1 record. FE dùng record id=1.

### social_links

* id
* name (ví dụ: "Facebook", "Zalo") → dùng hiển thị label luôn
* url
* icon_image_id FK -> images.id
* order int default 0
* active enum('true','false')
* created_at, updated_at

> Chỉ cần 1 icon cho mỗi mạng xã hội (không cần dark mode).

---

## 3. Media

### images (kho media dùng chung cho mọi nơi)

* id
* url (string)
* alt_text (string nullable)
* caption (text nullable)
* model_type (string: 'Product', 'Article', 'Brand', 'HomeComponent', 'Setting', 'Menu', ...)
* model_id (bigint)
* order int default 0
* created_at, updated_at

Quy ước:

* Mỗi entity có thể có nhiều ảnh (gallery). Ảnh có `order=0` là ảnh chính.
* Thương hiệu (brand) logo cũng lấy ở đây, không cần field riêng.

---

## 4. Danh mục & thuộc tính sản phẩm

Chia 2 lớp:

1. Marketing group (để admin tự gom sản phẩm theo ý muốn, ví dụ "Sản phẩm khác", "Đùi heo muối")
2. Thuộc tính filter chuyên sâu (loại rượu, giống nho, vùng nổi tiếng...).

### product_categories (nhóm marketing / dùng cho menu SẢN PHẨM KHÁC, banner, grouping lớn)

* id
* name (vd: "Rượu vang", "Rượu mạnh", "Bánh", "Đùi heo muối")
* slug (unique trong bảng)
* description text nullable
* order int default 0
* active enum('true','false')
* created_at, updated_at

### product_types (kiểu/loại đồ uống hoặc style như "Rượu vang đỏ", "Champagne", "Bia")

* id
* name
* slug (unique trong bảng)
* description text nullable
* order int default 0
* active enum('true','false')
* created_at, updated_at

### brands

* id
* name
* slug
* description text nullable
* order int default 0
* active enum('true','false')
* created_at, updated_at

> Logo/thương hiệu: chọn thông qua bảng `images` (morph, order=0 là logo chính khi FE hiển thị carousel BrandShowcase).

### countries

* id
* name
* slug
* description text nullable
* order int default 0
* active enum('true','false')
* created_at, updated_at

### regions

(VD: Bordeaux, Tuscany, California...)

* id
* name
* slug
* country_id FK -> countries.id
* description text nullable
* order int default 0
* active enum('true','false')
* created_at, updated_at

### grapes

(VD: Cabernet Sauvignon, Merlot...)

* id
* name
* slug
* description text nullable
* order int default 0
* active enum('true','false')
* created_at, updated_at

⚠ Business pending:

* Hiện tại 1 sản phẩm chọn được 1 grape và 1 region. Nếu khách yêu cầu "nhiều giống nho" hoặc "nhiều vùng" thì cần bảng pivot `product_grapes(product_id, grape_id)` và `product_regions(product_id, region_id)` ngay từ đầu để khỏi migrate sau.

---

## 5. Sản phẩm

### products

* id
* name
* slug (unique trong bảng)
* product_category_id FK -> product_categories.id (bắt buộc)
* type_id FK -> product_types.id (nullable)
* brand_id FK -> brands.id (nullable)
* country_id FK -> countries.id (nullable)
* region_id FK -> regions.id (nullable)
* grape_id FK -> grapes.id (nullable)
* description longtext (rich editor)
* volume_ml int nullable (dung tích chai)
* alcohol_percent decimal(5,2) nullable (nồng độ cồn)
* price decimal(15,2) default 0           // giá bán hiện tại
* original_price decimal(15,2) default 0   // giá gốc để show giảm giá
* active enum('true','false') default 'true'
* order int default 0
* meta_title nullable
* meta_description nullable
* meta_keywords nullable
* created_at, updated_at

Quy tắc FE hiển thị giá sản phẩm:

* Nếu `price > 0`:
  * Hiển thị `price` theo VND.
  * Nếu `original_price > price` thì hiển thị cả original_price bị gạch (giá khuyến mãi).
* Nếu `price = 0`:
  * Hiển thị chữ "Liên hệ".

Index gợi ý (performance filter trang danh sách sản phẩm):

* INDEX brand_id, country_id, region_id, grape_id, type_id, product_category_id
* INDEX alcohol_percent, volume_ml, price

Ảnh sản phẩm:

* morphMany(images) với `model_type='Product'`.
* Ảnh có `order = 0` là ảnh chính dùng ở listing, hero card, FavouriteProducts, CollectionShowcase...

Slug sản phẩm:

* Slug auto-generate từ name.
* Khi admin đổi name thì slug cũng đổi theo name mới.

---

## 6. Bài viết / EditorialSpotlight

### articles

* id
* title
* slug (unique trong bảng)
* content longtext (rich editor)
* active enum('true','false') default 'true'  // nếu false thì không show ra FE
* meta_title nullable
* meta_description nullable
* meta_keywords nullable
* created_at, updated_at

Ảnh cover bài viết:

* morphMany(images) với `model_type='Article'`.
* Ảnh `order=0` là thumbnail khi show trong `EditorialSpotlight`.

Không cần `excerpt` / mô tả ngắn. Bài viết chỉ là phần phụ phục vụ brand/SEO.

---

## 7. Menu điều hướng (normal / mega)

### menus (mục cấp 1 trên header)

* id
* title (VD: "RƯỢU VANG", "RƯỢU MẠNH", "SẢN PHẨM KHÁC")
* type enum('normal','mega')
* href nullable (dùng cho menu thường bấm là đi thẳng)
* order int default 0
* active enum('true','false')
* created_at, updated_at

### menu_blocks (cột trong mega menu)

* id
* menu_id FK -> menus.id
* title (VD: "THEO LOẠI RƯỢU", "THEO QUỐC GIA")
* order int default 0
* active enum('true','false')
* created_at, updated_at

### menu_block_items (link con trong từng block)

* id
* menu_block_id FK -> menu_blocks.id
* label (VD: "PHÁP", "Ý", "CABERNET SAUVIGNON")
* href (VD: "/san-pham?country=france")
* badge nullable (VD: "HOT") // chỉ là text ngắn, không cần style riêng
* order int default 0
* active enum('true','false')
* created_at, updated_at

Mega menu logic:

* Mega menu có nhiều `menu_blocks`, mỗi block có nhiều `menu_block_items`.
* Menu thường (`type='normal'`) có thể chỉ cần `href`.

---

## 8. Homepage components (layout trang chủ)

Use case: Admin có thể reorder, thêm nhiều block cùng type, tắt/mở block. Áp dụng cho TRANG CHỦ.

### home_components

* id
* type enum(

  'HeroCarousel',          // slider hero đầu trang

  'DualBanner',            // 2 banner song song

  'CategoryGrid',          // lưới danh mục nổi bật

  'FavouriteProducts',     // danh sách sp nổi bật cuộn ngang

  'BrandShowcase',         // carousel logo thương hiệu

  'CollectionShowcase',    // block bộ sưu tập (vang / mạnh)

  'EditorialSpotlight'     // block bài viết

  )
* config_json longtext

  * HeroCarousel: {"slides":[{"image_id":1,"alt":"..."}, ...]}
  * DualBanner: {"banners":[{"image_id":1,"alt":"...","href":"/abc"},{...}]}
  * CategoryGrid: {"categories":[{"name":"Vang đỏ","image_id":1,"href":"/..."}, ...]}
  * FavouriteProducts: {"products":[{"product_id":1,"badge":"Sale"}, ...]}
  * BrandShowcase: {"brands":[{"brand_id":1,"href":"/..."}, ...]}
  * CollectionShowcase: {

    "title":"Rượu Vang",

    "subtitle":"...",

    "description":"...",

    "ctaLabel":"Xem thêm",

    "ctaHref":"/ruou-vang",

    "tone":"wine" | "spirit",

    "products":[{"product_id":1,"badge":"HOT"}, ...]

    }
  * EditorialSpotlight: {"title":"Cẩm nang rượu","articles":[{"article_id":1}, ...]}
* order int default 0
* active enum('true','false')
* created_at, updated_at

Quy tắc render FE:

* FE hiển thị tất cả block `home_components.active='true'` theo `created_at DESC`, hoặc nếu muốn custom ưu tiên tay thì admin chỉnh `order` và FE có thể sort `order ASC, created_at DESC`.
* Có thể có nhiều block cùng `type` (ví dụ 2 lần CollectionShowcase).
* Không có block nào bắt buộc chỉ tồn tại 1 record: admin tự do.
* Nếu một block tham chiếu sản phẩm/bài viết inactive, FE sẽ bỏ qua item đó (ẩn từng item) thay vì render link 404.

---

## 9. Tracking / Analytics

### visitors

* id
* ip_address
* user_agent
* device (desktop/mobile/tablet/other)
* country nullable
* first_seen_at datetime
* last_seen_at datetime
* created_at, updated_at

> 1 visitor ~ 1 client duy nhất theo fingerprint FE (localStorage token). Dùng để đếm tổng khách khác nhau từng ghé site.

### visitor_sessions

* id
* visitor_id FK -> visitors.id
* session_key (string) // random cho mỗi phiên truy cập (ví dụ mỗi lần mở site mới)
* start_time datetime
* end_time datetime nullable
* pages_viewed int default 0
* created_at, updated_at

### tracking_events

* id
* visitor_id FK -> visitors.id
* visitor_session_id FK -> visitor_sessions.id
* event_type enum('page_view','click','scroll','contact_view','other')
* page_url
* product_id FK -> products.id nullable  // để đếm top sản phẩm xem nhiều nhất
* country_snapshot string nullable        // country tại thời điểm event để đếm top quốc gia traffic
* data_json longtext nullable
* created_at

Retention:

* Có thể cron xoá `tracking_events` >90 ngày nếu cần giảm size.
* `visitors` và `visitor_sessions` không xoá để còn số tổng lifetime.

---

## 10. Quan hệ chính tóm tắt

* users 1-n articles
* users 1-n audit_logs
* settings 1-n images (logo, favicon)
* social_links 1-1 icon image (qua images)
* product_categories 1-n products
* product_types 1-n products
* brands 1-n products
* countries 1-n regions
* regions 1-n products
* grapes 1-n products
* products 1-n images (morphMany)
* articles 1-n images (morphMany)
* menus 1-n menu_blocks 1-n menu_block_items
* home_components dùng images/products/articles gián tiếp qua `config_json`
* visitors 1-n visitor_sessions 1-n tracking_events
* products 1-n tracking_events (page_view gắn vào product_id)

---

## 11. Câu hỏi QA senior cần khách chốt (bản cập nhật)

1. Giống nho / vùng sản xuất:
   * Mỗi chai rượu có thể có nhiều giống nho (blend) và/hoặc nhiều vùng/vùng phụ không? Hay business chỉ cần chọn 1 giống nho + 1 vùng chính hiển thị marketing? (Nếu cần nhiều -> tạo thêm bảng `product_grapes` & `product_regions` ngay từ đầu.)
2. Dữ liệu filter sản phẩm:
   * Khách hàng khi lọc sản phẩm (trang filter) họ có muốn lọc theo nhiều giá trị cùng lúc không? Ví dụ chọn 2 giống nho một lúc. Nếu CÓ thì backend sau này phải hỗ trợ `whereIn`, và UI filter sẽ cần multi-select.
3. Chính sách đổi slug:
   * Slug sẽ auto đổi khi đổi tên sản phẩm / bài viết. FE có cần redirect slug cũ -> slug mới không, hay chấp nhận link cũ bị 404? (nếu cần redirect thì backend phải lưu lịch sử slug cũ ở 1 bảng nữa.)
4. Hình ảnh:
   * Admin có phải luôn upload ít nhất 1 ảnh (order=0) cho sản phẩm và bài viết không? Nếu thiếu ảnh thì FE hiển thị placeholder hay ẩn block đó?
5. FavouriteProducts và CollectionShowcase:
   * Business có muốn hiển thị badge kiểu "SALE", "HOT"… Bạn có muốn quy ước danh sách badge cố định (enum) hay cho phép gõ text tự do? (Hiện đang để text tự do.)
6. Giá khuyến mãi:
   * Khi có `original_price` > `price`, frontend sẽ hiển thị giá gạch và phần trăm giảm hay chỉ giá gạch? Nếu cần % giảm thì FE phải tự tính hay backend trả sẵn?
7. Analytics / báo cáo:
   * Admin có dashboard cần xem:
     * Top sản phẩm được xem nhiều nhất (theo `tracking_events.product_id`).
     * Top quốc gia truy cập (theo `tracking_events.country_snapshot`).
   * Cần thêm thống kê theo khoảng thời gian (7 ngày / 30 ngày / all time) không? Nếu có thì backend cần query theo `created_at`.
8. Ẩn sản phẩm / bài viết:
   * Khi `active='false'`, FE sẽ tự động bỏ qua mục đó trong mọi block homepage. Xác nhận là đúng mong muốn (tức là nếu admin tắt 1 sản phẩm hot thì block FavouriteProducts có thể tự ít item đi mà không báo lỗi).
9. Mega menu:
   * Admin có muốn tự thêm block trong mega menu để đẩy marketing tạm thời (ví dụ block "Ưu đãi Tết") không? Nếu có thì `menu_blocks` có thể chứa bất kỳ title + list link custom, không chỉ quốc gia / giống nho.
10. Audit log:
    * Có cần xem audit_logs trong admin UI (Filament) để biết ai đã chỉnh cái gì gần đây không? Nếu có thì sẽ build thêm Resource readonly.

--> Khi khách confirm 10 câu trên, migration và Filament Resource có thể được code ổn định không cần thay đổi schema nữa.
