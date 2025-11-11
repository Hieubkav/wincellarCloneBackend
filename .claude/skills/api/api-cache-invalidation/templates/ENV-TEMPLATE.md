# Environment Variables Template

## Backend (.env)

```bash
# Next.js On-Demand Revalidation
NEXT_REVALIDATE_URL=http://localhost:3000/api/revalidate
NEXT_REVALIDATE_SECRET=wincellar-secret-2025-change-in-production
```

### Production Backend (.env)

```bash
# Next.js On-Demand Revalidation (PRODUCTION)
NEXT_REVALIDATE_URL=https://yourdomain.com/api/revalidate
NEXT_REVALIDATE_SECRET=<GENERATE_STRONG_SECRET_64_CHARS>
```

---

## Frontend (.env.local)

```bash
# API Backend
NEXT_PUBLIC_API_BASE_URL=http://127.0.0.1:8000/api

# Media hosts
NEXT_PUBLIC_MEDIA_HOSTS=http://127.0.0.1:8000

# Revalidation Secret (MUST match backend)
REVALIDATE_SECRET=wincellar-secret-2025-change-in-production
```

### Production Frontend (.env.production)

```bash
# API Backend (PRODUCTION)
NEXT_PUBLIC_API_BASE_URL=https://api.yourdomain.com/api

# Media hosts
NEXT_PUBLIC_MEDIA_HOSTS=https://api.yourdomain.com

# Revalidation Secret (MUST match backend)
REVALIDATE_SECRET=<SAME_AS_BACKEND_SECRET>
```

---

## Generate Strong Secret

```bash
# Method 1: OpenSSL (recommended)
openssl rand -base64 48

# Method 2: PHP
php -r "echo bin2hex(random_bytes(32));"

# Method 3: Node.js
node -e "console.log(require('crypto').randomBytes(32).toString('base64'))"

# Method 4: Online
# Visit: https://www.random.org/strings/
# Settings: 64 characters, alphanumeric
```

---

## Security Checklist

- [ ] Secret is at least 48 characters
- [ ] Different secrets for dev/staging/production
- [ ] Secrets not committed to git
- [ ] Backend and frontend secrets match
- [ ] HTTPS only in production
