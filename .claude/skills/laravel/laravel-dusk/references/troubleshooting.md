## Troubleshooting

### Common Issues

**ChromeDriver version mismatch:**
```bash
php artisan dusk:chrome-driver --detect
```

**Elements not found:**
- Use `waitFor('.selector')` before interacting
- Check if element is in an iframe
- Verify selector with browser dev tools

**Tests failing randomly:**
- Replace `pause()` with explicit waits
- Increase timeout: `waitFor('.selector', 10)`
- Use `waitUntil()` for JavaScript conditions

**Database state issues:**
- Use `DatabaseTruncation` trait
- Reset data in `setUp()` method
- Check for transactions in application code
