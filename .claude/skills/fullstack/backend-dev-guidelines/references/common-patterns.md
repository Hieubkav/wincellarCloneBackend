## Common Patterns

**Pagination:**
```ts
async findAll(page = 1, limit = 10) {
  const skip = (page - 1) * limit;
  return prisma.user.findMany({ skip, take: limit });
}
```

**Soft Delete:**
```ts
async softDelete(id: string) {
  return prisma.user.update({
    where: { id },
    data: { deletedAt: new Date() },
  });
}
```


**Complete guide:** `read .claude/skills/backend-dev-guidelines/SKILL.md` (original 303 lines with detailed patterns)
