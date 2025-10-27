# `/home` – API trang chủ

## 1. `GET /api/v1/home`

- Không nhận tham số.
- Trả về danh sách component đang `active`, sắp theo `order ASC`, sau đó `id ASC`.
- Component không đủ dữ liệu (ví dụ sản phẩm tham chiếu đã inactive) sẽ bị loại bỏ khỏi payload và ghi log cảnh báo.

```json
{
  "data": [
    {
      "id": 1,
      "type": "HeroCarousel",
      "order": 0,
      "config": {
        "slides": [
          {
            "image": {
              "id": 701,
              "url": "https://cdn.example.com/images/hero-1.jpg",
              "alt": "Banner hè",
              "width": 1920,
              "height": 900
            },
            "href": "/san-pham/chateau-margaux-2015"
          }
        ]
      },
      "updated_at": "2025-10-27T09:00:00+07:00"
    }
  ]
}
```

### Cấu hình theo `type`

| Type | Cấu trúc `config` |
| --- | --- |
| `HeroCarousel` | `slides[]` (mỗi item: `image` object + `href` optional). |
| `DualBanner` | `banners[]` (mỗi item: `title?`, `subtitle?`, `href?`, `badge?`, `image`). |
| `CategoryGrid` | `categories[]` (mỗi item: `name`, `href?`, `description?`, `image`). |
| `FavouriteProducts` | `title?`, `subtitle?`, `products[]`.<br>`products[]` gồm `product` (tóm tắt), `badge?`, `href`. |
| `CollectionShowcase` | Thông tin khối (`title`, `subtitle?`, `description?`, `ctaLabel?`, `ctaHref?`, `tone?`) + `products[]` (như trên). |
| `EditorialSpotlight` | `title?`, `subtitle?`, `articles[]` gồm `article` (tóm tắt), `href`. |
| `BrandShowcase` | `title?`, `subtitle?`, `brands[]` gồm `term` (chi tiết kèm group), `href?`, `badge?`. |
| `*` khác | Payload trả nguyên `config` từ DB (không biến đổi). |

### Định nghĩa đối tượng tái sử dụng

| Đối tượng | Thuộc tính |
| --- | --- |
| `image` | `id`, `url`, `alt`, `width`, `height` |
| `product` | `id`, `name`, `slug`, `price`, `original_price`, `discount_percent`, `show_contact_cta`, `cover_image_url` |
| `article` | `id`, `title`, `slug`, `excerpt`, `cover_image_url`, `published_at` (ISO 8601) |
| `term` | `id`, `name`, `slug`, `group` (`id`, `code`, `name`), `icon_type`, `icon_value` |

Mã lỗi: `500`. (Không có 404 vì endpoint luôn trả danh sách – có thể rỗng).
