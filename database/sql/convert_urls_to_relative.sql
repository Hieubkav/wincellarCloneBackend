-- ============================================
-- Convert Absolute URLs to Relative Paths
-- ============================================
-- Mục đích: Chuyển URL localhost thành đường dẫn tương đối
-- Ví dụ: http://127.0.0.1:8000/storage/... => /storage/...
--
-- BACKUP DATABASE TRƯỚC KHI CHẠY!
-- ============================================

-- 1. Update home_components.config (JSON column chứa banner images)
UPDATE home_components 
SET config = REPLACE(config, 'http://127.0.0.1:8000/storage/', '/storage/'),
    updated_at = NOW()
WHERE config LIKE '%http://127.0.0.1:8000/storage/%';

UPDATE home_components 
SET config = REPLACE(config, 'http://localhost:8000/storage/', '/storage/'),
    updated_at = NOW()
WHERE config LIKE '%http://localhost:8000/storage/%';

UPDATE home_components 
SET config = REPLACE(config, 'https://127.0.0.1:8000/storage/', '/storage/'),
    updated_at = NOW()
WHERE config LIKE '%https://127.0.0.1:8000/storage/%';

UPDATE home_components 
SET config = REPLACE(config, 'https://localhost:8000/storage/', '/storage/'),
    updated_at = NOW()
WHERE config LIKE '%https://localhost:8000/storage/%';

-- 2. Update articles.content (HTML content có thể chứa embedded images)
UPDATE articles 
SET content = REPLACE(content, 'http://127.0.0.1:8000/storage/', '/storage/'),
    updated_at = NOW()
WHERE content LIKE '%http://127.0.0.1:8000/storage/%';

UPDATE articles 
SET content = REPLACE(content, 'http://localhost:8000/storage/', '/storage/'),
    updated_at = NOW()
WHERE content LIKE '%http://localhost:8000/storage/%';

UPDATE articles 
SET content = REPLACE(content, 'https://127.0.0.1:8000/storage/', '/storage/'),
    updated_at = NOW()
WHERE content LIKE '%https://127.0.0.1:8000/storage/%';

UPDATE articles 
SET content = REPLACE(content, 'https://localhost:8000/storage/', '/storage/'),
    updated_at = NOW()
WHERE content LIKE '%https://localhost:8000/storage/%';

-- 3. Update products.description (nếu có embedded images)
UPDATE products 
SET description = REPLACE(description, 'http://127.0.0.1:8000/storage/', '/storage/'),
    updated_at = NOW()
WHERE description LIKE '%http://127.0.0.1:8000/storage/%';

UPDATE products 
SET description = REPLACE(description, 'http://localhost:8000/storage/', '/storage/'),
    updated_at = NOW()
WHERE description LIKE '%http://localhost:8000/storage/%';

UPDATE products 
SET description = REPLACE(description, 'https://127.0.0.1:8000/storage/', '/storage/'),
    updated_at = NOW()
WHERE description LIKE '%https://127.0.0.1:8000/storage/%';

UPDATE products 
SET description = REPLACE(description, 'https://localhost:8000/storage/', '/storage/'),
    updated_at = NOW()
WHERE description LIKE '%https://localhost:8000/storage/%';

-- ============================================
-- Verify changes (SELECT queries to check)
-- ============================================

-- Check if any localhost URLs remain in home_components
SELECT id, type, 
       CASE 
           WHEN config LIKE '%127.0.0.1:8000%' THEN 'STILL HAS LOCALHOST'
           WHEN config LIKE '%localhost:8000%' THEN 'STILL HAS LOCALHOST'
           ELSE 'OK'
       END as status
FROM home_components 
WHERE config LIKE '%127.0.0.1:8000%' 
   OR config LIKE '%localhost:8000%';

-- Check articles
SELECT id, title,
       CASE 
           WHEN content LIKE '%127.0.0.1:8000%' THEN 'STILL HAS LOCALHOST'
           WHEN content LIKE '%localhost:8000%' THEN 'STILL HAS LOCALHOST'
           ELSE 'OK'
       END as status
FROM articles 
WHERE content LIKE '%127.0.0.1:8000%' 
   OR content LIKE '%localhost:8000%';

-- Check products
SELECT id, name,
       CASE 
           WHEN description LIKE '%127.0.0.1:8000%' THEN 'STILL HAS LOCALHOST'
           WHEN description LIKE '%localhost:8000%' THEN 'STILL HAS LOCALHOST'
           ELSE 'OK'
       END as status
FROM products 
WHERE description LIKE '%127.0.0.1:8000%' 
   OR description LIKE '%localhost:8000%';
