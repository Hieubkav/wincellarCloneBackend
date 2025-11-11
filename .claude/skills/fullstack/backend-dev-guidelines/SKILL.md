---
name: backend-dev-guidelines
description: Node.js/Express/TypeScript microservices development. Layered architecture (routes → controllers → services → repositories), BaseController, error handling, Sentry monitoring, Prisma, Zod validation, dependency injection. USE WHEN creating routes, controllers, services, repositories, middleware, API endpoints, database access, error tracking.
---
## When to Use

Creating routes, controllers, services, repositories, middleware, APIs, database queries, error handling.

## Quick Checklist

**New API Endpoint:**
- Route in `routes/` with verb naming (`getUser`, `createProduct`)
- Controller extends `BaseController`
- Service in `services/` with business logic
- Repository in `repositories/` with Prisma queries
- Zod validation for request data
- Error handling with `AppError`
- Sentry integration for monitoring

## Layered Architecture

```
Route → Controller → Service → Repository → Database
         ↓            ↓           ↓
     Validation   Business    Data Access
                   Logic
```

**Responsibilities:**
- **Routes**: Define endpoints, attach middleware
- **Controllers**: Handle HTTP (req/res), validation
- **Services**: Business logic, orchestration
- **Repositories**: Database queries only

## BaseController Pattern

```ts
class BaseController {
  ok(res, data?) { return res.status(200).json(data); }
  created(res, data) { return res.status(201).json(data); }
  noContent(res) { return res.status(204).send(); }
  badRequest(res, message) { return res.status(400).json({ error: message }); }
  notFound(res, message) { return res.status(404).json({ error: message }); }
}
```

## Error Handling

```ts
class AppError extends Error {
  constructor(message: string, public statusCode: number = 500) {
    super(message);
  }
}

// Throw in service
throw new AppError('User not found', 404);

// Global handler
app.use((err, req, res, next) => {
  Sentry.captureException(err);
  res.status(err.statusCode || 500).json({
    error: err.message || 'Internal server error',
  });
});
```

## Validation with Zod

```ts
import { z } from 'zod';

const createUserSchema = z.object({
  email: z.string().email(),
  name: z.string().min(2),
  age: z.number().min(18).optional(),
});

// In controller
const data = createUserSchema.parse(req.body); // Throws if invalid
```

## Dependency Injection

```ts
// Container
const userRepo = new UserRepository();
const userService = new UserService(userRepo);
const userController = new UserController(userService);

// Register routes
router.get('/users/:id', asyncHandler((req, res) =>
  userController.getUser(req, res)
));
```

## Async Handler Wrapper

```ts
const asyncHandler = (fn) => (req, res, next) => {
  Promise.resolve(fn(req, res, next)).catch(next);
};

router.get('/users', asyncHandler(async (req, res) => {
  const users = await userService.getAll();
  res.json(users);
}));
```

## Sentry Integration

```ts
import * as Sentry from '@sentry/node';

Sentry.init({ dsn: process.env.SENTRY_DSN });

// Auto-capture errors
app.use(Sentry.Handlers.requestHandler());
app.use(Sentry.Handlers.errorHandler());

// Manual capture
try {
  await riskyOperation();
} catch (error) {
  Sentry.captureException(error);
  throw error;
}
```

## Middleware Pattern

```ts
// Auth middleware
const authMiddleware = async (req, res, next) => {
  const token = req.headers.authorization?.split(' ')[1];
  if (!token) return res.status(401).json({ error: 'Unauthorized' });
  
  try {
    req.user = await verifyToken(token);
    next();
  } catch {
    res.status(401).json({ error: 'Invalid token' });
  }
};

router.use('/protected', authMiddleware);
```

## TypeScript Tips

```ts
// DTOs
interface CreateUserDto {
  email: string;
  name: string;
  age?: number;
}

// Service return types
async getById(id: string): Promise<User | null> {}

// Strict null checks
const user = await repo.findById(id);
if (!user) throw new AppError('Not found', 404);
return user; // TS knows user is not null here
```

## File Organization

```
src/
├── routes/
│   └── user.routes.ts
├── controllers/
│   └── UserController.ts
├── services/
│   └── UserService.ts
├── repositories/
│   └── UserRepository.ts
├── middleware/
│   └── auth.middleware.ts
├── types/
│   └── user.types.ts
└── utils/
    ├── AppError.ts
    └── asyncHandler.ts
```

---

---

## References

**Quick Patterns:** `read .claude/skills/fullstack/backend-dev-guidelines/references/quick-patterns.md`
**Prisma Patterns:** `read .claude/skills/fullstack/backend-dev-guidelines/references/prisma-patterns.md`
**Common Patterns:** `read .claude/skills/fullstack/backend-dev-guidelines/references/common-patterns.md`
