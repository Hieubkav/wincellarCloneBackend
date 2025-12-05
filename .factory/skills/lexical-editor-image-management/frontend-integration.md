# Frontend Integration Guide

Complete guide for integrating Lexical Editor on the frontend with automatic image upload handling.

## Filament Resource Configuration

### Basic Setup

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Malzariey\FilamentLexicalEditor\FilamentLexicalEditor;
use Malzariey\FilamentLexicalEditor\Enums\ToolbarItem;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Posts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Info')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Post Title')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                    ])->columns(2),

                Forms\Components\Section::make('Content')
                    ->schema([
                        FilamentLexicalEditor::make('content')
                            ->label('Rich Content')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('💡 Tip: Images are automatically converted from base64 to storage files')
                            ->enabledToolbars([
                                ToolbarItem::UNDO,
                                ToolbarItem::REDO,
                                ToolbarItem::DIVIDER,
                                ToolbarItem::BOLD,
                                ToolbarItem::ITALIC,
                                ToolbarItem::UNDERLINE,
                                ToolbarItem::STRIKETHROUGH,
                                ToolbarItem::DIVIDER,
                                ToolbarItem::H1,
                                ToolbarItem::H2,
                                ToolbarItem::H3,
                                ToolbarItem::DIVIDER,
                                ToolbarItem::FONT_FAMILY,
                                ToolbarItem::FONT_SIZE,
                                ToolbarItem::TEXT_COLOR,
                                ToolbarItem::BACKGROUND_COLOR,
                                ToolbarItem::DIVIDER,
                                ToolbarItem::LEFT,
                                ToolbarItem::CENTER,
                                ToolbarItem::RIGHT,
                                ToolbarItem::JUSTIFY,
                                ToolbarItem::DIVIDER,
                                ToolbarItem::BULLET,
                                ToolbarItem::NUMBERED,
                                ToolbarItem::QUOTE,
                                ToolbarItem::DIVIDER,
                                ToolbarItem::INDENT,
                                ToolbarItem::OUTDENT,
                                ToolbarItem::DIVIDER,
                                ToolbarItem::HR,
                                ToolbarItem::IMAGE,
                                ToolbarItem::CLEAR,
                            ]),
                    ]),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Cover Image')
                            ->image()
                            ->directory('uploads')
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('pdf')
                            ->label('PDF File')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('uploads')
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
```

## Complete Filament Setup

### Step 1: Install Dependencies

```bash
composer require filament/filament
composer require malzariey/filament-lexical-editor
npm install
npm run build
```

### Step 2: Create Resource

```bash
php artisan filament:resource Post --generate
```

### Step 3: Configure PostResource

Use the configuration above.

### Step 4: Create Pages

```bash
php artisan filament:page CreatePost --resource=PostResource
php artisan filament:page EditPost --resource=PostResource
php artisan filament:page ListPosts --resource=PostResource
```

### Step 5: Publish Assets

```bash
php artisan filament:install
npm run build
```

## Frontend Display

### Display Rich Content in Blade Template

```blade
<!-- resources/views/post/show.blade.php -->

@extends('layouts.app')

@section('content')
<article class="post-container">
    <header class="post-header">
        <h1>{{ $post->name }}</h1>
        <time datetime="{{ $post->created_at->toIso8601String() }}">
            {{ $post->created_at->format('Y-m-d H:i') }}
        </time>
    </header>

    @if($post->image)
        <img 
            src="{{ Storage::disk('public')->url($post->image) }}" 
            alt="{{ $post->name }}"
            class="post-cover-image"
        >
    @endif

    <!-- Rich content from Lexical Editor -->
    <div class="post-content lexical-content">
        {!! $post->content !!}
    </div>

    @if($post->pdf)
        <a href="{{ Storage::disk('public')->url($post->pdf) }}" 
           class="btn btn-download" 
           download>
            📥 Download PDF
        </a>
    @endif
</article>
@endsection
```

### CSS Styling

```css
/* resources/css/lexical-content.css */

/* Container styling */
.lexical-content {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.6;
    color: #333;
    max-width: 800px;
    margin: 2rem auto;
}

/* Text styling */
.lexical-content p {
    margin: 1rem 0;
}

.lexical-content strong, 
.lexical-content b {
    font-weight: 600;
}

.lexical-content em, 
.lexical-content i {
    font-style: italic;
}

.lexical-content u {
    text-decoration: underline;
}

.lexical-content del, 
.lexical-content strike {
    text-decoration: line-through;
}

/* Headings */
.lexical-content h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 2rem 0 1rem;
    line-height: 1.2;
}

.lexical-content h2 {
    font-size: 2rem;
    font-weight: 700;
    margin: 1.75rem 0 0.875rem;
    line-height: 1.3;
}

.lexical-content h3 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 1.5rem 0 0.75rem;
    line-height: 1.4;
}

/* Lists */
.lexical-content ul, 
.lexical-content ol {
    margin: 1rem 0;
    padding-left: 2rem;
}

.lexical-content li {
    margin: 0.5rem 0;
}

.lexical-content ul li {
    list-style-type: disc;
}

.lexical-content ol li {
    list-style-type: decimal;
}

/* Alignment */
.lexical-content .text-left {
    text-align: left;
}

.lexical-content .text-center {
    text-align: center;
}

.lexical-content .text-right {
    text-align: right;
}

.lexical-content .text-justify {
    text-align: justify;
}

/* Blockquotes */
.lexical-content blockquote {
    border-left: 4px solid #ddd;
    margin: 1.5rem 0;
    padding: 0.5rem 0 0.5rem 1rem;
    color: #666;
    font-style: italic;
}

/* Horizontal rule */
.lexical-content hr {
    margin: 2rem 0;
    border: none;
    border-top: 1px solid #ddd;
}

/* Images */
.lexical-content img {
    max-width: 100%;
    height: auto;
    margin: 1.5rem 0;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Inline code */
.lexical-content code {
    background-color: #f5f5f5;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

/* Code blocks */
.lexical-content pre {
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 1rem;
    overflow-x: auto;
    margin: 1rem 0;
}

.lexical-content pre code {
    background-color: transparent;
    padding: 0;
    font-size: 1em;
}

/* Tables */
.lexical-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

.lexical-content th,
.lexical-content td {
    border: 1px solid #ddd;
    padding: 0.75rem;
    text-align: left;
}

.lexical-content th {
    background-color: #f5f5f5;
    font-weight: 600;
}

.lexical-content tr:nth-child(even) {
    background-color: #fafafa;
}

/* Responsive */
@media (max-width: 768px) {
    .lexical-content {
        max-width: 100%;
        padding: 1rem;
    }

    .lexical-content h1 {
        font-size: 2rem;
    }

    .lexical-content h2 {
        font-size: 1.5rem;
    }

    .lexical-content h3 {
        font-size: 1.25rem;
    }

    .lexical-content img {
        margin: 1rem 0;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .lexical-content {
        color: #e0e0e0;
    }

    .lexical-content blockquote {
        border-left-color: #555;
        color: #999;
    }

    .lexical-content code {
        background-color: #2a2a2a;
        color: #e0e0e0;
    }

    .lexical-content pre {
        background-color: #2a2a2a;
        border-color: #444;
    }

    .lexical-content table {
        border-color: #444;
    }

    .lexical-content th {
        background-color: #333;
    }

    .lexical-content tr:nth-child(even) {
        background-color: #2a2a2a;
    }
}
```

## Vue Component Example

```vue
<!-- resources/js/components/LexicalContentViewer.vue -->

<template>
    <div class="lexical-content-wrapper">
        <article v-if="post" class="post-article">
            <header class="post-header">
                <h1>{{ post.name }}</h1>
                <div class="post-meta">
                    <time :datetime="post.created_at">
                        {{ formatDate(post.created_at) }}
                    </time>
                </div>
            </header>

            <figure v-if="post.image" class="post-cover">
                <img 
                    :src="getImageUrl(post.image)" 
                    :alt="post.name"
                    loading="lazy"
                >
            </figure>

            <div class="post-content lexical-content" v-html="post.content"></div>

            <footer v-if="post.pdf" class="post-footer">
                <a :href="getImageUrl(post.pdf)" class="btn btn-download" download>
                    <span>📥</span> Download PDF
                </a>
            </footer>
        </article>
    </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
    postId: {
        type: Number,
        required: true,
    },
})

const post = ref(null)
const loading = ref(true)
const error = ref(null)

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    })
}

const getImageUrl = (path) => {
    if (!path) return null
    return `/storage/${path}`
}

onMounted(async () => {
    try {
        const response = await axios.get(`/api/posts/${props.postId}`)
        post.value = response.data
    } catch (err) {
        error.value = err.message
    } finally {
        loading.value = false
    }
})
</script>

<style scoped>
.lexical-content-wrapper {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.post-article {
    line-height: 1.6;
}

.post-header {
    margin-bottom: 2rem;
}

.post-header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.post-meta {
    color: #666;
    font-size: 0.9rem;
}

.post-cover {
    margin: 2rem 0;
}

.post-cover img {
    width: 100%;
    height: auto;
    border-radius: 8px;
}

.post-footer {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #eee;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.btn:hover {
    background-color: #0056b3;
}

@media (max-width: 768px) {
    .lexical-content-wrapper {
        padding: 1rem;
    }

    .post-header h1 {
        font-size: 1.75rem;
    }
}
</style>
```

## React Component Example

```jsx
// resources/js/components/LexicalContentViewer.jsx

import React, { useState, useEffect } from 'react'
import axios from 'axios'

export default function LexicalContentViewer({ postId }) {
    const [post, setPost] = useState(null)
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState(null)

    useEffect(() => {
        const fetchPost = async () => {
            try {
                const response = await axios.get(`/api/posts/${postId}`)
                setPost(response.data)
            } catch (err) {
                setError(err.message)
            } finally {
                setLoading(false)
            }
        }

        fetchPost()
    }, [postId])

    if (loading) return <div>Loading...</div>
    if (error) return <div>Error: {error}</div>
    if (!post) return <div>Post not found</div>

    const getImageUrl = (path) => {
        return path ? `/storage/${path}` : null
    }

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        })
    }

    return (
        <article className="post-container">
            <header className="post-header">
                <h1>{post.name}</h1>
                <time dateTime={post.created_at}>
                    {formatDate(post.created_at)}
                </time>
            </header>

            {post.image && (
                <figure className="post-cover">
                    <img 
                        src={getImageUrl(post.image)} 
                        alt={post.name}
                        loading="lazy"
                    />
                </figure>
            )}

            <div 
                className="post-content lexical-content"
                dangerouslySetInnerHTML={{ __html: post.content }}
            />

            {post.pdf && (
                <footer className="post-footer">
                    <a 
                        href={getImageUrl(post.pdf)} 
                        className="btn btn-download"
                        download
                    >
                        📥 Download PDF
                    </a>
                </footer>
            )}
        </article>
    )
}
```

## API Endpoint Example

```php
// routes/api.php

use App\Models\Post;

Route::get('/posts/{id}', function ($id) {
    return Post::findOrFail($id);
});

// Or with more control
Route::apiResource('posts', PostController::class)->only(['show']);
```

## Real-World Example

### Complete User Flow

1. **Admin edits post** in Filament:
   - Uploads cover image
   - Pastes content with embedded images in Lexical Editor
   - Uploads PDF file

2. **Observer processes images**:
   - Converts base64 images to files
   - Saves to `storage/app/public/uploads/content/`
   - Updates content with file URLs

3. **Database stores**:
   - Post name, category, slug
   - Content with image URLs
   - Cover image path
   - PDF path

4. **Frontend displays**:
   - Renders HTML from content field
   - Shows cover image with proper alt text
   - Provides PDF download link
   - Applies responsive styling

### Example Post Content

After Observer processing:
```html
<h1>Understanding Web Performance</h1>

<p>Performance is crucial for user experience. Here's what we learned:</p>

<img src="/storage/uploads/content/lexical-1701234567-abc123.png" alt="Performance metrics">

<h2>Key Metrics</h2>

<ul>
  <li>First Contentful Paint (FCP)</li>
  <li>Largest Contentful Paint (LCP)</li>
  <li>Cumulative Layout Shift (CLS)</li>
</ul>

<blockquote>
  <p>Good performance is a feature, not a luxury.</p>
</blockquote>

<img src="/storage/uploads/content/lexical-1701234568-def456.jpg" alt="Load time comparison">

<p>Remember to optimize regularly!</p>
```

## Best Practices

1. **Lazy load images**
   ```blade
   <img src="..." loading="lazy" alt="description">
   ```

2. **Responsive images**
   ```blade
   <picture>
       <source srcset="..." media="(max-width: 768px)">
       <img src="..." alt="description">
   </picture>
   ```

3. **Image optimization**
   - Serve WebP with fallback
   - Compress images
   - Use CDN for delivery

4. **Security**
   - Sanitize HTML output
   - Validate file uploads
   - Rate limit image uploads

5. **Performance**
   - Use pagination for large content
   - Lazy load images
   - Cache rendered content
   - Use CDN for static assets
