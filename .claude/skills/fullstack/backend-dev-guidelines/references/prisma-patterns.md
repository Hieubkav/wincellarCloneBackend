## Prisma Patterns

```ts
// Find with relations
await prisma.user.findUnique({
  where: { id },
  include: { posts: true },
});

// Create with nested
await prisma.user.create({
  data: {
    email,
    posts: { create: [{ title: 'First post' }] },
  },
});

// Transaction
await prisma.$transaction([
  prisma.user.create({ data: userData }),
  prisma.log.create({ data: logData }),
]);
```
