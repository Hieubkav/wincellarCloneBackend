# System Architecture & Data Flow Diagrams

## Complete System Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                    ADMIN PANEL (Filament)                            │
│                    FilamentLexicalEditor                             │
│  - User uploads/pastes images                                        │
│  - Images stored as BASE64 in editor                                 │
│  - HTML content contains: <img src="data:image/png;base64,...">     │
└────────────────────────┬─────────────────────────────────────────────┘
                         │ Form Submission
                         ▼
        ┌────────────────────────────┐
        │  Laravel Controller         │
        │  Post::create($data)        │
        │  or $post->update($data)    │
        └────────────────┬────────────┘
                         │ Eloquent Event
                         ▼
╔════════════════════════════════════════════════════════════════════╗
║                    OBSERVER PROCESSES                               ║
╠════════════════════════════════════════════════════════════════════╣
║ saving() Event:                                                    ║
║ ├─ Find: data:image/{type};base64,{data}                         ║
║ ├─ Decode base64 to binary                                       ║
║ ├─ Create: lexical-{time}-{uniqid}.{ext}                         ║
║ ├─ Save to: storage/app/public/uploads/content/                  ║
║ └─ Replace base64 with file URL                                  ║
║                                                                    ║
║ updating() Event:                                                 ║
║ ├─ Delete old featured_image if changed                          ║
║ ├─ Delete old pdf if changed                                     ║
║ └─ Compare content images & delete unused                        ║
║                                                                    ║
║ deleted() Event:                                                  ║
║ ├─ Delete featured_image                                         ║
║ ├─ Delete pdf                                                    ║
║ └─ Delete all content images                                     ║
╚════════════════════════════════════════════════════════════════════╝
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    DATABASE (Posts Table)                           │
│  id | title | slug | content (HTML with URLs) | image | pdf | ... │
│  1  | Post  | post | <h1>...</h1><img src="..."> | ... | ... │    │
└─────────────────────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    FILE STORAGE                                     │
│  storage/app/public/uploads/                                        │
│  ├── content/2025/01/20/lexical-1705779600-abc123.png              │
│  ├── content/2025/01/20/lexical-1705779601-def456.jpg              │
│  └── ...                                                            │
└─────────────────────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    FRONTEND (Public Site)                           │
│  POST->content contains: <img src="/storage/uploads/content/...">  │
│  Browsers fetch images and display rendered HTML                   │
└─────────────────────────────────────────────────────────────────────┘
```

## Base64 Conversion Flow

```
FRONTEND (Lexical Editor)
    │
    ▼ User uploads image
<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA...">
    │
    ▼ Form submitted
PostController@store with $post->content
    │
    ▼ Observer::saving()
convertBase64ToStorage($content)
    │
    ├─ Regex extract:
    │  ├─ Full: data:image/png;base64,iVBORw0...
    │  ├─ Type: png
    │  └─ Data: iVBORw0...
    │
    ├─ Decode:
    │  └─ base64_decode() → binary PNG data
    │
    ├─ Create filename:
    │  └─ lexical-1705779600-5f7a2e1c.png
    │
    ├─ Create directory:
    │  └─ uploads/content/2025/01/20/
    │
    ├─ Save to storage:
    │  └─ Storage::disk('public')->put($path, $data)
    │
    ├─ Generate URL:
    │  └─ /storage/uploads/content/2025/01/20/lexical-1705779600-5f7a.png
    │
    └─ Replace in content:
       OLD: <img src="data:image/png;base64,iVBORw0...">
       NEW: <img src="/storage/uploads/content/2025/01/20/lexical-...png">
    │
    ▼ Save to database
POST record with converted content
    │
    ▼ FRONTEND Display
<img src="/storage/uploads/content/2025/01/20/lexical-...png">
    │
    ▼ Browser loads image from public storage
Image displayed on page
```

## Image Lifecycle (Timeline)

```
T0: Creation
    └─> Admin enters content with base64 image
        └─> Form submitted
            └─> Observer::saving() converts base64 → file
                └─> Post record created with file URL

T1: Display
    └─> Post displayed on public site
        └─> Content rendered with <img src="/storage/...">
            └─> Image loads from file storage

T2: Update
    └─> Admin edits post
        └─> Removes some images, adds new ones
            └─> Observer::updating() compares old vs new
                └─> Deletes unused images
                    └─> Converts new base64 images → files
                        └─> Post updated with new content

T3: Deletion
    └─> Admin deletes post
        └─> Observer::deleted() triggers
            └─> Deletes featured_image
                └─> Deletes pdf
                    └─> Extracts & deletes all content images
                        └─> Post record deleted
                            └─> Storage cleaned up

T4: Cleanup (Scheduled)
    └─> Every Sunday 2 AM
        └─> php artisan images:clean-unused
            └─> Scans database for used images
                └─> Scans storage for physical files
                    └─> Finds orphaned files
                        └─> Deletes unused files
```

## Storage Structure

```
storage/app/public/
├── uploads/
│   ├── featured-posts/
│   │   ├── post-1.jpg         (featured image)
│   │   └── post-2.png
│   │
│   ├── documents/
│   │   ├── guide.pdf          (PDF file)
│   │   └── manual.pdf
│   │
│   └── content/               (Lexical Editor images)
│       ├── 2025/
│       │   ├── 01/
│       │   │   ├── 20/
│       │   │   │   ├── lexical-1705779600-abc123.png
│       │   │   │   ├── lexical-1705779601-def456.jpg
│       │   │   │   └── lexical-1705779602-ghi789.gif
│       │   │   └── 21/
│       │   │       └── ...
│       │   └── 02/
│       │       └── ...
│       └── 2024/
│           └── ...
│
└── service-content/           (For ServicePost model)
    ├── 2025/
    │   └── 01/
    │       └── 20/
    │           └── service-lexical-1705779603-jkl012.png
    └── ...
```

## Component Responsibilities

```
┌──────────────────────────────────────────────────────────────────┐
│                       Model (Post)                               │
│  - Defines attributes (name, content, image, pdf)               │
│  - Observers registered in boot()                               │
│  - Relationships defined                                        │
└─────────────────┬────────────────────────────────────────────────┘
                  │
┌─────────────────▼────────────────────────────────────────────────┐
│                    Observer (PostObserver)                       │
│  - Listen to model events (creating, saving, updating, deleted)  │
│  - Coordinate image processing                                  │
│  - Handle file storage operations                               │
│  - Log all operations                                           │
└─────────────────┬────────────────────────────────────────────────┘
                  │
├─────────────────┴────────────────────────────────────────────────┐
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  convertBase64ToStorage()                                │   │
│  │  - Regex pattern matching                                │   │
│  │  - Base64 decoding                                       │   │
│  │  - File saving                                           │   │
│  │  - URL generation                                        │   │
│  │  - Content replacement                                   │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  deleteOldImage()                                        │   │
│  │  - Check file existence                                  │   │
│  │  - Delete from storage                                   │   │
│  │  - Error handling & logging                              │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  handleContentImages()                                   │   │
│  │  - Extract images from content                           │   │
│  │  - Compare old vs new                                    │   │
│  │  - Delete unused                                         │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  extractImagesFromContent()                              │   │
│  │  - Regex for img src                                     │   │
│  │  - Normalize URLs to paths                               │   │
│  │  - Return unique list                                    │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  saveBase64AsFile()                                      │   │
│  │  - Decode base64                                         │   │
│  │  - Create directory                                      │   │
│  │  - Save file                                             │   │
│  │  - Return storage path                                   │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                  │
└────────────────────────────────────────────────────────────────┘
                  │
                  ▼
        ┌──────────────────┐
        │ Storage Facade   │
        │ (Laravel)        │
        └─────────┬────────┘
                  │
                  ▼
        ┌──────────────────┐
        │ File System      │
        │ (Physical Disk)  │
        └──────────────────┘
```

## Error Handling Flow

```
Base64 Conversion
    ├─ Invalid base64?
    │   └─> Log error & skip image
    │
    ├─ Decode fails?
    │   └─> Throw exception with message
    │
    ├─ Directory creation fails?
    │   └─> Throw exception & log
    │
    ├─ File save fails?
    │   └─> Throw exception & rollback
    │
    └─ Success?
        └─> Continue with next image

Image Deletion
    ├─ File not found?
    │   └─> Log warning & continue
    │
    ├─ Permission denied?
    │   └─> Log error & continue
    │
    └─ Success?
        └─> Log info

Content Image Cleanup
    ├─ Invalid regex?
    │   └─> Return empty array
    │
    ├─ No images found?
    │   └─> Return empty array
    │
    └─ Images found?
        └─> Delete each one
```

## Performance Considerations

```
Operation              Time      Space        Notes
─────────────────────────────────────────────────────
Base64 decode        10-50ms    10-50MB      Depends on image size
File save            5-20ms     1-5MB        Disk I/O
Regex matching       1-5ms      1MB          Pattern matching
Database query       10-30ms    1MB          SELECT from posts
Directory creation   1-5ms      -            OS dependent
File deletion        2-10ms     -            Disk I/O
─────────────────────────────────────────────────────

Optimization Tips:
✅ Limit image size (5MB max recommended)
✅ Resize large images on upload
✅ Use compression (WebP format)
✅ Store date-based directories for faster lookups
✅ Batch cleanup during off-peak hours
✅ Monitor disk I/O and adjust scheduling
```

## Cleanup Command Flow

```
Start: php artisan images:clean-unused
    │
    ├─ Option: --dry-run?
    │   └─ Set dryRun flag
    │
    ├─ Scan database
    │   ├─ Post::all()->content
    │   ├─ ServicePost::all()->content
    │   └─ Collect all used image paths
    │
    ├─ Scan storage
    │   ├─ File::allFiles('uploads/')
    │   ├─ Get relative paths
    │   └─ Collect file sizes
    │
    ├─ Find differences
    │   └─ Unused = storage - database
    │
    ├─ Display results
    │   ├─ List unused files
    │   ├─ Show total size
    │   └─ Ask for confirmation
    │
    ├─ Confirm?
    │   ├─ No → Exit
    │   └─ Yes → Continue
    │
    ├─ Delete files
    │   ├─ For each unused file
    │   │   ├─ Storage::disk('public')->delete()
    │   │   └─ Log result
    │   └─ Progress bar
    │
    └─ Complete
        ├─ Show summary
        ├─ Files deleted: N
        ├─ Space freed: X.X MB
        └─ Done!
```

This architecture ensures:
- ✅ **Automatic processing** via Observers
- ✅ **No manual intervention** needed
- ✅ **Safety mechanisms** with dry-run mode
- ✅ **Comprehensive logging** for debugging
- ✅ **Efficient cleanup** with scheduling
- ✅ **Error recovery** with detailed logs
