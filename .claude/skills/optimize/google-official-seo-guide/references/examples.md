# Google SEO Examples

## Example 1: Basic VideoObject Structured Data

```json
{
  "@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "Video title",
  "description": "Video description",
  "thumbnailUrl": [
    "https://example.com/photos/1x1/photo.jpg",
    "https://example.com/photos/4x3/photo.jpg",
    "https://example.com/photos/16x9/photo.jpg"
  ],
  "uploadDate": "2024-03-31T08:00:00+08:00",
  "duration": "PT1M54S",
  "contentUrl": "https://example.com/video.mp4",
  "embedUrl": "https://example.com/embed/123"
}
```

**Use this for**: Adding basic video metadata to help Google understand and display your videos in search results.

---

## Example 2: LIVE Badge with BroadcastEvent

```json
{
  "@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "Livestream title",
  "uploadDate": "2024-10-27T14:00:00+00:00",
  "publication": {
    "@type": "BroadcastEvent",
    "isLiveBroadcast": true,
    "startDate": "2024-10-27T14:00:00+00:00",
    "endDate": "2024-10-27T14:37:14+00:00"
  }
}
```

**Use this for**: Enabling the LIVE badge on livestream videos in Google Search results.

---

## Example 3: Video Key Moments with Clip

```json
{
  "@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "Cat video",
  "hasPart": [
    {
      "@type": "Clip",
      "name": "Cat jumps",
      "startOffset": 30,
      "endOffset": 45,
      "url": "https://example.com/video?t=30"
    },
    {
      "@type": "Clip",
      "name": "Cat misses the fence",
      "startOffset": 111,
      "endOffset": 150,
      "url": "https://example.com/video?t=111"
    }
  ]
}
```

**Use this for**: Manually specifying important timestamps/chapters in your video for the key moments feature.

---

## Example 4: Good Anchor Text Practices

```html
<!-- Bad: Too generic -->
<a href="https://example.com">Click here</a> to learn more.

<!-- Better: Descriptive and contextual -->
For a full list of cheese available for purchase, see the
<a href="https://example.com">list of cheese types</a>.

<!-- Bad: Too many adjacent links -->
I've written about cheese
<a href="/page1">so</a>
<a href="/page2">many</a>
<a href="/page3">times</a>.

<!-- Better: Spaced out with context -->
I've written about cheese so many times this year:
the <a href="/blue-cheese">controversy over blue cheese</a>,
the <a href="/oldest-brie">world's oldest brie</a>, and
<a href="/boy-and-cheese">A Boy and His Cheese</a>.
```

**Use this for**: Creating effective internal and external links that help both users and Google understand your content.

---

## Example 5: Crawlable Links

```html
<!-- Recommended: Google can crawl these -->
<a href="https://example.com">Link text</a>
<a href="/products/category/shoes">Link text</a>
<a href="./products/category/shoes">Link text</a>

<!-- Not recommended: May not be crawled -->
<a routerLink="products/category">Link text</a>
<a onclick="goto('https://example.com')">Link text</a>
<span href="https://example.com">Link text</span>
```

**Use this for**: Ensuring your links are discoverable and crawlable by Googlebot.

---

## Example 6: Mobile and Desktop hreflang for Separate URLs

```html
<!-- Mobile version (https://m.example.com/) -->
<link rel="canonical" href="https://example.com/">
<link rel="alternate" hreflang="es" href="https://m.example.com/es/">
<link rel="alternate" hreflang="fr" href="https://m.example.com/fr/">

<!-- Desktop version (https://example.com/) -->
<link rel="canonical" href="https://example.com/">
<link rel="alternate" media="only screen and (max-width: 640px)"
      href="https://m.example.com/">
<link rel="alternate" hreflang="es" href="https://example.com/es/">
<link rel="alternate" hreflang="fr" href="https://example.com/fr/">
```

**Use this for**: Properly configuring separate mobile URLs (m-dot sites) with internationalization support.

---

## Example 7: robots meta tags

```html
<!-- Don't index this page -->
<meta name="robots" content="noindex">

<!-- Don't follow links on this page -->
<meta name="robots" content="nofollow">

<!-- Don't index and don't follow -->
<meta name="robots" content="noindex, nofollow">

<!-- Don't show snippet in search results -->
<meta name="robots" content="nosnippet">
```

**Use this for**: Controlling how Google crawls and indexes specific pages.

---

## Example 8: InteractionStatistic for Video Views

```json
{
  "@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "Video title",
  "interactionStatistic": {
    "@type": "InteractionCounter",
    "interactionType": { "@type": "WatchAction" },
    "userInteractionCount": 12345
  }
}
```

**Use this for**: Displaying the number of views/watches for your video content.

---

## Example 9: External Links with Attribution

```html
<!-- Citing sources with proper attribution -->
<p>
According to a recent study from Swiss researchers,
Emmental cheese wheels exposed to music had a milder flavor,
with the full findings available in
<a href="https://example.com/cheese-study">
  Cheese in Surround Sound—a culinary art experiment
</a>.
</p>

<!-- Use nofollow when you don't trust the source -->
<a href="https://untrusted-site.com" rel="nofollow">
  Untrusted content
</a>

<!-- Sponsored links must be marked -->
<a href="https://partner-site.com" rel="sponsored">
  Partner content
</a>
```

**Use this for**: Properly linking to external sources while maintaining SEO best practices.

---

## Example 10: Mobile-First Indexing Checklist

```html
<!-- Ensure same robots meta tags on mobile and desktop -->
<meta name="robots" content="index, follow">

<!-- Use same structured data on both versions -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Example Corp"
}
</script>

<!-- Ensure images have proper alt text on mobile -->
<img src="product.jpg" alt="Blue ceramic vase, 12 inches tall">

<!-- Use same title and meta description -->
<title>Product Name - Category | Site Name</title>
<meta name="description" content="High-quality product description">
```

**Use this for**: Ensuring your mobile site is properly optimized for Google's mobile-first indexing.
