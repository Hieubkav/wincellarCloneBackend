## Common Patterns

**List with actions:**
```tsx
const handleDelete = useCallback((id: string) => {
  deleteProduct(id);
}, [deleteProduct]);

{products.map(p => (
  <ProductCard key={p.id} product={p} onDelete={handleDelete} />
))}
```

**Form with validation:**
```tsx
import { useForm } from 'react-hook-form';

const { register, handleSubmit } = useForm();
const onSubmit = (data) => mutation.mutate(data);
```


**Complete guide:** `read .claude/skills/frontend-dev-guidelines/SKILL.md` (original 399 lines with detailed examples)
