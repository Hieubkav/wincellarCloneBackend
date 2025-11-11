---
name: frontend-dev-guidelines
description: React/TypeScript development guidelines. Suspense, lazy loading, useSuspenseQuery, features directory, MUI v7, TanStack Router, performance optimization. USE WHEN creating components, pages, features, data fetching, styling, routing, or frontend work.
---
## When to Use

Creating components, pages, features, data fetching, styling, routing, TypeScript work.

## Quick Checklist

**Component:**
- `React.FC<Props>` with TypeScript
- Lazy load if heavy: `React.lazy(()=> import())`
- Wrap in `<SuspenseLoader>` for loading
- `useSuspenseQuery` for data
- Import aliases: `@/`, `~types`, `~components`
- `useCallback` for handlers passed to children
- Default export at bottom

**Feature:**
- Directory: `features/{name}/`
- Subdirs: `api/`, `components/`, `hooks/`, `types/`
- API service: `api/{feature}Api.ts`
- Route: `routes/{name}/index.tsx`
- Export public API from `index.ts`

## Import Aliases

| Alias | Resolves To |
|-------|-------------|
| `@/` | `src/` |
| `~components` | `src/components/` |
| `~features` | `src/features/` |
| `~types` | `src/types/` |
| `~utils` | `src/utils/` |

## Data Fetching Pattern

```tsx
import { useSuspenseQuery } from '@tanstack/react-query';

const Component: React.FC = () => {
  const { data } = useSuspenseQuery({
    queryKey: ['key'],
    queryFn: fetchData,
  });
  return <div>{data.map(...)}</div>;
};

// In parent
<SuspenseLoader>
  <Component />
</SuspenseLoader>
```

## Component Structure

```tsx
// types
interface Props { id: string; onUpdate: () => void; }

// component
const MyComponent: React.FC<Props> = ({ id, onUpdate }) => {
  const { data } = useSuspenseQuery({...});
  
  const handleClick = useCallback(() => {
    onUpdate();
  }, [onUpdate]);
  
  return <Box>{data.name}</Box>;
};

export default MyComponent;
```

## MUI Styling

```tsx
import { Box, Typography } from '@mui/material';

// Inline (< 100 lines)
<Box sx={{ display: 'flex', gap: 2 }}>
  <Typography variant="h6">Title</Typography>
</Box>

// Separate file (> 100 lines)
import { styles } from './MyComponent.styles';
<Box sx={styles.container}>...</Box>
```

## Lazy Loading

```tsx
const HeavyComponent = React.lazy(() => import('./Heavy'));

<SuspenseLoader fallback={<Skeleton />}>
  <HeavyComponent />
</SuspenseLoader>
```

## Error Handling

```tsx
import { useMuiSnackbar } from '~utils/useMuiSnackbar';

const { showSuccess, showError } = useMuiSnackbar();

try {
  await mutation.mutateAsync(data);
  showSuccess('Saved!');
} catch (error) {
  showError('Failed to save');
}
```

## TanStack Router

```tsx
// routes/product/$id.tsx
export const Route = createFileRoute('/product/$id')({
  loader: ({ params }) => queryClient.ensureQueryData(productQuery(params.id)),
  component: ProductDetail,
});

const ProductDetail: React.FC = () => {
  const { id } = Route.useParams();
  const { data } = useSuspenseQuery(productQuery(id));
  return <div>{data.name}</div>;
};
```

## File Organization

```
src/
├── features/
│   └── product/
│       ├── api/productApi.ts
│       ├── components/ProductCard.tsx
│       ├── hooks/useProduct.ts
│       ├── types/product.types.ts
│       └── index.ts (public API)
├── components/ (shared)
├── routes/ (pages)
├── types/ (global)
└── utils/ (helpers)
```

## TypeScript Tips

```tsx
// Props with children
interface Props { children: React.ReactNode; }

// Event handlers
onChange: (value: string) => void;

// Optional props
name?: string;

// Strict typing
type Status = 'active' | 'inactive';
```

## Performance

- Lazy load routes: `React.lazy()`
- Memoize callbacks: `useCallback()`
- Memoize values: `useMemo()`
- Debounce search: `useDebounce()`
- Virtual lists: `react-window`

---

---

## References

**Common Patterns:** `read .claude/skills/fullstack/frontend-dev-guidelines/references/common-patterns.md`
