<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>API Docs - Thiên Kim Wine</title>
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
            border-bottom: 3px solid var(--amber);
        }
        header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
        }
        header p {
            font-size: 0.85rem;
            color: #DDD;
        }
        nav {
            background: #F5F5F5;
            padding: 0.8rem 2rem;
            border-bottom: 1px solid #DDD;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }
        nav a {
            text-decoration: none;
            color: var(--noir);
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.3rem 0.6rem;
            border-radius: 3px;
            transition: background 0.2s;
        }
        nav a:hover { background: var(--amber); color: #FFF; }
        .hero {
            padding: 2rem;
            background: linear-gradient(135deg, rgba(155,44,59,0.05) 0%, rgba(236,170,77,0.05) 100%);
            text-align: center;
            border-bottom: 1px solid #DDD;
            margin-bottom: 1rem;
        }
        .hero h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.6rem;
        }
        .hero p {
            font-size: 0.9rem;
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }
        .quick-start {
            padding: 1.5rem 2rem;
            background: var(--wine);
            color: #FFF;
            margin-bottom: 1rem;
        }
        .quick-start h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .quick-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .quick-card {
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 4px;
            border-left: 3px solid var(--amber);
        }
        .quick-card h4 {
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }
        .quick-card p {
            font-size: 0.8rem;
            font-weight: 400;
        }
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 2rem;
        }
        section {
            margin-bottom: 2.5rem;
        }
        section h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--noir);
            margin-bottom: 1rem;
            padding-bottom: 0.4rem;
            border-bottom: 2px solid var(--amber);
            display: inline-block;
        }
        section h3 {
            font-size: 1rem;
            font-weight: 600;
            margin: 1.2rem 0 0.6rem 0;
        }
        section p {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.6rem;
        }
        .endpoint {
            background: #F5F5F5;
            border: 1px solid #DDD;
            border-left: 4px solid var(--amber);
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 3px;
        }
        .endpoint-header {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 0.8rem;
            flex-wrap: wrap;
        }
        .method {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 3px;
            font-weight: 700;
            font-size: 0.7rem;
        }
        .method.get { background: #E3F2FD; color: #1976D2; }
        .method.post { background: #F3E5F5; color: #7B1FA2; }
        .method.put { background: #FFF3E0; color: #E65100; }
        .method.delete { background: #FFEBEE; color: #C62828; }
        .endpoint-path {
            font-family: 'Courier New', monospace;
            background: var(--noir);
            color: var(--amber);
            padding: 0.6rem;
            border-radius: 3px;
            font-size: 0.75rem;
            flex: 1;
        }
        .endpoint-description {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 0.8rem;
        }
        pre {
            background: var(--noir);
            color: #E0E0E0;
            padding: 0.8rem;
            border-radius: 3px;
            overflow-x: auto;
            margin: 0.8rem 0;
            font-size: 0.7rem;
            line-height: 1.4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0.8rem 0;
            font-size: 0.8rem;
        }
        th {
            background: var(--noir);
            color: #FFF;
            padding: 0.6rem;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 0.5rem;
            border-bottom: 1px solid #DDD;
        }
        tr:hover { background: #F9F9F9; }
        .badge {
            display: inline-block;
            padding: 0.2rem 0.4rem;
            background: var(--amber);
            color: #FFF;
            border-radius: 2px;
            font-size: 0.65rem;
            font-weight: 700;
        }
        .badge.required { background: var(--wine); }
        .info-box {
            background: rgba(28,28,28,0.05);
            border-left: 3px solid var(--noir);
            padding: 0.8rem;
            margin: 0.8rem 0;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        .auth-card {
            background: rgba(236,170,77,0.1);
            border-left: 3px solid var(--amber);
            padding: 0.8rem;
            margin: 0.8rem 0;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        footer {
            background: var(--noir);
            color: #FFF;
            padding: 1rem 2rem;
            text-align: center;
            border-top: 3px solid var(--amber);
            font-size: 0.75rem;
        }
        footer p { margin: 0.2rem 0; }
        footer a { color: var(--amber); text-decoration: none; }
        @media (max-width: 768px) {
            header h1 { font-size: 1.3rem; }
            nav ul { gap: 0.8rem; }
            .quick-grid { grid-template-columns: 1fr; }
            section h2 { font-size: 1.2rem; }
            .endpoint-header { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <header>
        <h1>Thiên Kim Wine API v1</h1>
        <p>REST API 19 endpoints - Backend Laravel 12 + Frontend Next.js 15</p>
    </header>

    <nav>
        <ul>
            <li><a href="#overview">Tổng Quan</a></li>
            <li><a href="#auth">Auth</a></li>
            <li><a href="#health">Health</a></li>
            <li><a href="#products">Products</a></li>
            <li><a href="#articles">Articles</a></li>
            <li><a href="#home">Home</a></li>
            <li><a href="#menus">Menus</a></li>
            <li><a href="#social">Social</a></li>
            <li><a href="#tracking">Tracking</a></li>
            <li><a href="#settings">Settings</a></li>
        </ul>
    </nav>

    <div class="hero">
        <h2>REST API Documentation</h2>
        <p>19 endpoints RESTful với JSON response, filter, sort, pagination, tracking, cache management.</p>
    </div>

    <div class="quick-start">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h3>Quick Start</h3>
            <div class="quick-grid">
                <div class="quick-card">
                    <h4>Base URL</h4>
                    <p><code>http://localhost:8000/api/v1</code></p>
                </div>
                <div class="quick-card">
                    <h4>Frontend</h4>
                    <p><code>http://localhost:3000</code></p>
                </div>
                <div class="quick-card">
                    <h4>Auth</h4>
                    <p>Hầu hết endpoints public không cần auth</p>
                </div>
                <div class="quick-card">
                    <h4>Response</h4>
                    <p>JSON với HATEOAS links</p>
                </div>
            </div>
        </div>
    </div>

    <main>
        <section id="overview">
            <h2>Tổng Quan</h2>
            <p><strong>Thiên Kim Wine API</strong> cung cấp 19 endpoints quản lý sản phẩm, bài viết, menus, settings, tracking.</p>
            <ul style="margin-left: 1.5rem; font-size: 0.85rem; color: #666;">
                <li><strong>19 API endpoints</strong> RESTful</li>
                <li><strong>Rate limit:</strong> 60 req/phút</li>
                <li><strong>Response:</strong> JSON + HATEOAS</li>
                <li><strong>Tracking:</strong> Anonymous visitor & event tracking</li>
                <li><strong>Cache:</strong> Redis + version tracking</li>
                <li><strong>Monitoring:</strong> Health check system</li>
            </ul>
        </section>

        <section id="auth">
            <h2>Authentication</h2>
            <p>Hầu hết endpoints public không yêu cầu xác thực.</p>
            <div class="endpoint">
                <h4>Header (nếu cần auth):</h4>
                <pre>Authorization: Bearer YOUR_TOKEN</pre>
            </div>
        </section>

        <section id="health">
            <h2>Health & System</h2>
            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method get">GET</span>
                    <div class="endpoint-path">/health</div>
                </div>
                <p class="endpoint-description">System health status</p>
                <div class="info-box">
                    <strong>Response:</strong> { "status": "OK", "database": true, "cache": true }
                </div>
            </div>
        </section>

        <section id="products">
            <h2>Products</h2>
            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method get">GET</span>
                    <div class="endpoint-path">/san-pham</div>
                </div>
                <p class="endpoint-description">Danh sách sản phẩm (filter, sort, pagination)</p>
                <h4>Parameters:</h4>
                <table>
                    <tr>
                        <th>Field</th>
                        <th>Kiểu</th>
                        <th>Mô Tả</th>
                    </tr>
                    <tr>
                        <td><code>q</code></td>
                        <td>string</td>
                        <td>Tìm kiếm theo tên</td>
                    </tr>
                    <tr>
                        <td><code>brand</code></td>
                        <td>int</td>
                        <td>ID thương hiệu</td>
                    </tr>
                    <tr>
                        <td><code>price_min, price_max</code></td>
                        <td>int</td>
                        <td>Khoảng giá</td>
                    </tr>
                    <tr>
                        <td><code>sort</code></td>
                        <td>string</td>
                        <td>price, name, created_at (thêm - để giảm dần)</td>
                    </tr>
                    <tr>
                        <td><code>page, per_page</code></td>
                        <td>int</td>
                        <td>Pagination (mặc định 24/trang)</td>
                    </tr>
                </table>
                <h4>Ví Dụ:</h4>
                <pre>GET /api/v1/san-pham?q=bordeaux&brand=5&price_max=5000000&sort=-price&page=1&per_page=24</pre>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method get">GET</span>
                    <div class="endpoint-path">/san-pham/{slug}</div>
                </div>
                <p class="endpoint-description">Chi tiết sản phẩm</p>
                <h4>Response:</h4>
                <pre>{
  "data": {
    "id": 1,
    "name": "Rượu Vang Đỏ Bordeaux",
    "slug": "ruou-vang-do-bordeaux",
    "price": 2500000,
    "alcohol": 13.5,
    "vintage": 2015,
    "description": "...",
    "gallery": [...],
    "brand": { ... },
    "origin": { ... },
    "wine_type": { ... }
  },
  "_links": { "self": { "href": "..." } }
}</pre>
            </div>
        </section>

        <section id="articles">
            <h2>Articles</h2>
            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method get">GET</span>
                    <div class="endpoint-path">/bai-viet</div>
                </div>
                <p class="endpoint-description">Danh sách bài viết (pagination)</p>
                <p style="font-size: 0.8rem; color: #666; margin-bottom: 0.5rem;">Query: page, per_page (mặc định 12/trang)</p>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method get">GET</span>
                    <div class="endpoint-path">/bai-viet/{slug}</div>
                </div>
                <p class="endpoint-description">Chi tiết bài viết</p>
            </div>
        </section>

        <section id="home">
            <h2>Home</h2>
            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method get">GET</span>
                    <div class="endpoint-path">/home</div>
                </div>
                <p class="endpoint-description">Homepage components (carousel, featured products, banners)</p>
            </div>
        </section>

        <section id="menus">
            <h2>Menus</h2>
            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method get">GET</span>
                    <div class="endpoint-path">/menus</div>
                </div>
                <p class="endpoint-description">Cấu trúc menu navigation</p>
                <p style="font-size: 0.8rem; color: #666; margin-top: 0.5rem;">Trả về: menus > blocks > items (3 cấp)</p>
            </div>
        </section>

        <section id="social">
            <h2>Social Links</h2>
            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method get">GET</span>
                    <div class="endpoint-path">/social-links</div>
                </div>
                <p class="endpoint-description">Facebook, Instagram, YouTube, Zalo... (ở footer)</p>
                <h4>Response:</h4>
                <pre>[
  {
    "id": 1,
    "platform": "facebook",
    "url": "https://facebook.com/wincellar",
    "icon_url": "/storage/icons/facebook.svg",
    "order": 1
  },
  ...
]</pre>
            </div>
        </section>

        <section id="tracking">
            <h2>Tracking & Analytics</h2>
            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method get">GET</span>
                    <div class="endpoint-path">/track/generate-id</div>
                </div>
                <p class="endpoint-description">Generate UUID cho anonymous tracking (call 1 lần first visit)</p>
                <h4>Response:</h4>
                <pre>{
  "success": true,
  "data": {
    "anon_id": "550e8400-e29b-41d4-a716-446655440000"
  }
}</pre>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method post">POST</span>
                    <div class="endpoint-path">/track/visitor</div>
                </div>
                <p class="endpoint-description">Track visitor session</p>
                <h4>Body:</h4>
                <table>
                    <tr>
                        <th>Field</th>
                        <th>Bắt Buộc</th>
                    </tr>
                    <tr>
                        <td><code>anon_id</code></td>
                        <td><span class="badge required">Required</span></td>
                    </tr>
                    <tr>
                        <td><code>user_agent</code></td>
                        <td><span class="badge">Optional</span></td>
                    </tr>
                </table>
            </div>

            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method post">POST</span>
                    <div class="endpoint-path">/track/event</div>
                </div>
                <p class="endpoint-description">Track events: product_view, article_view, cta_contact</p>
                <h4>Body:</h4>
                <pre>{
  "anon_id": "550e8400-e29b-41d4-a716-446655440000",
  "event_type": "product_view",
  "product_id": 123,
  "metadata": {
    "referrer": "/san-pham",
    "page_url": "/san-pham/ruou-vang-do"
  }
}</pre>
                <div class="auth-card">
                    <strong>Event Types:</strong> product_view, article_view, cta_contact
                </div>
            </div>
        </section>

        <section id="settings">
            <h2>Settings</h2>
            <div class="endpoint">
                <div class="endpoint-header">
                    <span class="method get">GET</span>
                    <div class="endpoint-path">/settings</div>
                </div>
                <p class="endpoint-description">Site info: logo, favicon, watermark sản phẩm (url + vị trí + kích thước), hotline, email, địa chỉ, meta defaults</p>
                <h4>Response:</h4>
                <pre>{
  "data": {
    "id": 1,
    "site_name": "Wincellar Clone",
    "hotline": "0123 456 789",
    "address": "123 Đường ABC, Quận 1, TP.HCM",
    "hours": "8:00 - 22:00",
    "email": "contact@wincellar.com",
    "logo_url": "/storage/images/logo.png",
    "favicon_url": "/storage/images/favicon.ico",
    "product_watermark_url": "/storage/images/watermark.png",
    "product_watermark_position": "none",
    "product_watermark_size": "128x128",
    "meta_defaults": {
      "title": "Thiên Kim Wine - Cửa Hàng Rượu Vang",
      "description": "Chuyên cung cấp rượu vang nhập khẩu",
      "keywords": "rượu vang, wine"
    }
  },
  "meta": {
    "cache_version": 15
  }
}</pre>
                <div class="info-box">
                    <strong>Cache:</strong> 1 giờ | Auto-invalidate khi admin update
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p><strong>Thiên Kim Wine API v1</strong></p>
        <p><a href="/tong-quan">Tổng quan</a> | <a href="/huong-dan">Hướng dẫn</a> | <a href="/tinh-nang">Tính năng</a> | <a href="/api-docs">API</a> | <a href="/admin">Admin</a></p>
        <p style="color: #AAA; margin-top: 0.6rem;">© 2025 Thiên Kim Wine. All rights reserved.</p>
    </footer>
</body>
</html>
