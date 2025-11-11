---
name: web-performance-audit
description: Conduct comprehensive web performance audits. Measure page speed, identify bottlenecks, and recommend optimizations to improve user experience and SEO.
---

# Web Performance Audit

## Overview

Web performance audits measure load times, identify bottlenecks, and guide optimization efforts to create faster, better user experiences.

## When to Use

- Regular performance monitoring
- After major changes
- User complaints about slowness
- SEO optimization
- Mobile optimization
- Performance baseline setting

## Core Web Vitals (Google Standards)

### Largest Contentful Paint (LCP)
- **Measure**: Time to load largest visible element
- **Good**: <2.5 seconds | **Poor**: >4 seconds
- **Impacts**: User perception of speed

### First Input Delay (FID)
- **Measure**: Time from user input to response
- **Good**: <100ms | **Poor**: >300ms
- **Impacts**: Responsiveness

### Cumulative Layout Shift (CLS)
- **Measure**: Visual stability (unexpected layout shifts)
- **Good**: <0.1 | **Poor**: >0.25
- **Impacts**: User frustration

## Additional Key Metrics

- **First Contentful Paint (FCP)**: Target <1.8 seconds
- **Time to Interactive (TTI)**: Target <3.8 seconds
- **Total Blocking Time (TBT)**: Target <300ms
- **Interaction to Next Paint (INP)**: Target <200ms

## Measurement Tools

- Google PageSpeed Insights
- Lighthouse (Chrome DevTools)
- WebPageTest
- New Relic / Datadog
- GTmetrix

## Quick Start Workflow

### 1. Measure Baseline
Run Lighthouse audit on both desktop and mobile to establish current performance:
```bash
# Chrome DevTools → Lighthouse → Generate Report
# Or use PageSpeed Insights: https://pagespeed.web.dev/
```

### 2. Identify Opportunities
Focus on metrics that fail "Good" thresholds:
- LCP > 2.5s → Optimize largest elements, images, fonts
- FID > 100ms → Reduce JavaScript blocking time
- CLS > 0.1 → Reserve space for dynamic content, fix layout shifts

### 3. Prioritize Fixes
**Quick Wins (1-2 days)**:
- Enable gzip/brotli compression
- Minify CSS/JavaScript
- Compress images
- Defer non-critical JavaScript
- Preload critical fonts

**Medium Effort (1-2 weeks)**:
- Implement lazy loading
- Code splitting
- Service worker caching
- WebP images with srcset
- Critical CSS extraction

**Long-term (1-3 months)**:
- Framework optimization
- Database query optimization
- CDN implementation
- Architecture refactor

### 4. Implement & Monitor
- Make changes incrementally
- Measure impact after each optimization
- Setup continuous monitoring (Google Analytics Web Vitals, New Relic, etc.)
- Create performance budgets (JS <150KB, CSS <50KB, Images <500KB)

## Performance Audit Process

For detailed audit framework with Python code examples:
**Read**: `references/analysis-process.md`

Key steps:
1. Measure desktop + mobile metrics
2. Collect field data (real users) + lab data
3. Identify specific bottlenecks
4. Prioritize by impact
5. Generate actionable report with timeline

## Optimization Strategies

For complete roadmap with checklists:
**Read**: `references/optimization-strategies.md`

Categories:
- **Network**: Compression, HTTP/2, CDN, caching
- **JavaScript**: Code splitting, minification, deferring
- **CSS**: Critical CSS, unused CSS removal, minification
- **Images**: WebP, srcset, lazy loading, compression

## Monitoring & Continuous Improvement

For monitoring setup and best practices:
**Read**: `references/monitoring-setup.md`

Setup includes:
- Real-time monitoring tools
- Performance baselines and targets
- Alert configuration for regressions
- Performance budget enforcement

## Best Practices

### ✅ DO
- Measure regularly (not just once)
- Use field data (real users) + lab data
- Focus on Core Web Vitals first
- Set realistic targets (10-20% improvement)
- Prioritize by impact
- Monitor continuously
- Setup performance budgets
- Test on slow networks (3G)
- Include mobile in all testing
- Document improvements

### ❌ DON'T
- Ignore field data from real users
- Focus on only one metric
- Set impossible targets
- Optimize without measurement
- Forget about image optimization
- Ignore JavaScript costs
- Skip mobile performance testing
- Over-optimize prematurely
- Forget about monitoring after launch
- Expect improvements without effort

## Quick Tips

- Start with free Lighthouse audit in Chrome DevTools
- Use WebPageTest for detailed waterfall analysis
- Test on 3G mobile to find real bottlenecks
- Prioritize LCP optimization first (biggest user impact)
- Create performance budget and share with team
- Review performance weekly, not just at launch

## References

**Analysis Process**: `read references/analysis-process.md` - Audit framework with code examples  
**Optimization Strategies**: `read references/optimization-strategies.md` - Complete roadmap with checklists  
**Monitoring Setup**: `read references/monitoring-setup.md` - Continuous monitoring best practices
