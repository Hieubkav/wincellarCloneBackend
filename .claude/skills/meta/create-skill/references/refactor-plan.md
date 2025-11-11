# Skills Refactoring Plan - Theo Chuẩn Mới

## 🎯 Mục Tiêu
- SKILL.md < 200 lines (hard limit)
- Split content vào references/ nếu cần
- Đảm bảo progressive disclosure
- Maintain quality và usability

## 📊 Phân Loại Skills

### ✅ ĐẠT CHUẨN (< 210 lines) - Keep as is
1. **docs-seeker** - 204 lines ✅
2. **filament-form-debugger** - 209 lines ✅
3. **image-management** - 211 lines ✅

### 🔧 CẦN REFACTOR NHẸ (210-260 lines) - Minor split
4. **api-design-principles** - 226 lines → Split examples vào references/
5. **create-skill** - 237 lines → Already optimal
6. **database-backup** - 254 lines → Split workflow details

### 🔨 CẦN REFACTOR TRUNG BÌNH (260-320 lines) - Major split
7. **product-search-scoring** - 271 lines → Split algorithm vào references/
8. **systematic-debugging** - 295 lines → Split phases vào references/
9. **filament-resource-generator** - 298 lines → Split templates
10. **filament-rules** - 298 lines → Split patterns vào references/
11. **backend-dev-guidelines** - 302 lines → Split architecture guide
12. **api-documentation-writer** - 305 lines → Split templates
13. **ui-styling** - 321 lines → Split component library
14. **api-cache-invalidation** - 325 lines → Split implementation guide

### 🔥 CẦN REFACTOR NẶNG (> 350 lines) - Massive split
15. **frontend-dev-guidelines** - 399 lines → Split architecture + patterns
16. **ux-designer** - 446 lines → Split accessibility + responsive + references

## 📋 Chiến Lược Refactor

### Pattern A: Simple Split (cho 210-260 lines)
```
skill/
├── SKILL.md (~150-180 lines)      # Core workflow + quick ref
└── references/
    └── detailed-guide.md          # Deep dive content
```

### Pattern B: Moderate Split (cho 260-320 lines)
```
skill/
├── SKILL.md (~180-200 lines)      # Essential only
└── references/
    ├── architecture.md            # Design patterns
    ├── examples.md                # Code examples
    └── troubleshooting.md         # Common issues
```

### Pattern C: Heavy Split (cho > 350 lines)
```
skill/
├── SKILL.md (~150-180 lines)      # Absolute minimum
├── references/
│   ├── core-concepts.md
│   ├── patterns.md
│   ├── best-practices.md
│   └── advanced-usage.md
└── scripts/ (nếu cần)
    └── helper-scripts.py
```

## 🎯 Priority Order (Refactor theo thứ tự)

### Phase 1: Critical Skills (Dùng nhiều nhất)
1. **filament-rules** (298) - Dùng mỗi ngày
2. **filament-resource-generator** (298) - Dùng mỗi ngày
3. **systematic-debugging** (295) - Critical workflow

### Phase 2: Development Guidelines
4. **frontend-dev-guidelines** (399) - Longest, high impact
5. **backend-dev-guidelines** (302) - Important patterns
6. **ux-designer** (446) - Longest overall

### Phase 3: API & Infrastructure
7. **api-cache-invalidation** (325) - Complex system
8. **api-design-principles** (226) - Can be concise
9. **api-documentation-writer** (305) - Template-heavy

### Phase 4: Specialized
10. **ui-styling** (321) - Component library
11. **product-search-scoring** (271) - Algorithm details
12. **database-backup** (254) - Simple workflow

## ✅ Success Criteria

Sau refactor, mỗi skill phải:
- [ ] SKILL.md < 200 lines
- [ ] Clear references to detailed docs
- [ ] Progressive disclosure maintained
- [ ] Examples still accessible
- [ ] No loss of critical information
- [ ] Validation passes (quick_validate.py)
- [ ] Natural language triggers work
