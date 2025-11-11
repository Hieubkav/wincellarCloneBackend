# Wincellar Clone - Laravel 12 + Filament 4.x Project

**Hướng Dẫn Lập Trình cho AI Assistants**

Trả lời bằng tiếng Việt (Always respond in Vietnamese)

---

## 🎯 Tổng Quan Dự Án

**Dự án:** Wincellar Clone - Nền tảng thương mại điện tử sản phẩm rượu
**Stack:** Laravel 12.x, Filament 4.x, MySQL/MariaDB
**Vị trí:** E:\Laravel\Laravel12\wincellarClone\wincellarcloneBackend

**⚠️ GIAO THỨC QUAN TRỌNG:** `read .claude/global/AI_AGENT_REMINDERS.md` trước khi thay đổi skills!

---

## ⚠️ QUAN TRỌNG: Giao Thức Auto-Sync Skills

**CHO AI AGENTS:** Sau BẤT KỲ thay đổi skills nào, bạn PHẢI chạy auto sync script!

### Khi nào cần Auto-Sync:
```
NẾU bạn vừa làm BẤT KỲ điều nào sau:
  ✓ Tạo skill mới (ví dụ: folder mới trong .claude/skills/)
  ✓ Gộp skills (xóa cái cũ, tạo mới gộp)
  ✓ Xóa/loại bỏ skills
  ✓ Cập nhật SKILLS_CONTEXT.md
THÌ:
  → NGAY LẬP TỨC chạy: python .claude/skills/meta/choose-skill/scripts/sync_choose_skill.py
  → Kiểm tra output hiển thị đếm cập nhật
  → Bao gồm kết quả sync trong phản hồi cho người dùng
```

### Tại sao Quan Trọng?
- `choose-skill` meta-agent đọc `skills-catalog.md` để gợi ý
- Không sync → gợi ý skills bị xóa/lỗi thời → PHÁ VỠ quy trình
- Sync giữ cho choose-skill thông minh và chính xác

### Ví dụ:
```
Người dùng: "Gộp skill A và B"
AI: 
1. Gộp skills ✓
2. Cập nhật SKILLS_CONTEXT.md ✓
3. AUTO-RUN sync script ✓  ← KHÔNG QUÊN!
4. Báo cáo: "Đã gộp và sync choose-skill"
```

---

## 📚 Các Skills Có Sẵn

<available_skills>

<skill>
<name>create-skill</name>
<description>Hướng dẫn tạo skills hiệu quả với phân loại danh mục thông minh, công cụ tự động (init_skill.py, suggest_skill_group.py, sync_to_choose_skill.py), và tài nguyên đi kèm (scripts/, references/, assets/). MỚI: AI-powered grouping intelligence phân tích skill domains, gợi ý danh mục tối ưu với điểm tin cậy, phát hiện cơ hội danh mục mới (3+ related skills), và xác định nhu cầu tái cấu trúc (overcrowded/underutilized categories). Ngăn chặn category sprawl và duy trì tổ chức tối ưu. SỬ DỤNG KHI người dùng nói 'tạo skill mới', 'gợi ý category cho skill', 'kiểm tra tổ chức skill', 'tái cấu trúc categories', hoặc muốn mở rộng khả năng với workflows chuyên dụng.</description>
<location>user/meta</location>
</skill>

<skill>
<name>choose-skill</name>
<description>Meta-agent phân tích các tác vụ và gợi ý kết hợp skills tối ưu với giải thích kiểu Feynman. Bộ phân tích CHỈ ĐỌC KHÔNG bao giờ sửa đổi code, chỉ cung cấp gợi ý. SỬ DỤNG KHI cảm thấy choáng ngợp bởi 34+ skills, không chắc chắn kỹ năng nào cần áp dụng, cần hướng dẫn về mẫu orchestration skills (sequential/parallel/conditional), muốn hiểu synergies skills, hoặc cần trợ giúp chọn skills phù hợp cho một tác vụ. Trả về 1-3 gợi ý kết hợp với giải thích Tiếng Việt đơn giản.</description>
<location>user/meta</location>
</skill>

<skill>
<name>filament-rules</name>
<description>Tiêu chuẩn lập trình Filament 4.x cho dự án Laravel 12 với custom Schema namespace (không phải Form), UI Tiếng Việt, mẫu Observer, quản lý ảnh. SỬ DỤNG KHI tạo Filament resources, sửa namespace errors (Class not found), triển khai forms, RelationManagers, hoặc bất kỳ tác vụ phát triển Filament nào.</description>
<location>user/filament</location>
</skill>

<skill>
<name>image-management</name>
<description>Hệ thống quản lý ảnh polymorphic tập trung với CheckboxList picker, WebP auto-conversion, quản lý thứ tự, soft deletes. SỬ DỤNG KHI thêm ảnh/gallery vào models, triển khai image upload, làm việc với ImagesRelationManager, hoặc khắc phục các vấn đề liên quan đến ảnh.</description>
<location>user/filament</location>
</skill>

<skill>
<name>database-backup</name>
<description>Quy trình migration database an toàn với integrational Spatie backup. Luôn backup trước migration, cập nhật schema mermaid.rb. SỬ DỤNG KHI tạo migrations, chạy migrations, khôi phục database, hoặc quản lý thay đổi schema database.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>filament-resource-generator</name>
<description>Tạo Filament resource tự động với namespace imports đúng, nhãn Tiếng Việt, cấu trúc tiêu chuẩn, và best practices. SỬ DỤNG KHI người dùng nói 'tạo resource mới', 'create new resource', 'generate Filament resource', 'scaffold admin resource'.</description>
<location>user/filament</location>
</skill>

<skill>
<name>filament-form-debugger</name>
<description>Chẩn đoán và sửa các lỗi form Filament phổ biến (namespace issues, class not found, type mismatch, argument errors). SỬ DỤNG KHI gặp 'Class not found', 'Argument must be of type', 'Trait not found', hoặc bất kỳ lỗi liên quan Filament nào.</description>
<location>user/filament</location>
</skill>

<skill>
<name>api-design-patterns</name>
<description>Mẫu API design REST và GraphQL toàn diện, best practices, OpenAPI specifications, versioning, authentication, error handling, pagination, rate limiting, và security. SỬ DỤNG KHI thiết kế APIs, tạo endpoints, review specifications, triển khai authentication, xây dựng backend services có khả năng mở rộng, hoặc thiết lập API standards. (Merged từ api-design-principles + api-best-practices)</description>
<location>user/api</location>
</skill>

<skill>
<name>api-cache-invalidation</name>
<description>Hệ thống cache invalidation tự động với Laravel Observers và Next.js On-Demand Revalidation. Tự động sync data real-time giữa backend và frontend khi admin update. SỬ DỤNG KHI người dùng phàn nàn "phải Ctrl+F5 mới thấy data mới", cần setup cache management, sync frontend-backend, hoặc optimize API performance với ISR.</description>
<location>user/api</location>
</skill>

<skill>
<name>docs-seeker</name>
<description>Tìm kiếm tài liệu kỹ thuật trên internet sử dụng tiêu chuẩn llms.txt, repositories GitHub qua Repomix, và khám phá song song. SỬ DỤNG KHI người dùng cần tài liệu mới nhất cho libraries/frameworks, tài liệu định dạng llms.txt, phân tích GitHub repository, hoặc khám phá tài liệu toàn diện từ nhiều nguồn.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>systematic-debugging</name>
<description>Khung debugging 4 pha systematically bắt buộc điều tra root cause trước các sửa chữa. DỪNG fix ngẫu nhiên và patch triệu chứng. SỬ DỤNG KHI gặp bugs, test failures, unexpected behavior, errors, hoặc khi fixes thất bại lặp đi lặp lại. ĐẶC BIỆT SỬ DỤNG khi dưới áp lực thời gian hoặc cảm giác cám dỗ 'quick fix'.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>backend-dev-guidelines</name>
<description>Hướng dẫn phát triển backend toàn diện cho Node.js/Express/TypeScript microservices. Sử dụng khi tạo routes, controllers, services, repositories, middleware, hoặc làm việc với Express APIs, Prisma database access, Sentry error tracking, Zod validation, unifiedConfig, dependency injection, hoặc async patterns. Bao gồm layered architecture (routes → controllers → services → repositories), BaseController pattern, error handling, performance monitoring, testing strategies, và migration từ legacy patterns.</description>
<location>user/fullstack</location>
</skill>

<skill>
<name>frontend-dev-guidelines</name>
<description>Hướng dẫn phát triển frontend cho ứng dụng React/TypeScript. Mẫu hiện đại bao gồm Suspense, lazy loading, useSuspenseQuery, tổ chức file với features directory, MUI v7 styling, TanStack Router, performance optimization, và TypeScript best practices. Sử dụng khi tạo components, pages, features, fetching data, styling, routing, hoặc làm việc với frontend code.</description>
<location>user/fullstack</location>
</skill>

<skill>
<name>ux-designer</name>
<description>Hướng dẫn thiết kế UI/UX chuyên gia để xây dựng giao diện duy nhất, accessible, và user-centered. Sử dụng khi thiết kế giao diện, đưa ra quyết định thiết kế trực quan, chọn colors/typography, triển khai responsive layouts, hoặc khi người dùng đề cập design, UI, UX, styling, hoặc visual appearance. Luôn hỏi trước khi đưa ra quyết định thiết kế.</description>
<location>user/fullstack</location>
</skill>

<skill>
<name>ui-styling</name>
<description>Tạo giao diện người dùng đẹp, accessible với shadcn/ui components (Radix UI + Tailwind CSS), canvas-based visual designs, và responsive layouts. SỬ DỤNG KHI xây dựng user interfaces, triển khai design systems, thêm accessible components (dialogs, dropdowns, forms, tables), tùy chỉnh themes/colors, triển khai dark mode, tạo visual designs/posters, hoặc thiết lập consistent styling patterns.</description>
<location>user/fullstack</location>
</skill>

<skill>
<name>product-search-scoring</name>
<description>Hệ thống tìm kiếm sản phẩm nâng cao với keyword scoring, Vietnamese text normalization, multi-field matching, và ranking kết quả tìm kiếm. Hệ thống multi-layer: text normalization (Vietnamese accents), keyword processing (stop word filtering), query building với filters, và caching strategy. SỬ DỤNG KHI triển khai search functionality, thêm keyword scoring vào products, optimize search algorithm, cải thiện search relevance, xử lý Vietnamese text với accents, hoặc xây dựng e-commerce search features.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>brainstorming</name>
<description>Sử dụng khi tạo hoặc phát triển ý tưởng, trước khi viết code hoặc implementation plans - refines rough ideas thành fully-formed designs thông qua collaborative questioning, alternative exploration, và incremental validation. Hỏi câu hỏi từng cái một, khám phá 2-3 approaches với trade-offs, trình bày design trong các phần (200-300 từ), và validate incrementally. Tài liệu validated designs tới docs/plans/. SỬ DỤNG KHI biến rough ideas thành designs, planning new features, exploring architecture options, trước implementation, hoặc khi người dùng cần trợ giúp refining requirements. Đừng sử dụng trong các quá trình mechanical rõ ràng.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>sequential-thinking</name>
<description>Sử dụng khi các vấn đề phức tạp yêu cầu systematic step-by-step reasoning với khả năng revise thoughts, branch into alternative approaches, hoặc dynamically adjust scope. Cho phép iterative reasoning, revision tracking, branch exploration, và maintained context throughout analysis. Lý tưởng cho multi-stage analysis, design planning, problem decomposition, hoặc tasks với initially unclear scope. SỬ DỤNG KHI problem yêu cầu multiple interconnected reasoning steps, initial scope không rõ ràng, cần filter through complexity, có thể cần backtrack hoặc revise conclusions, hoặc muốn explore alternative solution paths. Đừng sử dụng cho simple queries hoặc single-step tasks.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>writing-plans</name>
<description>Sử dụng khi design hoàn tất và bạn cần detailed implementation tasks cho engineers với zero codebase context - tạo comprehensive implementation plans với exact file paths, complete code examples, và verification steps giả định engineer có minimal domain knowledge. Viết bite-sized tasks (2-5 min mỗi cái), bao gồm exact commands với expected output, tuân theo TDD/DRY/YAGNI principles, và save plans tới docs/plans/. SỬ DỤNG KHI tạo implementation plans, breaking down features thành tasks, documenting step-by-step instructions, sau design/brainstorming phase, hoặc khi người dùng cần detailed execution guide. Offer execution choice: subagent-driven hoặc parallel session.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>api-documentation-writer</name>
<description>Tạo tài liệu API toàn diện cho REST, GraphQL, WebSocket APIs với OpenAPI specs, endpoint descriptions, request/response examples, error codes, authentication guides, và SDKs. Tài liệu tham khảo friendly với developers. SỬ DỤNG KHI người dùng nói 'viết document API', 'tạo API docs', 'generate API documentation', 'document REST endpoints', hoặc cần tạo technical reference cho developers.</description>
<location>user/api</location>
</skill>

<skill>
<name>laravel</name>
<description>Laravel v12 - The PHP Framework For Web Artisans. Hỗ trợ toàn diện routing, Eloquent ORM, migrations, authentication, API development, modern PHP patterns, relationships, middleware, service providers, queues, cache, validation, Laravel Sanctum/Passport. SỬ DỤNG KHI xây dựng Laravel applications/APIs, làm việc với Eloquent models, tạo migrations/seeders/factories, triển khai authentication/authorization, troubleshooting Laravel errors, hoặc tuân theo Laravel best practices.</description>
<location>user/laravel</location>
</skill>

<skill>
<name>laravel-dusk</name>
<description>Laravel Dusk - Browser automation và testing API cho Laravel applications. Hỗ trợ toàn diện writing browser tests, automating UI testing, testing JavaScript interactions, implementing end-to-end tests, sử dụng Page Object pattern, configuring ChromeDriver, waiting for JavaScript events. SỬ DỤNG KHI writing/debugging browser tests, testing user interfaces, implementing E2E testing workflows, làm việc với form submissions/authentication flows, hoặc troubleshooting browser test failures/timing issues.</description>
<location>user/laravel</location>
</skill>

<skill>
<name>laravel-prompts</name>
<description>Laravel Prompts - Beautiful và user-friendly forms cho command-line applications với browser-like features bao gồm placeholder text và validation. Hỗ trợ toàn diện building interactive Artisan commands, text input, select menus, confirmation dialogs, progress bars, loading spinners, tables trong CLI. SỬ DỤNG KHI building Laravel Artisan commands với interactive prompts, tạo CLI applications trong PHP, triển khai form validation trong command-line tools, hoặc testing console commands với prompts.</description>
<location>user/laravel</location>
</skill>

<skill>
<name>web-performance-audit</name>
<description>Thực hiện web performance audits toàn diện đo lường Core Web Vitals (LCP, FID, CLS), page speed, bottleneck identification, và optimization recommendations. Bao gồm performance metrics analysis, optimization strategies (quick wins, medium effort, long-term), monitoring setup, và performance budgets. SỬ DỤNG KHI optimize web performance, cải thiện page speed, phân tích Core Web Vitals, thiết lập performance monitoring, xác định performance bottlenecks, hoặc triển khai performance improvements.</description>
<location>user/optimize</location>
</skill>

<skill>
<name>google-official-seo-guide</name>
<description>Hướng dẫn SEO chính thức Google bao gồm search optimization, Search Console, crawling/indexing, structured data (VideoObject, BroadcastEvent, Clip), mobile-first indexing, internationalization, và search visibility improvements. Tệp tham khảo toàn diện cho appearance, crawling, fundamentals, guides, indexing, và specialty topics. SỬ DỤNG KHI triển khai SEO best practices, thêm structured data, optimize cho Google Search, fix crawling/indexing issues, triển khai schema.org markup, hoặc cải thiện search visibility.</description>
<location>user/optimize</location>
</skill>

<skill>
<name>seo-content-optimizer</name>
<description>Optimize content cho search engines với keyword analysis, readability scoring (Flesch Reading Ease), meta descriptions generation, content structure evaluation, và competitor comparison. Cung cấp actionable SEO recommendations ưu tiên theo impact. SỬ DỤNG KHI optimize blog posts/articles cho SEO, phân tích keyword density, cải thiện content readability, tạo meta tags, xác định content gaps, hoặc cải thiện search rankings.</description>
<location>user/marketing</location>
</skill>

<skill>
<name>databases</name>
<description>Làm việc với MongoDB (document database, BSON documents, aggregation pipelines, Atlas cloud) và PostgreSQL (relational database, SQL queries, psql CLI, pgAdmin). SỬ DỤNG KHI thiết kế database schemas, viết queries và aggregations, optimize indexes cho performance, thực hiện database migrations, configure replication và sharding, triển khai backup và restore strategies, quản lý database users và permissions, phân tích query performance, hoặc administer production databases.</description>
<location>user/database</location>
</skill>

<skill>
<name>database-performance</name>
<description>Phân tích và optimize database performance thông qua index analysis và query profiling. Xác định missing/unused indexes, interpret EXPLAIN plans, tìm bottlenecks, và recommend optimization strategies. SỬ DỤNG KHI optimize slow queries, phân tích database workloads, cải thiện query execution speed, hoặc quản lý database indexes. (Merged từ analyzing-database-indexes + analyzing-query-performance)</description>
<location>user/database</location>
</skill>

<skill>
<name>comparing-database-schemas</name>
<description>So sánh database schemas, tạo migration scripts, và cung cấp rollback procedures sử dụng database-diff-tool plugin. Hỗ trợ PostgreSQL và MySQL. SỬ DỤNG KHI so sánh database schemas qua environments, tạo migration scripts, tạo rollback procedures, đồng bộ database schemas, hoặc validate changes trước deployment.</description>
<location>user/database</location>
</skill>

<skill>
<name>designing-database-schemas</name>
<description>Thiết kế, visualize, và document database schemas với ERD generation, normalization guidance (1NF-BCNF), relationship mapping, và automated documentation. Tạo efficient database structures, generate SQL statements, produce interactive HTML docs, và maintain data dictionaries. SỬ DỤNG KHI thiết kế schemas, tạo database models, tạo ERD diagrams, normalize databases, hoặc document existing databases. (Includes database documentation generation)</description>
<location>user/database</location>
</skill>

<skill>
<name>database-data-generation</name>
<description>Tạo realistic database seed data và test fixtures cho development, testing, và demonstrations. Tạo realistic users, products, orders, và custom schemas sử dụng Faker libraries trong khi maintaining relational integrity và data consistency. SỬ DỤNG KHI populating databases, tạo test fixtures, seeding development environments, hoặc tạo demo data. (Merged từ generating-database-seed-data + generating-test-data)</description>
<location>user/database</location>
</skill>

<skill>
<name>database-validation</name>
<description>Database security scanning toàn diện và data integrity validation. Xác định security vulnerabilities, enforce OWASP compliance, validate data types/formats/ranges, ensure referential integrity, và triển khai business rules. SỬ DỤNG KHI assess database security, check compliance, validate data integrity, hoặc enforce constraints. (Merged từ scanning-database-security + validating-database-integrity)</description>
<location>user/database</location>
</skill>

<skill>
<name>generating-orm-code</name>
<description>Tạo ORM models và database schemas cho các ORMs khác nhau (TypeORM, Prisma, Sequelize, SQLAlchemy, Django ORM, Entity Framework, Hibernate). Hỗ trợ cả database-to-code và code-to-database schema generation. SỬ DỤNG KHI tạo ORM models, tạo database schemas, tạo entities, tạo migrations, hoặc làm việc với ORMs cụ thể.</description>
<location>user/database</location>
</skill>

<skill>
<name>sql-optimization-patterns</name>
<description>Master SQL query optimization, indexing strategies, và EXPLAIN analysis để dramatically cải thiện database performance và eliminate slow queries. SỬ DỤNG KHI debug slow queries, thiết kế database schemas, optimize application performance, hoặc triển khai SQL optimization best practices.</description>
<location>user/database</location>
</skill>



<!-- NEW FRONTEND SKILLS -->

<skill>
<name>frontend-components</name>
<description>Thiết kế reusable, composable UI components tuân theo single responsibility principle với clear interfaces, encapsulation, và minimal props. SỬ DỤNG KHI tạo hoặc sửa đổi component files (.jsx, .tsx, .vue, .svelte), xác định component props/interfaces, triển khai composition patterns, quản lý component-level state, tạo reusable UI elements (buttons, forms, cards, modals), document component APIs, hoặc refactor components cho better reusability.</description>
<location>user/frontend</location>
</skill>

<skill>
<name>frontend-responsive</name>
<description>Xây dựng responsive, mobile-first layouts sử dụng fluid containers, flexible units, media queries, và touch-friendly design. SỬ DỤNG KHI tạo layouts cho mobile/tablet/desktop, triển khai mobile-first design, viết media queries/breakpoints, sử dụng flexible units (rem, em, %), triển khai fluid layouts với flexbox/grid, ensure touch targets meet 44x44px minimum, optimize images cho different screens, hoặc test UI qua multiple device sizes.</description>
<location>user/frontend</location>
</skill>



<skill>
<name>nextjs</name>
<description>Next.js 16 App Router patterns: Server Components, Server Actions, Cache Components với "use cache", async params/searchParams, proxy.ts (replaces middleware.ts), React 19.2, Metadata API, Turbopack. Bao gồm breaking changes, hydration fixes, performance optimization, TypeScript configuration. SỬ DỤNG KHI xây dựng Next.js apps, triển khai Server Components/Actions, xử lý SSR/hydration, sử dụng App Router, hoặc troubleshooting Next.js 16 issues.</description>
<location>user/frontend</location>
</skill>

<skill>
<name>react-component-architecture</name>
<description>Thiết kế scalable React components sử dụng functional components, hooks, composition patterns, và TypeScript. Bao gồm custom hooks, HOCs, render props, compound components, và performance optimization. SỬ DỤNG KHI xây dựng component libraries, thiết kế reusable UI patterns, tạo custom hooks, triển khai component composition, hoặc optimize React performance.</description>
<location>user/frontend</location>
</skill>

<skill>
<name>tailwind-css</name>
<description>Utility-first CSS framework cho rapid UI development với responsive design, dark mode, component patterns, và production optimization. Bao gồm core utilities, breakpoints, state variants, theme customization, và best practices. SỬ DỤNG KHI styling với Tailwind, triển khai responsive designs, tùy chỉnh themes, extract components, hoặc optimize Tailwind cho production.</description>
<location>user/frontend</location>
</skill>

<skill>
<name>ui-design-system</name>
<description>UI design system toolkit để tạo design tokens (colors, typography, spacing), component documentation, responsive calculations, và developer handoff. Bao gồm design_token_generator.py script. SỬ DỤNG KHI tạo design systems, duy trì visual consistency, tạo design tokens, hoặc facilitate design-dev collaboration.</description>
<location>user/frontend</location>
</skill>

<skill>
<name>zustand-state-management</name>
<description>Production-ready Zustand state management cho React với TypeScript, persist middleware, devtools, slices pattern, và Next.js SSR hydration. Ngăn chặn 5 documented issues: hydration mismatches, TypeScript errors, infinite renders, persist middleware problems, slices type inference. SỬ DỤNG KHI thiết lập global state, triển khai persist với localStorage, xử lý Next.js hydration, hoặc migrate từ Redux/Context API.</description>
<location>user/frontend</location>
</skill>

<skill>
<name>cache-optimization</name>
<description>Phân tích và cải thiện application caching strategies: cache hit rates, TTL configurations, cache key design, invalidation strategies. SỬ DỤNG KHI optimize cache performance, cải thiện caching strategy, phân tích cache hit rate, thiết kế cache keys, optimize TTL, hoặc resolve cache-related bottlenecks.</description>
<location>user/frontend</location>
</skill>

<!-- NEW TESTING SKILLS -->

<skill>
<name>e2e-testing-patterns</name>
<description>Master end-to-end testing với Playwright và Cypress: Page Object Model, fixtures, waiting strategies, network mocking, visual regression, accessibility testing. SỬ DỤNG KHI triển khai E2E tests, debug flaky tests, test critical user workflows, thiết lập CI/CD test pipelines, test qua browsers, hoặc thiết lập E2E testing standards.</description>
<location>user/testing</location>
</skill>

<skill>
<name>playwright-automation</name>
<description>Complete browser automation với Playwright: auto-detects dev servers, viết clean test scripts tới /tmp, test pages/forms/responsiveness, take screenshots, validate UX. SỬ DỤNG KHI test websites, automate browser interactions, validate web functionality, perform any browser-based testing, hoặc automate UI tasks.</description>
<location>user/testing</location>
</skill>

<skill>
<name>qa-verification</name>
<description>Comprehensive truth scoring (0.0-1.0 scale), code quality verification, và automatic rollback system với 0.95 accuracy threshold. Real-time reliability metrics cho code, agents, tasks. Automated correctness, security, best practices validation. SỬ DỤNG KHI ensure code quality, triển khai verification checks, track quality metrics, thiết lập automatic rollback, hoặc integrate quality gates vào CI/CD.</description>
<location>user/testing</location>
</skill>

<!-- NEW API SKILL -->



<!-- NEW FULLSTACK SKILLS -->

<skill>
<name>auth-implementation-patterns</name>
<description>Master authentication/authorization patterns: JWT (access/refresh tokens), session-based auth, OAuth2/social login, RBAC, permission-based access control, resource ownership, password security (bcrypt), rate limiting. SỬ DỤNG KHI triển khai auth systems, securing APIs, thêm OAuth2/social login, triển khai RBAC, thiết kế session management, migrate auth systems, hoặc debug security issues.</description>
<location>user/fullstack</location>
</skill>

<skill>
<name>better-auth</name>
<description>Production-ready authentication framework cho TypeScript với Cloudflare D1 support qua Drizzle ORM hoặc Kysely. Self-hosted alternative tới Clerk/Auth.js. Hỗ trợ social providers (Google, GitHub, Microsoft, Apple), email/password, magic links, 2FA, passkeys, organizations, RBAC. QUAN TRỌNG: Yêu cầu Drizzle ORM hoặc Kysely (KHÔNG direct D1 adapter). Ngăn chặn 12 auth errors phổ biến. SỬ DỤNG KHI xây dựng auth cho Cloudflare Workers + D1, cần self-hosted auth solution, migrate từ Clerk, triển khai multi-tenant SaaS, hoặc yêu cầu advanced features (2FA, organizations, RBAC).</description>
<location>user/fullstack</location>
</skill>

<skill>
<name>fastapi-templates</name>
<description>Tạo production-ready FastAPI projects với async patterns, dependency injection, comprehensive error handling. Cấu trúc dự án: api/routes, core/config, models, schemas, services, repositories. Bao gồm CRUD repository pattern, service layer, async database operations. SỬ DỤNG KHI bắt đầu FastAPI projects, xây dựng async REST APIs, tạo high-performance web services, thiết lập API projects với proper structure/testing.</description>
<location>user/fullstack</location>
</skill>

<!-- NEW WORKFLOWS SKILLS -->

<skill>
<name>code-review-excellence</name>
<description>Master effective code review practices: constructive feedback, bug catching, knowledge sharing, team morale maintenance. Quy trình 4 pha (context gathering, high-level review, line-by-line, summary). Bao gồm feedback techniques, severity differentiation, language-specific patterns, architectural review, test quality, security review. SỬ DỤNG KHI review pull requests, thiết lập review standards, mentoring developers, conducting architecture reviews, tạo review checklists, hoặc cải thiện team collaboration.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>git-commit-helper</name>
<description>Tạo descriptive commit messages bằng cách phân tích git diffs tuân theo conventional commits format: type(scope): description. Types: feat, fix, docs, style, refactor, test, chore. Bao gồm multi-file commits, breaking changes, scope examples, validation checklist. SỬ DỤNG KHI viết commit messages, review staged changes, phân tích git diff, hoặc standardize commit message format.</description>
<location>user/workflows</location>
</skill>

<skill>
<name>repomix</name>
<description>Package toàn bộ code repositories thành single AI-friendly files sử dụng Repomix. Khả năng: pack codebases với include/exclude patterns, tạo XML/Markdown/JSON/plain text formats, preserve file structure/context, optimize cho AI consumption với token counting, filter theo file types/directories. SỬ DỤNG KHI packaging codebases cho AI analysis, tạo repository snapshots cho LLM context, phân tích third-party libraries, chuẩn bị cho security audits, tạo documentation context, hoặc evaluate unfamiliar codebases.</description>
<location>user/workflows</location>
</skill>

<!-- NEW META SKILL -->



</available_skills>

---

## 🔧 Nguyên Tắc Cơ Bản

### 1. Chất Lượng Code
- Không để logic hoặc file quá 500 dòng
- Chia logic hợp lý, kế thừa đúng cách
- Tham khảo PLAN.md để hiểu dự án

### 2. Tiêu Chuẩn Filament 4.x
- **QUAN TRỌNG**: Dự án dùng `Schema` thay vì `Form`
- Layout components → `Filament\Schemas\Components\*`
- Form fields → `Filament\Forms\Components\*`
- Get utility → `Filament\Schemas\Components\Utilities\Get`
- **KHÔNG BẢO GIỜ** sử dụng Alpine.js custom code (sử dụng built-in components)

### 3. Quản Lý Database
- **LUÔN** backup trước migration: `php artisan backup:run --only-db`
- Cập nhật `mermaid.rb` khi tạo/sửa migration
- Giữ tối đa 10 bản backup gần nhất

### 4. Tiếng Việt Ưu Tiên
- Tất cả labels, messages phải tiếng Việt
- Date format: `d/m/Y H:i` (31/12/2024 14:30)
- Exception: Code, comments, commit messages (English OK)

---

## 🚨 Tiêu Chuẩn Lập Trình Quan Trọng

### Test/Debug Files Policy

**QUY TẮC: Test files thuộc /tests, cleanup ngay lập tức**

**Vị trí đúng:**
```bash
# ✅ LUÔN đặt trong thư mục /tests
tests/Feature/CheckSomethingTest.php
tests/Unit/FeatureTest.php
tests/Debug/DebugIssueTest.php

# ❌ KHÔNG BAO GIỜ trong project root
check_something.php  # Sai!
test_feature.php     # Sai!
```

**Quy trình:**
1. Tạo test file → CHỈ trong thư mục `/tests`
2. Chạy test & verify
3. **XÓA ngay lập tức sau sử dụng**
4. Ghi chép phát hiện trong `/docs` nếu cần

**Quick cleanup:**
```powershell
# Xóa test files được tạo nhầm trong root
Get-ChildItem -Filter "*test*.php","*check*.php","*debug*.php","*fix*.php" | 
    Where-Object { $_.DirectoryName -notmatch "\\tests\\?" } | 
    Remove-Item -Force
```

### Tổ Chức Tài Liệu

**QUY TẮC: Tổ chức docs theo chuyên đề, không để rải rác**

```
/docs
├── /setup/              # Initial setup guides
├── /architecture/       # System design & database schema
├── /phases/             # Development history
├── /api/                # API documentation
├── /database/           # Database docs
├── /features/           # Feature documentation
├── /features-detailed/  # Deep-dive feature docs
└── /deprecated/         # Outdated documentation
```

**Nguyên tắc:**
- New features → `/docs/[topic]/*.md`
- Setup guides → `/docs/setup/`
- Architecture → `/docs/architecture/`
- Outdated docs → `/docs/deprecated/` hoặc xóa

---

## 🗂️ Cấu Trúc Dự Án

```
E:\Laravel\Laravel12\wincellarClone\wincellarcloneBackend\
├── .claude/
│   ├── global/
│   │   └── SYSTEM.md              # File này
│   └── skills/
│       ├── create-skill/          # Skill creation framework
│       ├── filament-rules/        # Filament coding standards
│       ├── image-management/      # Image system guide
│       ├── database-backup/       # Backup workflow
│       ├── filament-resource-generator/
│       └── filament-form-debugger/
├── docs/                          # Legacy docs (will be deprecated)
├── app/
│   ├── Filament/Resources/
│   ├── Models/
│   └── Observers/
├── database/
│   ├── migrations/
│   └── backups/
├── AGENTS.md                      # Legacy (now references .claude/)
├── PLAN.md                        # Project roadmap
└── mermaid.rb                     # Database schema
```

---

## 📖 Cách Sử Dụng Skills

Skills được **tự động kích hoạt** khi bạn yêu cầu các tác vụ liên quan sử dụng ngôn ngữ tự nhiên.

**Ví dụ:**

```
Người dùng: "Tạo resource mới cho Product"
→ Kích hoạt: filament-resource-generator

Người dùng: "Class not found Tabs"
→ Kích hoạt: filament-form-debugger

Người dùng: "Thêm gallery ảnh vào Article"
→ Kích hoạt: image-management

Người dùng: "Chạy migration mới"
→ Kích hoạt: database-backup

Người dùng: "Tạo skill cho AI Agent"
→ Kích hoạt: create-skill
```

Bạn **không cần** phải nói rõ ràng "sử dụng skill X" - Tôi sẽ tự động phát hiện và kích hoạt skill liên quan dựa trên yêu cầu của bạn.

---

## 🚀 Tham Khảo Nhanh

### Các Lệnh Phổ Biến
```bash
# Development
php artisan serve
npm run dev

# Database
php artisan backup:run --only-db
php artisan migrate
php artisan db:seed

# Filament
php artisan make:filament-resource ResourceName
```

### Tệp Quan Trọng
- **Skills**: `.claude/skills/[skill-name]/SKILL.md`
- **Deep docs**: `.claude/skills/[skill-name]/CLAUDE.md`
- **Project plan**: `PLAN.md`
- **Database schema**: `mermaid.rb`

---

## 🎯 Ví Dụ Quy Trình

### Tạo Filament Resource Mới
1. Yêu cầu: "Tạo resource mới cho Category"
2. Tôi kích hoạt skill `filament-resource-generator`
3. Tạo resource với namespaces đúng, nhãn Tiếng Việt
4. Thêm ImagesRelationManager nếu cần
5. Tạo Observer cho SEO fields
6. Test và verify

### Thêm Image Gallery vào Model
1. Yêu cầu: "Thêm gallery vào Product"
2. Tôi kích hoạt skill `image-management`
3. Thêm morphMany relationship
4. Tạo ImagesRelationManager
5. Triển khai CheckboxList picker
6. Test upload và ordering

### Chạy Database Migration
1. Yêu cầu: "Chạy migration X"
2. Tôi kích hoạt skill `database-backup`
3. Backup database trước tiên
4. Chạy migration
5. Cập nhật mermaid.rb
6. Verify success

---

## 💡 Nhắc Nhở Nguyên Tắc Chính

1. **Progressive Disclosure**: Skills load context khi cần (SKILL.md → CLAUDE.md)
2. **No Duplication**: Reference global context này, không copy
3. **Vietnamese First**: UI phải 100% Tiếng Việt
4. **Backup First**: Luôn backup trước các hoạt động rủi ro
5. **Standards Compliance**: Tuân theo mẫu Filament 4.x
6. **Living Documents**: Skills được cập nhật khi chúng ta học hỏi

---

## 🔗 Tham Khảo Legacy

**Hệ thống cũ (đang deprecated):**
- `AGENTS.md` → Bây giờ references `.claude/` structure
- `docs/filament/` → Migrated tới `.claude/skills/filament-rules/`
- `docs/IMAGE_MANAGEMENT.md` → `.claude/skills/image-management/`
- `docs/spatie_backup.md` → `.claude/skills/database-backup/`

**Sử dụng skill-based system mới cho tất cả công việc trong tương lai.**

---

**Last Updated:** 2025-11-11  
**System Version:** 2.0 (Skill-based architecture)
