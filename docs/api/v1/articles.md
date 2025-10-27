# `/bai-viet` – API bài viết

## 1. `GET /api/v1/bai-viet`

| Tham số | Mô tả |
| --- | --- |
| `page` | Trang (mặc định 1) |
| `per_page` | Số item mỗi trang (mặc định 12, tối đa 50) |
| `sort` | `-created_at` (mặc định) \| `created_at` \| `title` \| `-title` |

Phản hồi `200 OK`:

```json
{
  "data": [
    {
      "id": 501,
      "title": "Cách bảo quản rượu vang tại nhà",
      "slug": "cach-bao-quan-ruou-vang-tai-nha",
      "excerpt": "Những nguyên tắc cơ bản giúp giữ chất lượng rượu...",
      "cover_image_url": "https://cdn.example.com/articles/501/cover.jpg",
      "published_at": "2025-10-01T12:30:22+07:00"
    }
  ],
  "meta": {
    "page": 1,
    "per_page": 12,
    "total": 64,
    "sort": "-created_at"
  }
}
```

| Trường | Ghi chú |
| --- | --- |
| `cover_image_url` | Dựa trên cover (`order = 0`) hoặc placeholder `media.placeholders.article` |
| `published_at` | ISO 8601, lấy từ `created_at` |

Lỗi: `400`, `422`, `500`.

---

## 2. `GET /api/v1/bai-viet/{slug}`

Phản hồi `200 OK`:

```json
{
  "data": {
    "id": 501,
    "title": "Cách bảo quản rượu vang tại nhà",
    "slug": "cach-bao-quan-ruou-vang-tai-nha",
    "excerpt": "Những nguyên tắc cơ bản giúp giữ chất lượng rượu...",
    "content": "<p>...</p>",
    "cover_image_url": "https://cdn.example.com/articles/501/cover.jpg",
    "gallery": [
      {
        "id": 990,
        "url": "https://cdn.example.com/articles/501/gallery-1.jpg",
        "alt": "Wine cellar",
        "order": 1,
        "width": 1280,
        "height": 720
      }
    ],
    "author": {
      "id": 2,
      "name": "Nguyễn Văn A"
    },
    "published_at": "2025-10-01T12:30:22+07:00",
    "meta": {
      "title": "Cách bảo quản rượu vang tại nhà - Wincellar",
      "description": "Seo description..."
    }
  }
}
```

| Trường | Ghi chú |
| --- | --- |
| `gallery` | Toàn bộ ảnh của bài viết, đã sắp `order ASC` |
| `author` | Null khi không gán `author_id` |
| `meta` | Giữ nguyên `meta_title/meta_description` (có thể null) |

Lỗi: `404` nếu slug không tồn tại hoặc bài viết `inactive`; `500`.
