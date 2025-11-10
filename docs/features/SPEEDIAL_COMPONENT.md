# Speed Dial Component - Hướng dẫn sử dụng

**Ngày tạo:** 2025-11-10  
**Component Type:** `speed_dial`

## 📖 Tổng quan

Speed Dial là thanh liên hệ nhanh hiển thị ở:
- **Desktop**: Góc phải màn hình (fixed position)
- **Mobile**: Bottom navigation bar

Cho phép khách hàng truy cập nhanh các kênh liên hệ: Hotline, Zalo, Messenger, v.v.

---

## 🎯 Tính năng

### Backend
- ✅ Enum type: `SpeedDial` trong `HomeComponentType`
- ✅ Form builder: Dynamic form với các trường:
  - Icon type (home, phone, zalo, messenger, custom)
  - Custom icon upload
  - Label hiển thị
  - Link đến (tel:, https://)
  - Target (_self, _blank)
- ✅ API Transformer: `SpeedDialTransformer`
- ✅ Integrated vào `HomeComponentAssembler`

### Frontend
- ✅ TypeScript types: `SpeedDialConfig`, `SpeedDialItem`
- ✅ Adapter: `adaptSpeedDialProps()`
- ✅ Component: `Speedial.tsx` với responsive design
- ✅ Fallback: Nếu không có data từ API, dùng default hardcoded

---

## 🚀 Cách sử dụng

### 1. Tạo Speed Dial Component trong Admin

**Truy cập:**
```
http://127.0.0.1:8000/admin/home-components/create
```

**Các bước:**

1. **Chọn loại component:**
   - Loại khối giao diện: `Speed Dial - Liên hệ nhanh`

2. **Thêm các nút liên hệ:**
   
   **Ví dụ 1: Nút Trang chủ**
   - Loại icon: `Trang chủ (Home)`
   - Nhãn hiển thị: `Trang chủ`
   - Link đến: `/`
   - Cách mở link: `Cùng tab (_self)`

   **Ví dụ 2: Nút Hotline**
   - Loại icon: `Điện thoại (Phone)`
   - Nhãn hiển thị: `Hotline`
   - Link đến: `tel:0946698008`
   - Cách mở link: `Cùng tab (_self)`

   **Ví dụ 3: Nút Zalo**
   - Loại icon: `Zalo`
   - Nhãn hiển thị: `Zalo`
   - Link đến: `https://zalo.me/306009538036482403`
   - Cách mở link: `Tab mới (_blank)`

   **Ví dụ 4: Nút Messenger**
   - Loại icon: `Messenger`
   - Nhãn hiển thị: `Messenger`
   - Link đến: `https://m.me/winecellar.vn`
   - Cách mở link: `Tab mới (_blank)`

   **Ví dụ 5: Custom Icon**
   - Loại icon: `Tùy chỉnh (Custom Icon)`
   - Icon tùy chỉnh: *Chọn image từ gallery*
   - Nhãn hiển thị: `Email`
   - Link đến: `mailto:contact@example.com`
   - Cách mở link: `Cùng tab (_self)`

3. **Bật hiển thị:**
   - Đang hiển thị: `ON`

4. **Lưu lại**

---

### 2. Kiểm tra API Response

**Endpoint:**
```bash
GET http://127.0.0.1:8000/api/v1/home
```

**Response mẫu:**
```json
{
  "data": [
    {
      "id": 10,
      "type": "speed_dial",
      "order": 99,
      "config": {
        "items": [
          {
            "icon_type": "home",
            "icon_url": null,
            "label": "Trang chủ",
            "href": "/",
            "target": "_self"
          },
          {
            "icon_type": "phone",
            "icon_url": null,
            "label": "Hotline",
            "href": "tel:0946698008",
            "target": "_self"
          },
          {
            "icon_type": "zalo",
            "icon_url": null,
            "label": "Zalo",
            "href": "https://zalo.me/306009538036482403",
            "target": "_blank"
          },
          {
            "icon_type": "messenger",
            "icon_url": null,
            "label": "Messenger",
            "href": "https://m.me/winecellar.vn",
            "target": "_blank"
          }
        ]
      }
    }
  ]
}
```

---

### 3. Xem trên Frontend

**Truy cập:**
```
http://localhost:3000/
```

**Kiểm tra:**

✅ **Desktop (>= 1024px):**
- Thanh speedial ở góc phải màn hình
- Các nút xếp dọc
- Hover effect: nút di chuyển lên nhẹ
- Background: #9B2C3B (màu đỏ rượu)

✅ **Mobile (< 1024px):**
- Bottom navigation bar
- Các nút xếp ngang (grid responsive)
- Background: #9B2C3B
- Divider giữa các nút

---

## 🧪 Test Cases

### Test 1: Fallback khi không có data
```bash
# Xóa hoặc tắt speed_dial component trong admin
# Frontend vẫn hiển thị speedial với default data hardcoded
```

### Test 2: Custom icon
```bash
# Tạo nút với icon type = "custom"
# Upload 1 icon (PNG/SVG)
# Frontend hiển thị icon đó thay vì Lucide icon
```

### Test 3: Dynamic grid
```bash
# Thêm 2 items → Mobile: grid-cols-2
# Thêm 3 items → Mobile: grid-cols-3
# Thêm 4 items → Mobile: grid-cols-4
```

### Test 4: Link types
```bash
# tel: link → Gọi điện trực tiếp
# https: link + _blank → Mở tab mới
# / link + _self → Navigate trong app
```

---

## 📁 Files đã thay đổi

### Backend
```
app/Enums/HomeComponentType.php                          [UPDATED]
app/Filament/Resources/HomeComponents/Schemas/HomeComponentForm.php  [UPDATED]
app/Services/Api/V1/Home/Transformers/SpeedDialTransformer.php      [NEW]
app/Services/Api/V1/Home/HomeComponentAssembler.php                 [UPDATED]
```

### Frontend
```
lib/api/home.ts                     [UPDATED] - Added types
components/home/adapters.tsx         [UPDATED] - Added adapter
components/layouts/Speedial.tsx      [UPDATED] - Dynamic props
app/layout.tsx                       [UPDATED] - Fetch data
```

---

## 🎨 Customization

### Thay đổi màu sắc

**Backend (không cần)** - Màu do frontend quy định

**Frontend:**
```tsx
// components/layouts/Speedial.tsx
// Desktop background
className="bg-[#9B2C3B]"  // Đổi màu này

// Hover state
className="hover:bg-[#851e2b]"  // Đổi màu hover

// Mobile background
className="bg-[#9B2C3B]"
```

### Thêm icon type mới

**Backend:**
```php
// app/Filament/Resources/HomeComponents/Schemas/HomeComponentForm.php
Select::make('icon_type')
    ->options([
        'home' => 'Trang chủ (Home)',
        'phone' => 'Điện thoại (Phone)',
        'zalo' => 'Zalo',
        'messenger' => 'Messenger',
        'email' => 'Email',  // ← THÊM MỚI
        'custom' => 'Tùy chỉnh (Custom Icon)',
    ])
```

**Frontend:**
```tsx
// components/layouts/Speedial.tsx
import { Mail } from "lucide-react";

const ICON_TYPE_TO_LUCIDE: Record<string, LucideIcon> = {
  home: Home,
  phone: Phone,
  zalo: MessageSquareText,
  messenger: MessageCircle,
  email: Mail,  // ← THÊM MỚI
};
```

---

## 🐛 Troubleshooting

### Speedial không hiển thị

**Kiểm tra:**
1. Component có `active = true`?
2. API endpoint `/api/v1/home` trả về data?
3. Frontend có lỗi console?

### Icon không đúng

**Kiểm tra:**
1. `icon_type` có trong `ICON_TYPE_TO_LUCIDE`?
2. Custom icon: `icon_url` có hợp lệ?
3. Image có tồn tại trong storage?

### Không fetch được data

**Kiểm tra:**
1. `.env.local` frontend có `NEXT_PUBLIC_API_BASE_URL`?
2. Backend API có chạy (`php artisan serve`)?
3. CORS có được config đúng?

---

## 📝 Notes

- **Order field:** Dùng để sắp xếp trong list components, nhưng speedial thường ở cuối (order cao)
- **Fallback:** Nếu API fail, frontend tự động dùng default data
- **Revalidation:** Frontend cache 5 phút (300s) - xem `page.tsx`
- **Performance:** Speedial render trong layout → fetch 1 lần cho toàn site

---

**Version:** 1.0  
**Last Updated:** 2025-11-10
