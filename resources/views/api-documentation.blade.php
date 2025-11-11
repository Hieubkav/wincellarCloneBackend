<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wincellar API Documentation</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:300,400,500,600,700" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --noir: #1C1C1C;
            --amber: #ECAA4D;
            --wine: #9B2C3B;
            --white: #FFFFFF;
            --gray-light: #F5F5F5;
            --gray-border: #E8E8E8;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--noir);
            background: var(--white);
            line-height: 1.6;
        }

        /* Header */
        header {
            background: var(--noir);
            color: var(--white);
            padding: 2rem;
            border-bottom: 3px solid var(--amber);
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: -1px;
            margin-bottom: 0.5rem;
        }

        header p {
            font-size: 1rem;
            font-weight: 300;
            color: #CCC;
        }

        /* Navigation */
        nav {
            background: var(--gray-light);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--gray-border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }

        nav a {
            text-decoration: none;
            color: var(--noir);
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        nav a:hover {
            background: var(--amber);
            color: var(--white);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Hero Section */
        .hero {
            padding: 4rem 2rem;
            background: linear-gradient(135deg, rgba(155, 44, 59, 0.05) 0%, rgba(236, 170, 77, 0.05) 100%);
            text-align: center;
            border-bottom: 1px solid var(--gray-border);
        }

        .hero h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--noir);
        }

        .hero p {
            font-size: 1.1rem;
            color: #666;
            max-width: 700px;
            margin: 0 auto;
            font-weight: 400;
        }

        /* Quick Start */
        .quick-start {
            padding: 3rem 2rem;
            background: var(--wine);
            color: var(--white);
        }

        .quick-start h3 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 2rem;
        }

        .quick-start-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .quick-start-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid var(--amber);
        }

        .quick-start-card h4 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .quick-start-card p {
            font-size: 0.95rem;
            font-weight: 300;
            line-height: 1.6;
        }

        /* Main Content */
        main {
            padding: 3rem 2rem;
        }

        section {
            margin-bottom: 4rem;
        }

        section h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--noir);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--amber);
        }

        section h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--noir);
            margin: 2rem 0 1rem 0;
        }

        /* Endpoint Card */
        .endpoint {
            background: var(--gray-light);
            border: 1px solid var(--gray-border);
            border-left: 4px solid var(--amber);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .endpoint:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .endpoint-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .method {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .method.get {
            background: #E3F2FD;
            color: #1976D2;
        }

        .method.post {
            background: #F3E5F5;
            color: #7B1FA2;
        }

        .method.put {
            background: #FFF3E0;
            color: #E65100;
        }

        .method.delete {
            background: #FFEBEE;
            color: #C62828;
        }

        .endpoint-path {
            font-family: 'Courier New', monospace;
            background: var(--noir);
            color: var(--amber);
            padding: 0.8rem;
            border-radius: 4px;
            font-size: 0.95rem;
            flex: 1;
            min-width: 200px;
        }

        .endpoint-description {
            color: #666;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        /* Code Block */
        pre {
            background: var(--noir);
            color: #E0E0E0;
            padding: 1.5rem;
            border-radius: 4px;
            overflow-x: auto;
            margin: 1rem 0;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        code {
            font-family: 'Courier New', monospace;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            border: 1px solid var(--gray-border);
        }

        th {
            background: var(--noir);
            color: var(--white);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-border);
        }

        tr:hover {
            background: var(--gray-light);
        }

        /* Links */
        a {
            color: var(--wine);
            text-decoration: none;
        }

        a:hover {
            color: var(--amber);
            text-decoration: underline;
        }

        /* Auth Section */
        .auth-card {
            background: rgba(236, 170, 77, 0.1);
            border-left: 4px solid var(--amber);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 4px;
        }

        .auth-card h4 {
            color: var(--wine);
            margin-bottom: 0.5rem;
        }

        /* Info Box */
        .info-box {
            background: rgba(28, 28, 28, 0.05);
            border-left: 4px solid var(--noir);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 4px;
        }

        .info-box strong {
            color: var(--noir);
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            background: var(--amber);
            color: var(--white);
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 0.5rem;
        }

        .badge.required {
            background: var(--wine);
        }

        /* Footer */
        footer {
            background: var(--noir);
            color: var(--white);
            padding: 2rem;
            text-align: center;
            border-top: 3px solid var(--amber);
            margin-top: 4rem;
        }

        footer p {
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            header h1 {
                font-size: 1.8rem;
            }

            nav ul {
                gap: 0.5rem;
            }

            section h2 {
                font-size: 1.5rem;
            }

            .endpoint-header {
                flex-direction: column;
                align-items: flex-start;
            }

            pre {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <h1>🍷 Wincellar API</h1>
            <p>Tài liệu API cho hệ thống quản lý rượu vang thế hệ mới</p>
        </div>
    </header>

    <!-- Navigation -->
    <nav>
        <div class="container">
            <ul>
                <li><a href="#overview">Tổng quan</a></li>
                <li><a href="#auth">Xác thực</a></li>
                <li><a href="#health">Health Check</a></li>
                <li><a href="#products">Sản phẩm</a></li>
                <li><a href="#home">Trang chủ</a></li>
                <li><a href="#settings">Cài đặt</a></li>
                <li><a href="#docs">Tài liệu</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="hero">
        <div class="container">
            <h2>Bắt đầu nhanh chóng</h2>
            <p>API REST hiện đại với response JSON, hỗ trợ filter, sort, pagination và search đầy đủ.</p>
        </div>
    </div>

    <!-- Quick Start -->
    <div class="quick-start">
        <div class="container">
            <h3>Quick Start</h3>
            <div class="quick-start-grid">
                <div class="quick-start-card">
                    <h4>1. Base URL</h4>
                    <p><code>http://localhost:8000/api/v1</code></p>
                </div>
                <div class="quick-start-card">
                    <h4>2. Lấy dữ liệu</h4>
                    <p>Hầu hết endpoints không cần authentication</p>
                </div>
                <div class="quick-start-card">
                    <h4>3. Phản hồi JSON</h4>
                    <p>Tất cả responses đều là JSON format</p>
                </div>
            </div>
        </div>
    </div>

    <main>
        <div class="container">
            <!-- Overview -->
            <section id="overview">
                <h2>📖 Tổng quan</h2>
                <p>Wincellar API cung cấp các endpoints để:</p>
                <ul style="margin: 1rem 0 0 2rem;">
                    <li>Quản lý sản phẩm rượu vang với filter nâng cao</li>
                    <li>Lấy dữ liệu trang chủ (banners, featured products)</li>
                    <li>Lấy thông tin cài đặt ứng dụng (logo, contact info, meta defaults)</li>
                    <li>Kiểm tra tính khỏe mạnh của hệ thống</li>
                    <li>Tìm kiếm sản phẩm theo tên, giá, thương hiệu, xuất xứ...</li>
                </ul>

                <div class="info-box">
                    <strong>Lưu ý:</strong> API hiện đang ở phiên bản v1. Tất cả endpoints bắt đầu bằng <code>/api/v1</code>
                </div>
            </section>

            <!-- Authentication -->
            <section id="auth">
                <h2>🔐 Xác thực</h2>
                <p>Hầu hết các endpoints public không yêu cầu xác thực. Một số endpoints admin sẽ yêu cầu JWT token.</p>

                <h3>Header yêu cầu</h3>
                <pre>Authorization: Bearer YOUR_TOKEN</pre>

                <div class="auth-card">
                    <h4>Cấp JWT Token</h4>
                    <p>Token được cấp qua endpoint login với credentials.</p>
                </div>
            </section>

            <!-- Health Check -->
            <section id="health">
                <h2>🏥 Health & System</h2>

                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method get">GET</span>
                        <div class="endpoint-path">/health</div>
                    </div>
                    <p class="endpoint-description">Kiểm tra tính khỏe mạnh của hệ thống (database, cache, storage)</p>

                    <h4>Tham số</h4>
                    <p>Không có</p>

                    <h4>Phản hồi thành công (200)</h4>
                    <pre>{
  "status": "healthy",
  "services": {
    "database": {
      "status": "healthy",
      "response_time_ms": 2.34
    },
    "cache": {
      "status": "healthy",
      "response_time_ms": 1.23
    }
  },
  "performance": {
    "response_time_ms": 15.67,
    "memory_usage_mb": 12.5
  }
}</pre>

                    <div class="info-box">
                        <strong>Rate Limit:</strong> 60 requests/minute
                    </div>
                </div>
            </section>

            <!-- Products -->
            <section id="products">
                <h2>🍷 Sản phẩm</h2>

                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method get">GET</span>
                        <div class="endpoint-path">/san-pham</div>
                    </div>
                    <p class="endpoint-description">Danh sách sản phẩm với filter, sort, pagination</p>

                    <h4>Query Parameters</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Tham số</th>
                                <th>Kiểu</th>
                                <th>Mô tả</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>page</code></td>
                                <td>integer</td>
                                <td>Trang hiện tại (default: 1)</td>
                            </tr>
                            <tr>
                                <td><code>per_page</code></td>
                                <td>integer</td>
                                <td>Số items per page (max: 60, default: 24)</td>
                            </tr>
                            <tr>
                                <td><code>sort</code></td>
                                <td>string</td>
                                <td>Sắp xếp: <code>-created_at</code>, <code>price</code>, <code>-price</code></td>
                            </tr>
                            <tr>
                                <td><code>q</code></td>
                                <td>string</td>
                                <td>Tìm kiếm theo tên sản phẩm</td>
                            </tr>
                            <tr>
                                <td><code>terms[brand][]</code></td>
                                <td>integer[]</td>
                                <td>Filter theo thương hiệu (ID)</td>
                            </tr>
                            <tr>
                                <td><code>price_min</code></td>
                                <td>integer</td>
                                <td>Giá tối thiểu (VND)</td>
                            </tr>
                            <tr>
                                <td><code>price_max</code></td>
                                <td>integer</td>
                                <td>Giá tối đa (VND)</td>
                            </tr>
                            <tr>
                                <td><code>alcohol_min</code></td>
                                <td>float</td>
                                <td>Độ cồn tối thiểu (%)</td>
                            </tr>
                            <tr>
                                <td><code>alcohol_max</code></td>
                                <td>float</td>
                                <td>Độ cồn tối đa (%)</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Ví dụ Request</h4>
                    <pre>GET /api/v1/san-pham?page=1&per_page=24&sort=-created_at&price_min=100000&price_max=500000</pre>

                    <h4>Phản hồi thành công (200)</h4>
                    <pre>{
  "data": [
    {
      "id": 1,
      "name": "Rượu Vang Đỏ Bordeaux",
      "slug": "ruou-vang-do-bordeaux",
      "price": 250000,
      "alcohol": 13.5,
      "vintage": 2015,
      "description": "...",
      "images": [...]
    }
  ],
  "pagination": {
    "total": 150,
    "per_page": 24,
    "current_page": 1,
    "last_page": 7
  }
}</pre>

                    <div class="info-box">
                        <strong>Rate Limit:</strong> 60 requests/minute | <strong>Auth:</strong> Không yêu cầu
                    </div>
                </div>
            </section>

            <!-- Home -->
            <section id="home">
                <h2>🏠 Trang chủ</h2>

                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method get">GET</span>
                        <div class="endpoint-path">/home</div>
                    </div>
                    <p class="endpoint-description">Lấy dữ liệu trang chủ (components, featured products, banners)</p>

                    <h4>Tham số</h4>
                    <p>Không có</p>

                    <h4>Phản hồi thành công (200)</h4>
                    <pre>{
  "data": {
    "components": [
      {
        "type": "carousel",
        "items": [...]
      },
      {
        "type": "featured_products",
        "products": [...]
      }
    ]
  }
}</pre>

                    <div class="info-box">
                        <strong>Rate Limit:</strong> 60 requests/minute | <strong>Auth:</strong> Không yêu cầu
                    </div>
                </div>
            </section>

            <!-- Settings -->
            <section id="settings">
                <h2>⚙️ Cài đặt</h2>

                <div class="endpoint">
                    <div class="endpoint-header">
                        <span class="method get">GET</span>
                        <div class="endpoint-path">/settings</div>
                    </div>
                    <p class="endpoint-description">Lấy thông tin cài đặt ứng dụng (logo, thông tin liên hệ, meta defaults)</p>

                    <h4>Tham số</h4>
                    <p>Không có</p>

                    <h4>Phản hồi thành công (200)</h4>
                    <pre>{
  "data": {
    "id": 1,
    "site_name": "Wincellar Clone",
    "hotline": "0123 456 789",
    "address": "123 Đường ABC, Quận 1, TP.HCM",
    "hours": "8:00 - 22:00 hàng ngày",
    "email": "contact@wincellar.com",
    "logo_url": "/storage/images/logo.png",
    "favicon_url": "/storage/images/favicon.ico",
    "meta_defaults": {
      "title": "Wincellar - Cửa hàng rượu vang uy tín",
      "description": "Chuyên cung cấp rượu vang nhập khẩu chính hãng",
      "keywords": "rượu vang, wine, bordeaux"
    },
    "_links": {
      "self": {
        "href": "http://localhost:8000/api/v1/settings",
        "method": "GET"
      }
    }
  },
  "meta": {
    "api_version": "v1",
    "timestamp": "2025-11-11T10:30:00Z"
  }
}</pre>

                    <div class="info-box">
                        <strong>Rate Limit:</strong> 60 requests/minute | <strong>Auth:</strong> Không yêu cầu | <strong>Cache:</strong> 1 giờ (auto-invalidate khi admin update)
                    </div>

                    <div class="auth-card">
                        <h4>💡 Cách sử dụng</h4>
                        <ul style="margin: 0.5rem 0 0 1.5rem;">
                            <li>Settings được cache 1 giờ để tối ưu performance</li>
                            <li>Cache tự động invalidate khi admin update trong Filament</li>
                            <li>Frontend nên call endpoint này 1 lần khi app init và lưu vào global state</li>
                            <li>Không trả về sensitive data (passwords, API keys, etc.)</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Documentation -->
            <section id="docs">
                <h2>📚 Tài liệu đầy đủ</h2>

                <p>Để xem tài liệu API chi tiết đầy đủ, vui lòng xem các file sau:</p>

                <div class="endpoint" style="background: rgba(236, 170, 77, 0.1);">
                    <h4>📄 Tài liệu API</h4>
                    <ul style="margin: 1rem 0 0 2rem;">
                        <li><a href="/docs/api/API_ENDPOINTS.md" target="_blank">API Endpoints - Danh sách tất cả endpoints</a></li>
                        <li><a href="/docs/api/API_DESIGN_AUDIT.md" target="_blank">API Design Audit - Đánh giá thiết kế</a></li>
                        <li><a href="/docs/api/API_TEST_RESULTS.md" target="_blank">API Test Results - Kết quả kiểm tra</a></li>
                    </ul>
                </div>

                <div class="info-box">
                    <strong>💡 Lưu ý:</strong> Tài liệu API được tự động cập nhật khi có thay đổi. Kiểm tra <code>docs/api</code> để xem phiên bản mới nhất.
                </div>
            </section>

            <!-- Support -->
            <section>
                <h2>💬 Hỗ trợ</h2>
                <p>Nếu gặp vấn đề hoặc có câu hỏi về API, vui lòng:</p>
                <ul style="margin: 1rem 0 0 2rem;">
                    <li>Kiểm tra tài liệu đầy đủ tại <code>/docs</code></li>
                    <li>Xem các test cases tại <code>/tests</code></li>
                    <li>Liên hệ team development</li>
                </ul>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p><strong>Wincellar API v1</strong></p>
            <p>Tài liệu API được cập nhật lần cuối: {{ date('d/m/Y H:i') }}</p>
            <p style="color: #CCC; margin-top: 1rem; font-size: 0.85rem;">© 2025 Thiên Kim Wine. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
