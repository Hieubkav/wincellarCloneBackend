# API v1

- **Base URL:** `/api/v1`
- **Content type:** `application/json; charset=utf-8`
- **Rate limit:** 60 request/phút/IP (theo `RateLimiter::for('api')`)
- **Phiên bản tài liệu:** cập nhật ngày 27/10/2025

| Tài liệu chi tiết | Chức năng |
| --- | --- |
| [products.md](products.md) | Danh sách & chi tiết sản phẩm (`/san-pham`) |
| [articles.md](articles.md) | Bài viết (`/bai-viet`) |
| [home.md](home.md) | Cấu phần trang chủ (`/home`) |

> Ghi chú: Tất cả ví dụ dưới đây chỉ minh họa cấu trúc dữ liệu. FE/QA nên kiểm tra thêm bằng seed data thực tế để xác nhận giá trị cụ thể.
