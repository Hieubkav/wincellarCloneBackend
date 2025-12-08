<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hướng Dẫn - Thiên Kim Wine</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,600,700" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --noir: #1C1C1C; --amber: #ECAA4D; --wine: #9B2C3B; }
        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--noir);
            background: #FFF;
            line-height: 1.6;
        }
        header {
            background: var(--noir);
            color: #FFF;
            padding: 1rem 2rem;
            border-bottom: 3px solid var(--wine);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size: 1.2rem; font-weight: 700; text-decoration: none; color: #FFF; }
        .nav-links { display: flex; gap: 1rem; font-size: 0.8rem; }
        .nav-links a {
            color: #FFF;
            text-decoration: none;
            padding: 0.3rem 0.6rem;
            border-radius: 3px;
            transition: background 0.2s;
        }
        .nav-links a:hover { background: rgba(155,44,59,0.3); }
        .nav-links a.active { background: var(--wine); }
        .layout {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 0;
            min-height: calc(100vh - 100px);
        }
        .sidebar {
            background: #F5F5F5;
            border-right: 2px solid #DDD;
            position: sticky;
            top: 57px;
            height: calc(100vh - 57px);
            overflow-y: auto;
        }
        .sidebar-title {
            padding: 0.8rem;
            font-size: 0.85rem;
            font-weight: 700;
            background: var(--wine);
            color: #FFF;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        .sidebar-menu li {
            border-bottom: 1px solid #DDD;
        }
        .sidebar-menu a {
            display: block;
            padding: 0.6rem 1rem;
            text-decoration: none;
            color: var(--noir);
            font-size: 0.75rem;
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }
        .sidebar-menu a:hover {
            background: #FFF;
            border-left-color: var(--wine);
        }
        .sidebar-menu a.active {
            background: #FFF;
            border-left-color: var(--wine);
            color: var(--wine);
            font-weight: 700;
        }
        .content {
            padding: 1.5rem;
            background: #FFF;
        }
        .content-header {
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 3px solid var(--wine);
        }
        .content-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }
        .content-header p {
            font-size: 0.8rem;
            color: #666;
        }
        section {
            margin-bottom: 2rem;
        }
        section h2 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            color: var(--noir);
        }
        section h3 {
            font-size: 1rem;
            font-weight: 600;
            margin: 1.2rem 0 0.6rem 0;
            color: var(--noir);
        }
        section p {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.6rem;
            line-height: 1.6;
        }
        .steps {
            counter-reset: step;
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }
        .steps li {
            counter-increment: step;
            margin-bottom: 1rem;
            padding-left: 2.5rem;
            position: relative;
            font-size: 0.85rem;
        }
        .steps li:before {
            content: counter(step);
            position: absolute;
            left: 0;
            top: 0;
            width: 1.8rem;
            height: 1.8rem;
            background: var(--wine);
            color: #FFF;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .steps li strong {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 0.2rem;
        }
        .steps li p {
            margin: 0;
            font-size: 0.8rem;
            color: #666;
        }
        .note {
            background: rgba(236,170,77,0.1);
            border-left: 4px solid var(--amber);
            padding: 0.8rem;
            margin: 1rem 0;
            font-size: 0.85rem;
        }
        .note strong {
            color: var(--amber);
        }
        .path {
            background: #F5F5F5;
            border: 1px solid #DDD;
            padding: 0.8rem;
            margin: 0.8rem 0;
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            overflow-x: auto;
        }
        .code {
            background: var(--noir);
            color: #E0E0E0;
            padding: 0.8rem;
            margin: 0.8rem 0;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            line-height: 1.4;
            overflow-x: auto;
        }
        ul, ol {
            margin-left: 1rem;
            font-size: 0.85rem;
            color: #666;
        }
        ul li, ol li {
            margin-bottom: 0.3rem;
        }
        footer {
            background: var(--noir);
            color: #FFF;
            padding: 1rem 2rem;
            text-align: center;
            border-top: 3px solid var(--wine);
        }
        footer p {
            font-size: 0.75rem;
            margin: 0.2rem 0;
        }
        footer a {
            color: var(--wine);
            text-decoration: none;
        }
        @media (max-width: 1024px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar { position: relative; height: auto; top: 0; border-right: none; border-bottom: 2px solid #DDD; }
        }
        @media (max-width: 768px) {
            .header-content { flex-direction: column; gap: 0.5rem; }
            .nav-links { flex-wrap: wrap; justify-content: center; gap: 0.4rem; }
            .content { padding: 1rem; }
            .content-header h1 { font-size: 1.3rem; }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="/" class="logo">Thiên Kim Wine</a>
            <nav class="nav-links">
                <a href="/tong-quan">Tổng quan</a>
                <a href="/huong-dan" class="active">Hướng dẫn</a>
                <a href="/tinh-nang">Tính năng</a>
                <a href="/api-docs">API</a>
                <a href="/admin">Admin</a>
            </nav>
        </div>
    </header>

    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-title">Nội Dung</div>
            <ul class="sidebar-menu">
                <li><a href="#start" class="active">1. Bắt Đầu</a></li>
                <li><a href="#admin">2. Admin Panel</a></li>
                <li><a href="#product">3. Sản Phẩm</a></li>
                <li><a href="#taxonomy">4. Taxonomy</a></li>
                <li><a href="#article">5. Bài Viết</a></li>
                <li><a href="#menu">6. Menu</a></li>
                <li><a href="#settings">7. Settings</a></li>
                <li><a href="#seo">8. SEO</a></li>
                <li><a href="#tracking">9. Tracking</a></li>
            </ul>
        </aside>

        <main class="content">
            <div class="content-header">
                <h1>Hướng Dẫn Sử Dụng</h1>
                <p>Cách sử dụng Admin Panel quản lý rượu vang</p>
            </div>

            <section id="start">
                <h2>1. Bắt Đầu</h2>
                
                <h3>Kiến Trúc Hệ Thống</h3>
                <p>Hệ thống tách biệt Frontend-Backend:</p>
                <ul>
                    <li><strong>Frontend:</strong> Next.js 15 - Website khách hàng xem (port 3000)</li>
                    <li><strong>Backend API:</strong> Laravel 12 - API endpoints (port 8000)</li>
                    <li><strong>Admin Panel:</strong> Filament 4 - Quản lý tại http://127.0.0.1:8000/admin</li>
                </ul>

                <h3>Truy Cập Hệ Thống</h3>
                <div class="path">
                    <strong>Backend Admin:</strong> http://127.0.0.1:8000/admin<br>
                    <strong>Frontend Website:</strong> http://localhost:3000<br>
                    <strong>API Endpoints:</strong> http://127.0.0.1:8000/api/v1
                </div>

                <h3>Đăng Nhập Admin</h3>
                <ol class="steps">
                    <li>
                        <strong>Mở trình duyệt</strong>
                        <p>Vào http://127.0.0.1:8000/admin</p>
                    </li>
                    <li>
                        <strong>Nhập email & password</strong>
                        <p>Sử dụng tài khoản admin (default: admin@example.com)</p>
                    </li>
                    <li>
                        <strong>Click "Đăng nhập"</strong>
                        <p>Vào Dashboard</p>
                    </li>
                </ol>

                <h3>Khởi Động Hệ Thống</h3>
                <div class="code">cd wincellarcloneBackend && php artisan serve
cd ../wincellarcloneFrontEnd && npm run dev</div>
                <p>Backend chạy port 8000, Frontend port 3000</p>
            </section>

            <section id="admin">
                <h2>2. Admin Panel (Filament)</h2>
                <p>Dashboard hiển thị: Tổng sản phẩm, bài viết, visitor, top products, top articles.</p>
                <p>Sidebar menu: Sản phẩm, Bài viết, Taxonomy, Menu, Settings, Tracking, Social Links.</p>
            </section>

            <section id="product">
                <h2>3. Sản Phẩm</h2>
                
                <h3>Thêm Sản Phẩm</h3>
                <ol class="steps">
                    <li><strong>Menu Sản Phẩm → Thêm Mới</strong></li>
                    <li><strong>Điền tên sản phẩm</strong><p>VD: "Rượu Vang Đỏ Bordeaux Château Margaux 2015"</p></li>
                    <li><strong>Nhập giá & giá gốc</strong><p>Để tính % giảm auto</p></li>
                    <li><strong>Upload ảnh</strong><p>Ảnh chính + gallery (kéo thả)</p></li>
                    <li><strong>Điền độ cồn, vintage, dung tích</strong></li>
                    <li><strong>Chọn phân loại</strong><p>Brand, origin, wine type, grapes</p></li>
                    <li><strong>Nhập mô tả & SEO</strong></li>
                    <li><strong>Click "Lưu"</strong></li>
                </ol>

                <h3>Các Trường Quan Trọng</h3>
                <ul>
                    <li><strong>Tên (Bắt buộc):</strong> Tên rượu đầy đủ</li>
                    <li><strong>Slug:</strong> Auto tạo từ tên (dùng cho URL)</li>
                    <li><strong>Giá:</strong> Giá hiện tại VND</li>
                    <li><strong>Giá Gốc:</strong> Giá cũ (nếu sale)</li>
                    <li><strong>Vintage:</strong> Năm sản xuất (VD: 2015)</li>
                </ul>
            </section>

            <section id="taxonomy">
                <h2>4. Taxonomy (Phân Loại)</h2>
                <p>Taxonomy giúp phân loại sản phẩm theo nhiều tiêu chí để dễ tìm kiếm.</p>
                
                <h3>Các Taxonomy</h3>
                <ul>
                    <li><strong>Brands:</strong> Thương hiệu (Château Margaux, Penfolds...)</li>
                    <li><strong>Origin > Country:</strong> Quốc gia (Pháp, Ý, Chile...)</li>
                    <li><strong>Origin > Region:</strong> Vùng (Bordeaux, Tuscany...)</li>
                    <li><strong>Wine Types:</strong> Loại (Đỏ, Trắng, Rosé, Sparkling)</li>
                    <li><strong>Grapes:</strong> Giống nho (Cabernet, Merlot, Chardonnay...)</li>
                </ul>

                <h3>Thêm Term Mới</h3>
                <ol class="steps">
                    <li><strong>Menu Taxonomy → chọn loại (VD: Brands)</strong></li>
                    <li><strong>Click "Thêm Mới"</strong></li>
                    <li><strong>Nhập tên term</strong><p>VD: "Château Latour"</p></li>
                    <li><strong>Upload logo/icon (tuỳ chọn)</strong></li>
                    <li><strong>Click "Lưu"</strong></li>
                </ol>
            </section>

            <section id="article">
                <h2>5. Bài Viết</h2>
                
                <h3>Thêm Bài Viết</h3>
                <ol class="steps">
                    <li><strong>Menu Bài Viết → Thêm Mới</strong></li>
                    <li><strong>Nhập tiêu đề</strong><p>VD: "Cách Chọn Rượu Vang Cho Bữa Tiệc"</p></li>
                    <li><strong>Upload ảnh bìa</strong></li>
                    <li><strong>Viết nội dung</strong><p>Dùng Rich Text Editor (giống Word)</p></li>
                    <li><strong>Thêm gallery (tuỳ chọn)</strong></li>
                    <li><strong>Cài đặt SEO</strong><p>Meta title, description</p></li>
                    <li><strong>Click "Xuất Bản"</strong></li>
                </ol>
            </section>

            <section id="menu">
                <h2>6. Menu Builder</h2>
                <p>Tạo menu navigation (header, footer) kéo thả không cần code.</p>
                
                <h3>Cấu Trúc Menu</h3>
                <ul>
                    <li><strong>Menu Container:</strong> VD "Main Navigation"</li>
                    <li><strong>Blocks:</strong> Nhóm items (VD "Sản Phẩm")</li>
                    <li><strong>Items:</strong> Link cụ thể (VD "Rượu Vang Đỏ")</li>
                </ul>

                <h3>Tạo Menu</h3>
                <ol class="steps">
                    <li><strong>Menu → Thêm Mới</strong></li>
                    <li><strong>Đặt tên "Main Navigation"</strong></li>
                    <li><strong>Thêm Blocks</strong><p>Click "Add Block", nhập tên</p></li>
                    <li><strong>Thêm Items vào Block</strong><p>Chọn Term hoặc custom URL</p></li>
                    <li><strong>Kéo thả sắp xếp</strong></li>
                    <li><strong>Lưu</strong></li>
                </ol>
            </section>

            <section id="settings">
                <h2>7. Settings (Cài Đặt)</h2>
                <p>Quản lý thông tin chung của website: logo, hotline, email, địa chỉ, SEO defaults.</p>
                
                <h3>Các Cài Đặt Quan Trọng</h3>
                <ul>
                    <li><strong>Site Name:</strong> Tên website (VD: "Thiên Kim Wine")</li>
                    <li><strong>Logo & Favicon:</strong> Upload ảnh</li>
                    <li><strong>Hotline, Email, Địa chỉ, Giờ mở cửa</strong></li>
                    <li><strong>Google Maps Embed:</strong> Dán iframe từ Google Maps</li>
                    <li><strong>Meta Defaults:</strong> Title, description, keywords cho SEO</li>
                </ul>
            </section>

            <section id="seo">
                <h2>8. SEO & Meta Tags</h2>
                <p>SEO giúp website xuất hiện trên Google khi khách tìm "rượu vang", "wine shop"...</p>
                
                <h3>Cài Đặt SEO Cho Sản Phẩm</h3>
                <ol class="steps">
                    <li><strong>Vào sản phẩm cần chỉnh</strong></li>
                    <li><strong>Cuộn đến phần "SEO Settings"</strong></li>
                    <li><strong>Điền Meta Title</strong><p>VD: "Rượu Vang Đỏ Bordeaux 2015 - Thiên Kim Wine"</p></li>
                    <li><strong>Điền Meta Description</strong><p>VD: "Rượu vang Pháp cao cấp, độ cồn 13.5%, giá 2.5M. Giao hàng toàn quốc"</p></li>
                    <li><strong>Thêm Keywords</strong><p>VD: "rượu vang đỏ, bordeaux, pháp"</p></li>
                    <li><strong>Lưu</strong></li>
                </ol>

                <div class="note">
                    <strong>Mẹo:</strong> Viết mô tả tự nhiên cho người đọc, không spam từ khóa. Google thích nội dung hữu ích, không spam.
                </div>
            </section>

            <section id="tracking">
                <h2>9. Tracking & Analytics</h2>
                <p>Xem có bao nhiêu người vào website, sản phẩm nào hot, bài viết nào phổ biến.</p>
                
                <h3>Xem Thống Kê</h3>
                <ol class="steps">
                    <li><strong>Vào Dashboard</strong></li>
                    <li><strong>Xem widget "Visitors Today"</strong><p>Số người hôm nay</p></li>
                    <li><strong>Xem "Top Products"</strong><p>Sản phẩm xem nhiều</p></li>
                    <li><strong>Xem "Popular Articles"</strong><p>Bài viết đọc nhiều</p></li>
                </ol>

                <h3>Dữ Liệu Thu Thập</h3>
                <ul>
                    <li>Số lượt truy cập (không lưu IP, email)</li>
                    <li>Sản phẩm được xem (tracking)</li>
                    <li>Bài viết được đọc</li>
                    <li>Click "Liên hệ" (CTA tracking)</li>
                </ul>
            </section>
        </main>
    </div>

    <footer>
        <p><strong>Thiên Kim Wine - Developer Portal</strong></p>
        <p><a href="/tong-quan">Tổng quan</a> | <a href="/huong-dan">Hướng dẫn</a> | <a href="/tinh-nang">Tính năng</a> | <a href="/api-docs">API</a> | <a href="/admin">Admin</a></p>
        <p style="color: #999; margin-top: 0.6rem;">© 2025 Thiên Kim Wine. All rights reserved.</p>
    </footer>

    <script>
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const target = document.querySelector(targetId);
                if (target) {
                    document.querySelectorAll('.sidebar-menu a').forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
