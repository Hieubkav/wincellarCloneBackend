<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tổng Quan - Thiên Kim Wine</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,600,700" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --noir: #1C1C1C;
            --amber: #ECAA4D;
            --wine: #9B2C3B;
        }
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
            border-bottom: 3px solid var(--amber);
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
            gap: 1.2rem;
            font-size: 0.85rem;
        }
        .nav-links a {
            color: #FFF;
            text-decoration: none;
            padding: 0.4rem 0.8rem;
            border-radius: 3px;
            transition: background 0.2s;
        }
        .nav-links a:hover {
            background: rgba(236,170,77,0.2);
        }
        .nav-links a.active {
            background: var(--amber);
            color: var(--noir);
        }
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .hero {
            text-align: center;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #DDD;
        }
        .hero h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .hero p {
            font-size: 1rem;
            color: #666;
            max-width: 700px;
            margin: 0 auto;
        }
        section {
            margin-bottom: 3rem;
        }
        section h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 1rem;
            border-bottom: 3px solid var(--amber);
            padding-bottom: 0.4rem;
            display: inline-block;
        }
        section h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 1.5rem 0 0.8rem 0;
        }
        section p {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 0.8rem;
            line-height: 1.7;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.2rem;
            margin: 1.5rem 0;
        }
        .card {
            background: #F5F5F5;
            border: 2px solid #DDD;
            border-left: 4px solid var(--amber);
            padding: 1.2rem;
        }
        .card h4 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.6rem;
        }
        .card ul {
            list-style: none;
            padding: 0;
        }
        .card li {
            padding: 0.3rem 0 0.3rem 1.2rem;
            position: relative;
            font-size: 0.85rem;
            color: #666;
        }
        .card li:before {
            content: "→";
            position: absolute;
            left: 0;
            color: var(--amber);
            font-weight: 700;
        }
        .tech-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }
        .tech-item {
            background: #FFF;
            border: 2px solid #DDD;
            padding: 1rem;
            text-align: center;
        }
        .tech-item strong {
            display: block;
            font-size: 0.95rem;
            margin-bottom: 0.3rem;
        }
        .tech-item span {
            color: #666;
            font-size: 0.75rem;
        }
        footer {
            background: var(--noir);
            color: #FFF;
            padding: 1.2rem 2rem;
            text-align: center;
            border-top: 3px solid var(--amber);
        }
        footer p {
            font-size: 0.8rem;
            margin: 0.2rem 0;
        }
        footer a {
            color: var(--amber);
            text-decoration: none;
        }
        @media (max-width: 768px) {
            header { padding: 1rem 1.5rem; }
            .header-content { flex-direction: column; gap: 0.8rem; }
            .nav-links { flex-wrap: wrap; justify-content: center; gap: 0.6rem; }
            .logo { font-size: 1.1rem; }
            main { padding: 1.5rem 1rem; }
            section h2 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="/" class="logo">Thiên Kim Wine</a>
            <nav class="nav-links">
                <a href="/tong-quan" class="active">Tổng quan</a>
                <a href="/huong-dan">Hướng dẫn</a>
                <a href="/tinh-nang">Tính năng</a>
                <a href="/api-docs">API</a>
                <a href="/admin">Admin</a>
            </nav>
        </div>
    </header>

    <main>
        <div class="hero">
            <h1>Tổng Quan Hệ Thống</h1>
            <p>Website quản lý rượu vang fullstack - Backend Laravel 12 + Frontend Next.js 15</p>
        </div>

        <section>
            <h2>Giới Thiệu</h2>
            <p><strong>Thiên Kim Wine</strong> là hệ thống quản lý cửa hàng rượu vang hoàn chỉnh. Chủ shop quản lý sản phẩm trên Admin Panel. Khách hàng duyệt website để tìm, lọc, xem chi tiết rượu vang. API kết nối 2 phần với nhau.</p>
            
            <div class="grid">
                <div class="card">
                    <h4>Tác Dụng</h4>
                    <ul>
                        <li>Quản lý sản phẩm, giá, ảnh, bài viết</li>
                        <li>Khách tìm kiếm, lọc sản phẩm dễ dàng</li>
                        <li>Tracking visitor, biết sản phẩm hot</li>
                        <li>Tối ưu SEO cho Google</li>
                    </ul>
                </div>
                <div class="card">
                    <h4>Người Dùng</h4>
                    <ul>
                        <li>Chủ shop: Quản lý admin panel</li>
                        <li>Khách hàng: Duyệt website public</li>
                        <li>Developer: API integration</li>
                    </ul>
                </div>
            </div>
        </section>

        <section>
            <h2>Công Nghệ</h2>
            <p>Hệ thống tách biệt Backend - Frontend (Decoupled Architecture) với stack hiện đại.</p>
            
            <h3>Backend (Laravel 12 + Filament 4)</h3>
            <div class="tech-grid">
                <div class="tech-item">
                    <strong>Laravel 12</strong>
                    <span>PHP framework</span>
                </div>
                <div class="tech-item">
                    <strong>Filament 4</strong>
                    <span>Admin panel</span>
                </div>
                <div class="tech-item">
                    <strong>MySQL</strong>
                    <span>Database</span>
                </div>
                <div class="tech-item">
                    <strong>Redis</strong>
                    <span>Cache</span>
                </div>
            </div>

            <h3>Frontend (Next.js 15 + React 19)</h3>
            <div class="tech-grid">
                <div class="tech-item">
                    <strong>Next.js 15</strong>
                    <span>React framework + SSR</span>
                </div>
                <div class="tech-item">
                    <strong>React 19</strong>
                    <span>UI library</span>
                </div>
                <div class="tech-item">
                    <strong>TypeScript</strong>
                    <span>Type safety</span>
                </div>
                <div class="tech-item">
                    <strong>TanStack Query</strong>
                    <span>Data fetching</span>
                </div>
                <div class="tech-item">
                    <strong>Zustand</strong>
                    <span>State management</span>
                </div>
                <div class="tech-item">
                    <strong>Tailwind CSS</strong>
                    <span>Styling</span>
                </div>
            </div>

            <h3>API & Monitoring</h3>
            <div class="grid">
                <div class="card">
                    <h4>API Backend</h4>
                    <ul>
                        <li>19 RESTful endpoints</li>
                        <li>Rate limit 60 req/phút</li>
                        <li>JSON response + HATEOAS</li>
                        <li>Tracking visitor & events</li>
                    </ul>
                </div>
                <div class="card">
                    <h4>Admin Panel</h4>
                    <ul>
                        <li>CRUD products, articles</li>
                        <li>Menu builder kéo thả</li>
                        <li>Upload ảnh multi-file</li>
                        <li>Dashboard widgets</li>
                    </ul>
                </div>
                <div class="card">
                    <h4>Frontend</h4>
                    <ul>
                        <li>SSR cho SEO tốt</li>
                        <li>Responsive mobile-first</li>
                        <li>Search + filter + sort</li>
                        <li>Structured data Schema.org</li>
                    </ul>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p><strong>Thiên Kim Wine - Developer Portal</strong></p>
        <p><a href="/tong-quan">Tổng quan</a> | <a href="/huong-dan">Hướng dẫn</a> | <a href="/tinh-nang">Tính năng</a> | <a href="/api-docs">API</a> | <a href="/admin">Admin</a></p>
        <p style="color: #999; margin-top: 0.8rem;">© 2025 Thiên Kim Wine. All rights reserved.</p>
    </footer>
</body>
</html>
