<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tính Năng - Thiên Kim Wine</title>
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
            padding: 1.2rem 2rem;
            border-bottom: 3px solid var(--noir);
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
        .logo {
            font-size: 1.3rem;
            font-weight: 700;
            text-decoration: none;
            color: #FFF;
        }
        .nav-links {
            display: flex;
            gap: 1rem;
            font-size: 0.85rem;
        }
        .nav-links a {
            color: #FFF;
            text-decoration: none;
            padding: 0.4rem 0.8rem;
            border-radius: 3px;
            transition: background 0.2s;
        }
        .nav-links a:hover { background: rgba(236,170,77,0.2); }
        .nav-links a.active { background: var(--amber); color: var(--noir); }
        .layout {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 220px 1fr;
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
            padding: 1rem;
            font-size: 0.95rem;
            font-weight: 700;
            background: var(--noir);
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
            padding: 0.7rem 1.2rem;
            text-decoration: none;
            color: var(--noir);
            font-size: 0.8rem;
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }
        .sidebar-menu a:hover {
            background: #FFF;
            border-left-color: var(--amber);
        }
        .sidebar-menu a.active {
            background: #FFF;
            border-left-color: var(--amber);
            color: var(--amber);
            font-weight: 700;
        }
        .content {
            padding: 1.8rem;
            background: #FFF;
        }
        .content-header {
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 3px solid var(--noir);
        }
        .content-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
        }
        .content-header p {
            font-size: 0.85rem;
            color: #666;
        }
        section {
            margin-bottom: 2rem;
        }
        section h2 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--noir);
        }
        .summary-box {
            background: rgba(155,44,59,0.05);
            border: 2px solid var(--wine);
            padding: 1.2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .summary-box h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            color: var(--wine);
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }
        .stat {
            text-align: center;
        }
        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--wine);
            display: block;
        }
        .stat-label {
            font-size: 0.7rem;
            color: #666;
            font-weight: 600;
        }
        .feature-card {
            background: #F5F5F5;
            border: 2px solid #DDD;
            border-left: 4px solid var(--amber);
            padding: 1rem;
            margin-bottom: 0.8rem;
        }
        .feature-card h3 {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.4rem;
            color: var(--noir);
        }
        .feature-card p {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .feature-list li {
            padding: 0.2rem 0 0.2rem 1rem;
            position: relative;
            font-size: 0.8rem;
            color: #666;
        }
        .feature-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--amber);
            font-weight: 700;
        }
        footer {
            background: var(--noir);
            color: #FFF;
            padding: 1rem 2rem;
            text-align: center;
            border-top: 3px solid var(--noir);
        }
        footer p {
            font-size: 0.75rem;
            margin: 0.2rem 0;
        }
        footer a {
            color: var(--amber);
            text-decoration: none;
        }
        @media (max-width: 1024px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar { position: relative; height: auto; top: 0; border-right: none; border-bottom: 2px solid #DDD; }
            .content { padding: 1.5rem 1.2rem; }
        }
        @media (max-width: 768px) {
            .header-content { flex-direction: column; gap: 0.6rem; }
            .nav-links { flex-wrap: wrap; justify-content: center; gap: 0.5rem; }
            .content-header h1 { font-size: 1.4rem; }
            .summary-stats { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="/" class="logo">Thiên Kim Wine</a>
            <nav class="nav-links">
                <a href="/tong-quan">Tổng quan</a>
                <a href="/huong-dan">Hướng dẫn</a>
                <a href="/tinh-nang" class="active">Tính năng</a>
                <a href="/api-docs">API</a>
                <a href="/admin">Admin</a>
            </nav>
        </div>
    </header>

    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-title">Danh Mục</div>
            <ul class="sidebar-menu">
                <li><a href="#summary" class="active">Tổng Kết</a></li>
                <li><a href="#admin">Quản Lý</a></li>
                <li><a href="#customer">Khách Hàng</a></li>
                <li><a href="#seo">SEO</a></li>
                <li><a href="#analytics">Thống Kê</a></li>
            </ul>
        </aside>

        <main class="content">
            <div class="content-header">
                <h1>Tính Năng Hệ Thống</h1>
                <p>22 tính năng chính + 19 API endpoints</p>
            </div>

            <section id="summary">
                <div class="summary-box">
                    <h3>Tóm Tắt</h3>
                    <div class="summary-stats">
                        <div class="stat">
                            <span class="stat-number">22</span>
                            <span class="stat-label">Tính Năng</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">19</span>
                            <span class="stat-label">API</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">8</span>
                            <span class="stat-label">Tech</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">100%</span>
                            <span class="stat-label">Mobile</span>
                        </div>
                    </div>
                </div>
            </section>

            <section id="admin">
                <h2>Quản Lý (Admin)</h2>
                
                <div class="feature-card">
                    <h3>1. Sản Phẩm</h3>
                    <p>CRUD sản phẩm với giá, ảnh, phân loại</p>
                    <ul class="feature-list">
                        <li>Thêm/sửa/xóa sản phẩm rượu vang</li>
                        <li>Upload nhiều ảnh (kéo thả, preview)</li>
                        <li>Phân loại: brand, origin, type, grapes</li>
                        <li>Giá + giá gốc (tính % auto)</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>2. Taxonomy</h3>
                    <p>Phân loại: brand, origin, loại rượu, giống nho</p>
                    <ul class="feature-list">
                        <li>Thương hiệu (Château Margaux...)</li>
                        <li>Xuất xứ: quốc gia + vùng</li>
                        <li>Loại rượu (đỏ, trắng, rosé...)</li>
                        <li>Giống nho (Cabernet, Merlot...)</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>3. Bài Viết</h3>
                    <p>Blog: viết, ảnh, xuất bản</p>
                    <ul class="feature-list">
                        <li>Soạn thảo HTML rich text</li>
                        <li>Upload ảnh bìa + gallery</li>
                        <li>Xuất bản/Draft</li>
                        <li>SEO meta tags</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>4. Menu Builder</h3>
                    <p>Tạo navigation menu kéo thả</p>
                    <ul class="feature-list">
                        <li>Thêm menu blocks + items</li>
                        <li>Kéo thả sắp xếp</li>
                        <li>Link tới product/article/custom URL</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>5. Settings</h3>
                    <p>Logo, hotline, email, địa chỉ, Google Maps, meta defaults</p>
                    <ul class="feature-list">
                        <li>Logo + Favicon</li>
                        <li>Thông tin liên hệ</li>
                        <li>SEO mặc định</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>6. Social Links</h3>
                    <p>Facebook, Instagram, YouTube, Zalo... ở footer</p>
                    <ul class="feature-list">
                        <li>Thêm/sửa social links</li>
                        <li>Upload icon tùy chỉnh</li>
                    </ul>
                </div>
            </section>

            <section id="customer">
                <h2>Khách Hàng (Frontend)</h2>

                <div class="feature-card">
                    <h3>7. Tìm Kiếm (Search)</h3>
                    <p>Real-time search với debounce 500ms</p>
                    <ul class="feature-list">
                        <li>Tìm theo tên sản phẩm</li>
                        <li>Highlight kết quả</li>
                        <li>Tìm không dấu</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>8. Lọc (Filters)</h3>
                    <p>Multi-select lọc kết hợp</p>
                    <ul class="feature-list">
                        <li>Giá: min-max slider</li>
                        <li>Độ cồn: range slider</li>
                        <li>Brand, origin, type, grapes</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>9. Sắp Xếp (Sorting)</h3>
                    <p>Giá, ngày tạo, tên A-Z</p>
                    <ul class="feature-list">
                        <li>Giá: thấp→cao / cao→thấp</li>
                        <li>Ngày: mới→cũ / cũ→mới</li>
                        <li>Tên: A-Z / Z-A</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>10. Pagination & Scroll</h3>
                    <p>24 items/trang hoặc infinite scroll</p>
                    <ul class="feature-list">
                        <li>Pagination: click trang</li>
                        <li>Infinite scroll: auto load</li>
                        <li>Loading skeleton</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>11. Chi Tiết Sản Phẩm</h3>
                    <p>Gallery, giá, thông tin, CTA liên hệ, SEO</p>
                    <ul class="feature-list">
                        <li>Gallery lightbox + carousel</li>
                        <li>Độ cồn, vintage, dung tích</li>
                        <li>Mô tả rich HTML</li>
                        <li>CTA tracking</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>12. Bài Viết</h3>
                    <p>Danh sách + chi tiết bài blog</p>
                    <ul class="feature-list">
                        <li>List + pagination</li>
                        <li>Chi tiết: content, gallery, author</li>
                        <li>SEO meta tags</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>13. Responsive</h3>
                    <p>Mobile-first design, responsive mọi thiết bị</p>
                    <ul class="feature-list">
                        <li>Breakpoints: mobile/tablet/desktop</li>
                        <li>Touch-friendly UI</li>
                        <li>Lazy load images</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>14. Cache</h3>
                    <p>Frontend cache version tracking</p>
                    <ul class="feature-list">
                        <li>TanStack Query caching</li>
                        <li>Backend Redis cache</li>
                        <li>Auto invalidate</li>
                    </ul>
                </div>
            </section>

            <section id="seo">
                <h2>SEO & Marketing</h2>

                <div class="feature-card">
                    <h3>15. Meta Tags Động</h3>
                    <p>Title, description, keywords per page</p>
                    <ul class="feature-list">
                        <li>Meta title + description</li>
                        <li>Fallback mặc định</li>
                        <li>Open Graph Facebook</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>16. Structured Data</h3>
                    <p>Product + Article + Organization schema (JSON-LD)</p>
                    <ul class="feature-list">
                        <li>Product schema (price, brand)</li>
                        <li>Article schema</li>
                        <li>Google rich snippets</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>17. SEO-friendly URLs</h3>
                    <p>Auto slug từ title (dấu → không dấu)</p>
                    <ul class="feature-list">
                        <li>VD: "Rượu Vang Đỏ" → /san-pham/ruou-vang-do</li>
                        <li>Unique validation</li>
                    </ul>
                </div>
            </section>

            <section id="analytics">
                <h2>Thống Kê & Analytics</h2>

                <div class="feature-card">
                    <h3>18. Visitor Tracking</h3>
                    <p>UUID anonymous, localStorage persistent</p>
                    <ul class="feature-list">
                        <li>Generate ID khi visitor lần đầu</li>
                        <li>Track sessions: POST /track/visitor</li>
                        <li>100% anonymous (no IP, email...)</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>19. Event Tracking</h3>
                    <p>product_view, article_view, cta_contact</p>
                    <ul class="feature-list">
                        <li>POST /track/event</li>
                        <li>Metadata: referrer, page_url...</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>20. Dashboard Widgets</h3>
                    <p>Visitor stats, top products, popular articles</p>
                    <ul class="feature-list">
                        <li>Visitor hôm nay/tuần/tháng</li>
                        <li>Top products viewed</li>
                        <li>Top articles read</li>
                    </ul>
                </div>

                <div class="feature-card">
                    <h3>21-22. 19 API Endpoints</h3>
                    <p>Health, cache, products, articles, home, menus, social, settings, tracking</p>
                    <ul class="feature-list">
                        <li>GET /health</li>
                        <li>GET /san-pham, /bai-viet, /home, /menus, /social-links, /settings</li>
                        <li>POST /track/visitor, /track/event</li>
                        <li>Tài liệu tại /api-docs</li>
                    </ul>
                </div>
            </section>
        </main>
    </div>

    <footer>
        <p><strong>Thiên Kim Wine</strong></p>
        <p><a href="/tong-quan">Tổng quan</a> | <a href="/huong-dan">Hướng dẫn</a> | <a href="/tinh-nang">Tính năng</a> | <a href="/api-docs">API</a> | <a href="/admin">Admin</a></p>
        <p style="color: #999; margin-top: 0.6rem;">© 2025 Thiên Kim Wine. All rights reserved.</p>
    </footer>
</body>
</html>
