<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wincellar Clone - Developer Portal</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,600,700" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --noir: #1C1C1C;
            --amber: #ECAA4D;
            --wine: #9B2C3B;
            --white: #FFFFFF;
            --gray: #F5F5F5;
        }
        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--noir);
            background: var(--white);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: var(--noir);
            color: var(--white);
            padding: 1.5rem 2rem;
            border-bottom: 3px solid var(--amber);
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }
        .subtitle {
            font-size: 0.95rem;
            font-weight: 400;
            color: #DDD;
        }
        main {
            flex: 1;
            padding: 3rem 2rem;
            background: linear-gradient(135deg, rgba(236,170,77,0.01) 0%, rgba(155,44,59,0.01) 100%);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .intro {
            text-align: center;
            margin-bottom: 3rem;
        }
        .intro h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
        }
        .intro p {
            font-size: 1rem;
            color: #666;
            max-width: 650px;
            margin: 0 auto;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin: 0 auto;
            max-width: 900px;
        }
        .card {
            background: var(--white);
            border: 2px solid #DDD;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
            transition: border 0.2s;
        }
        .card:hover {
            border-color: var(--amber);
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--noir);
        }
        .card-description {
            font-size: 0.9rem;
            color: #666;
            flex: 1;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        .card-badge {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            background: #EEE;
            color: var(--noir);
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid #DDD;
            width: fit-content;
        }
        .card.overview { border-left: 4px solid var(--amber); }
        .card.guide { border-left: 4px solid var(--wine); }
        .card.features { border-left: 4px solid var(--noir); }
        .card.api { border-left: 4px solid var(--amber); }
        footer {
            background: var(--noir);
            color: var(--white);
            padding: 1.5rem 2rem;
            text-align: center;
            border-top: 3px solid var(--amber);
        }
        footer p {
            font-size: 0.85rem;
            margin: 0.3rem 0;
        }
        footer a {
            color: var(--amber);
            text-decoration: none;
        }
        @media (max-width: 768px) {
            h1 { font-size: 1.6rem; }
            .intro h2 { font-size: 1.4rem; }
            .cards-grid { grid-template-columns: 1fr; }
            main { padding: 1.5rem 1rem; }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>Thiên Kim Wine</h1>
            <p class="subtitle">Developer Portal - Laravel 12 + Next.js 15</p>
        </div>
    </header>

    <main id="main-content">
        <div class="container">
            <div class="intro">
                <h2>Quản Lý Rượu Vang Fullstack</h2>
                <p>Backend: Laravel 12 + Filament 4 | Frontend: Next.js 15 + React 19 | 19 API endpoints</p>
            </div>

            <div class="cards-grid">
                <a href="/tong-quan" class="card overview">
                    <h3 class="card-title">Tổng Quan</h3>
                    <p class="card-description">Kiến trúc, công nghệ, cấu trúc hệ thống chính.</p>
                    <span class="card-badge">Overview</span>
                </a>

                <a href="/huong-dan" class="card guide">
                    <h3 class="card-title">Hướng Dẫn</h3>
                    <p class="card-description">Cách sử dụng Admin Panel, quản lý sản phẩm, bài viết, menu, settings.</p>
                    <span class="card-badge">Tutorials</span>
                </a>

                <a href="/tinh-nang" class="card features">
                    <h3 class="card-title">Tính Năng</h3>
                    <p class="card-description">22 tính năng chính: CRUD, search, filter, tracking, cache, SEO, 19 endpoints.</p>
                    <span class="card-badge">Features</span>
                </a>

                <a href="/api-docs" class="card api">
                    <h3 class="card-title">API Docs</h3>
                    <p class="card-description">Tài liệu 19 API endpoints: health, products, articles, menus, tracking, settings.</p>
                    <span class="card-badge">API</span>
                </a>
            </div>
        </div>
    </main>

    <footer>
        <p><strong>Thiên Kim Wine</strong></p>
        <p><a href="/tong-quan">Tổng quan</a> | <a href="/huong-dan">Hướng dẫn</a> | <a href="/tinh-nang">Tính năng</a> | <a href="/api-docs">API Docs</a> | <a href="/admin">Admin</a></p>
        <p style="color: #AAA; margin-top: 0.8rem;">© 2025 Thiên Kim Wine. All rights reserved.</p>
    </footer>
</body>
</html>
