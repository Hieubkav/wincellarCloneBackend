# Lexical Editor Image Management Skill

This skill provides comprehensive guidance for implementing Lexical Editor with automatic image management using Laravel Observers. Transform base64 images into optimized storage files and automatically clean up unused images.

## 📚 Documentation Structure

### Core Files

1. **[SKILL.md](./SKILL.md)** - Main skill documentation
   - Overview and when to use this skill
   - Quick start guide
   - Architecture and workflow
   - Best practices
   - Common issues and solutions
   - Testing strategies
   - Validation checklist

2. **[observer-implementation.md](./observer-implementation.md)** - Observer pattern guide
   - Complete Observer class template
   - Step-by-step integration
   - Customization options
   - Event lifecycle
   - Testing the observer
   - Troubleshooting

3. **[cleanup-command.md](./cleanup-command.md)** - Image cleanup automation
   - Full console command implementation
   - Installation and usage
   - Scheduling as cron job
   - Advanced usage (custom models, filters, reports)
   - Testing and monitoring
   - Best practices

4. **[frontend-integration.md](./frontend-integration.md)** - Frontend implementation
   - Filament Resource configuration
   - Blade template examples
   - CSS styling (responsive, dark mode)
   - Vue component example
   - React component example
   - API endpoints
   - Real-world examples

5. **[EXAMPLES.md](./EXAMPLES.md)** - Production examples
   - Blog post editor system
   - Service descriptions with multiple editors
   - News article with scheduled cleanup
   - Multi-language content
   - Performance optimization
   - Complete testing suite

## 🚀 Quick Start

### 1. Install Dependencies
```bash
composer require malzariey/filament-lexical-editor
npm install
```

### 2. Create Observer
Copy the complete Observer class from [observer-implementation.md](./observer-implementation.md) and register it in `EventServiceProvider`.

### 3. Configure Filament Resource
Use the template from [frontend-integration.md](./frontend-integration.md) to set up your admin interface.

### 4. Test It Works
```bash
php artisan tinker
$post = App\Models\Post::create([
    'name' => 'Test',
    'content' => '<img src="data:image/png;base64,iVBORw0...">',
]);
dd($post->content); // Should show /storage/uploads/content/lexical-xxx.png
```

## 🎯 Key Features

✅ **Automatic Base64 Conversion**
- Converts base64 images to actual files
- Maintains proper file naming (timestamp + uniqid)
- Handles multiple image formats (jpg, png, gif, webp, svg)

✅ **Smart Cleanup**
- Deletes old images when new ones are uploaded
- Removes unused images when content is edited
- Cleans up all files when post is deleted
- Includes dry-run mode for safety

✅ **Production Ready**
- Comprehensive error handling
- Detailed logging for debugging
- Soft delete support
- Database transaction safety

✅ **Well Tested**
- Unit test examples
- Feature test examples
- Real-world test cases
- Integration testing patterns

## 📂 Storage Structure

```
storage/app/public/
├── uploads/
│   ├── [main-images].jpg              # Main content images
│   ├── [documents].pdf                # PDF files
│   └── content/
│       ├── lexical-170123456-abc.jpg  # Post content images
│       ├── lexical-170123457-def.png
│       └── lexical-170123458-ghi.gif
└── service-content/                   # For ServicePost model
    ├── service-lexical-170123-jkl.jpg
    └── service-lexical-170123-mno.png
```

## 🔄 Event Lifecycle

```
Creating → Saving → Insert/Update → Updating → Deleted → ForceDeleted
   │         │                         │          │          │
   │         │                         │          │          └─> Delete all files
   │         │                         │          └─> Compare & delete unused
   │         │                         └─> Delete old image/PDF
   │         └─> Convert base64 → files
   └─> Auto-generate slug
```

## 💡 Use Cases

### Blog Platform
- Rich content with embedded images
- Automatic image optimization
- Cleanup old posts and images

### Service Directory
- Multiple service descriptions
- Image galleries in content
- Client portfolio showcases

### News Website
- High-volume content creation
- Automatic image management
- Scheduled cleanup jobs

### Multi-Language Site
- Separate images per language
- Translation-aware storage
- Localized cleanup

### Document Management
- PDF uploads with content
- Image extraction from PDFs
- Archive old documents

## 📖 Learning Path

### Beginner
1. Read [SKILL.md](./SKILL.md) overview
2. Follow Quick Start section
3. Study [observer-implementation.md](./observer-implementation.md) basics
4. Test with simple example

### Intermediate
1. Review [frontend-integration.md](./frontend-integration.md)
2. Implement in your Filament resource
3. Test image conversion manually
4. Review logging output

### Advanced
1. Study [cleanup-command.md](./cleanup-command.md)
2. Implement scheduled cleanup
3. Review [EXAMPLES.md](./EXAMPLES.md) for patterns
4. Add custom optimizations
5. Set up monitoring and alerts

### Expert
1. Customize storage paths per model
2. Implement image resizing/compression
3. Add CDN integration
4. Create custom cleanup strategies
5. Implement advanced monitoring

## 🛠️ Common Tasks

### Setup New Model
1. Create migration with `content` column
2. Create Observer using template
3. Register Observer in EventServiceProvider
4. Create Filament Resource
5. Test image conversion

### Add Image Cleanup
1. Generate console command
2. Implement methods from [cleanup-command.md](./cleanup-command.md)
3. Schedule in Kernel.php
4. Test with --dry-run
5. Enable automated cleanup

### Optimize Images
1. Add Intervention\Image package
2. Implement resizing in Observer
3. Add compression logic
4. Test with various formats
5. Monitor storage usage

### Monitor Cleanup
1. Set up Slack notifications
2. Create monitoring dashboard
3. Schedule weekly reports
4. Alert on failures
5. Track freed space

## 🧪 Testing

All documentation includes complete testing examples:
- Unit tests for image conversion
- Feature tests for observer events
- Integration tests for cleanup
- Real-world test cases

Run tests:
```bash
php artisan test
# or
phpunit tests/Feature/LexicalEditorTest.php
```

## 📊 Monitoring

### View Logs
```bash
tail -f storage/logs/laravel.log | grep lexical
```

### Check Storage Usage
```bash
du -sh storage/app/public/uploads
find storage/app/public/uploads -type f | wc -l
```

### Run Cleanup Preview
```bash
php artisan images:clean-unused --dry-run
```

### Verify Observer Running
```bash
php artisan tinker
>>> Post::getObservableEvents()
```

## 🐛 Troubleshooting

### Images Not Converting?
→ See [observer-implementation.md](./observer-implementation.md) Troubleshooting section

### Files Not Deleting?
→ Check permissions: `chmod -R 775 storage/app/public`

### Observer Not Running?
→ Clear cache: `php artisan optimize:clear`

### Storage Full?
→ Run cleanup: `php artisan images:clean-unused --dry-run`

## 🔐 Security Considerations

✅ **File Upload Validation**
- Verify base64 data integrity
- Check file size limits
- Validate MIME types

✅ **Storage Security**
- Keep uploads outside webroot
- Use proper permissions (644 for files, 755 for dirs)
- Implement rate limiting

✅ **Cleanup Safety**
- Always test with --dry-run first
- Backup storage before cleanup
- Keep detailed logs

## 📈 Performance Tips

1. **Image Optimization**
   - Use WebP format where possible
   - Resize large images automatically
   - Compress before storage

2. **Database**
   - Index content fields if searching
   - Use pagination for large lists
   - Cache rendered content

3. **Storage**
   - Use CDN for static assets
   - Implement lazy loading
   - Archive old content regularly

4. **Cleanup**
   - Schedule during off-peak hours
   - Use batch processing
   - Monitor progress

## 🎓 Best Practices

### Code Quality
- ✅ Comprehensive error handling
- ✅ Detailed logging at each step
- ✅ Type hints on all methods
- ✅ Clear comments explaining logic

### Storage Management
- ✅ Organize by model type
- ✅ Use date-based subdirectories
- ✅ Meaningful file naming
- ✅ Regular cleanup

### Testing
- ✅ Unit tests for conversions
- ✅ Feature tests for events
- ✅ Integration tests for workflow
- ✅ Real data testing

### Documentation
- ✅ Comments in code
- ✅ README files in directories
- ✅ Migration guides
- ✅ Troubleshooting guides

## 📞 Support Resources

### Documentation
- [Laravel Observers](https://laravel.com/docs/eloquent#observers)
- [Filament Forms](https://filamentphp.com/docs/3.x/forms)
- [Lexical Editor](https://lexical.dev/)
- [Intervention Image](https://image.intervention.io/)

### External Links
- [Malzariey Filament Lexical Editor](https://github.com/malzariey/filament-lexical-editor)
- [Laravel File Storage](https://laravel.com/docs/filesystem)
- [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary/v10/introduction)

## 📝 Changelog

### Version 1.0.0 (Initial Release)
- Complete Observer implementation guide
- Cleanup command with scheduling
- Frontend integration examples
- Production-ready examples
- Comprehensive testing guides
- Performance optimization tips

## 💰 License

This skill is provided as part of the PHP Architecture course and project templates. Use freely in your projects.

## ✨ Next Steps

1. **Choose your starting point**
   - New project? → Start with [SKILL.md](./SKILL.md) overview
   - Adding to existing project? → [observer-implementation.md](./observer-implementation.md)
   - Updating admin interface? → [frontend-integration.md](./frontend-integration.md)

2. **Follow the guides**
   - Each section includes step-by-step instructions
   - Copy code examples directly
   - Test at each stage

3. **Deploy with confidence**
   - Review all validation checklists
   - Test thoroughly with provided examples
   - Monitor logs and storage after deployment

## 🚀 Ready to Get Started?

👉 **Begin with [SKILL.md](./SKILL.md)** for the complete guide and architecture overview.

---

**Last Updated:** December 2024
**Version:** 1.0.0
**Status:** Production Ready ✅
