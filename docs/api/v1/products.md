# `/san-pham` – API sản phẩm

## 1. `GET /api/v1/san-pham`

| Thuộc tính | Mô tả |
| --- | --- |
| `terms[brand][]` | ID term brand (array int) |
| `terms[origin][country][]` | ID term quốc gia (array int) |
| `terms[origin][region][]` | ID term vùng (array int) |
| `terms[grape][]` | ID term giống nho (array int) |
| `type[]` | ID `product_types` |
| `price_min`, `price_max` | Giá (VND) – int ≥0 |
| `alcohol_min`, `alcohol_max` | Độ cồn – float 0–100 |
| `page` | Trang (mặc định 1) |
| `per_page` | Số bản ghi mỗi trang (mặc định 24, tối đa 60) |
| `sort` | Một trong: `-created_at` (mặc định) \| `created_at` \| `price` \| `-price` \| `name` \| `-name` |

Phản hồi `200 OK`:

```json
{
  "data": [
    {
      "id": 123,
      "name": "Château Margaux 2015",
      "slug": "chateau-margaux-2015",
      "price": 12990000,
      "original_price": 14990000,
      "discount_percent": 13,
      "show_contact_cta": false,
      "main_image_url": "https://cdn.example.com/products/123/cover.jpg",
      "gallery": [
        {
          "id": 777,
          "url": "https://cdn.example.com/products/123/gallery-1.jpg",
          "alt": "Château Margaux 2015 bottle",
          "order": 1,
          "width": 1200,
          "height": 1600
        }
      ],
      "brand_term": {
        "id": 12,
        "name": "Château Margaux",
        "slug": "chateau-margaux"
      },
      "country_term": {
        "id": 31,
        "name": "Pháp",
        "slug": "france"
      },
      "alcohol_percent": 13.5,
      "volume_ml": 750,
      "badges": ["HOT", "LIMITED"]
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 24,
    "total": 230,
    "sort": "-created_at"
  }
}
```

| Trường | Ghi chú |
| --- | --- |
| `main_image_url` | Ưu tiên cover (`images.order = 0`); fallback placeholder `config/media.php` |
| `gallery` | Danh sách ảnh đã sắp `order ASC`; chỉ gồm ảnh `active` |
| `discount_percent` | Trả `null` nếu không giảm giá; đã làm tròn theo rule BE |
| `show_contact_cta` | `true` nếu giá = 0 |

Mã lỗi: `400` (tham số sai format), `422` (validate fail), `500`.

---

## 2. `GET /api/v1/san-pham/{slug}`

Trả 404 nếu slug không tồn tại hoặc sản phẩm `inactive`.

```json
{
  "data": {
    "id": 123,
    "name": "Château Margaux 2015",
    "slug": "chateau-margaux-2015",
    "description": "Ghi chú tasting...",
    "price": 12990000,
    "original_price": 14990000,
    "discount_percent": 13,
    "show_contact_cta": false,
    "cover_image_url": "https://cdn.example.com/products/123/cover.jpg",
    "gallery": [
      {
        "id": 777,
        "url": "https://cdn.example.com/products/123/gallery-1.jpg",
        "alt": "Château Margaux 2015 bottle",
        "order": 1,
        "width": 1200,
        "height": 1600
      }
    ],
    "brand_term": {
      "id": 12,
      "name": "Château Margaux",
      "slug": "chateau-margaux"
    },
    "country_term": {
      "id": 31,
      "name": "Pháp",
      "slug": "france"
    },
    "grape_terms": [
      {
        "id": 91,
        "name": "Cabernet Sauvignon",
        "slug": "cabernet-sauvignon"
      }
    ],
    "origin_terms": [
      {
        "id": 45,
        "name": "Bordeaux",
        "slug": "bordeaux"
      }
    ],
    "alcohol_percent": 13.5,
    "volume_ml": 750,
    "badges": ["HOT", "LIMITED"],
    "category": {
      "id": 4,
      "name": "Rượu vang đỏ",
      "slug": "ruou-vang-do"
    },
    "type": {
      "id": 2,
      "name": "Wine",
      "slug": "wine"
    },
    "breadcrumbs": [
      {
        "label": "Rượu vang đỏ",
        "href": "/san-pham/ruou-vang-do"
      },
      {
        "label": "Wine",
        "href": "/san-pham?type=wine"
      },
      {
        "label": "Château Margaux",
        "href": "/san-pham?brand=chateau-margaux"
      }
    ],
    "meta": {
      "title": "Château Margaux 2015 - Wincellar",
      "description": "Ghi chú SEO..."
    }
  }
}
```

| Trường | Ghi chú |
| --- | --- |
| `grape_terms`, `origin_terms` | Tập term cùng group, đã lọc inactive |
| `breadcrumbs` | Chỉ gồm category, type, brand nếu tồn tại |
| `meta` | BE trả nguyên giá trị meta_title/meta_description (có thể `null`) |

Mã lỗi: `404` (không tồn tại/inactive), `500`.
