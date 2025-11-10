---
name: api-documentation-writer
description: Generate comprehensive API documentation for REST, GraphQL, WebSocket APIs with OpenAPI specs, endpoint descriptions, request/response examples, error codes, authentication guides, and SDKs. USE WHEN user says 'viết document API', 'tạo API docs', 'generate API documentation', 'document REST endpoints', hoặc cần tạo technical reference cho developers.
---

# API Documentation Writer

Tạo tài liệu API comprehensive, developer-friendly cho các hệ thống API.

## When to Activate This Skill

- User nói "viết document API"
- User muốn "tạo API documentation"
- Cần generate "OpenAPI spec"
- Cần document "GraphQL schema"
- Cần tạo "developer guide" cho API
- Cần document "webhook" hoặc "authentication"

## Core Workflow

### Phase 1: Gather API Information

Hỏi user những câu hỏi sau để hiểu rõ API:
- **API type**: REST, GraphQL, WebSocket, gRPC?
- **Authentication**: API key, OAuth, JWT, Bearer token?
- **Base URL**: Production URL, versioning strategy?
- **Endpoints**: Danh sách endpoints, mục đích của từng cái?
- **Request/Response format**: JSON, XML, custom format?
- **Rate limiting**: Có giới hạn không, bao nhiêu requests/giờ?
- **Webhooks**: Có support webhooks không?

### Phase 2: Generate Complete Documentation Structure

**Overview Section**:
- Mô tả API (1-2 câu)
- Key capabilities
- Quick start checklist
- Support & resources

**Authentication**:
- Cách lấy credentials
- Nơi đặt auth tokens
- Example authenticated request
- Token refresh process (nếu có)

**Base URL & Versioning**:
- Production + sandbox URLs
- Version format (path, header, query param)
- Current version + changelog

**Endpoints** (mỗi endpoint):
- HTTP method + path
- Mô tả chi tiết
- Path parameters
- Query parameters
- Request headers
- Request body schema
- Response codes + meanings
- Response body schema
- Example requests (curl, JavaScript, Python)
- Example responses (formatted JSON)

**Error Handling**:
- Standard error response format
- Common error codes + meanings
- Troubleshooting guide

**Rate Limiting**:
- Limits + windows
- Headers to check
- How to handle rate limits

**SDKs & Libraries**:
- Official client libraries
- Community libraries
- Installation instructions

**Webhooks** (nếu có):
- Available webhook events
- Setup process
- Payload examples
- Security verification

### Phase 3: Format Output

**REST API Template**:
```markdown
# [API Name] Documentation

## Overview

[Brief description]

**Base URL**: `https://api.example.com/v1`

**Authentication**: API Key via Authorization header

## Quick Start

1. [Step 1]
2. [Step 2]
3. [Step 3]

## Authentication

All requests require API key:

\`\`\`
Authorization: Bearer YOUR_API_KEY
\`\`\`

## Endpoints

### GET /resource

Retrieve list of resources.

**Parameters**:
- `limit` (optional, integer): Number of results
- `offset` (optional, integer): Pagination offset

**Request Example**:
\`\`\`bash
curl -X GET "https://api.example.com/v1/resource?limit=10" \
  -H "Authorization: Bearer YOUR_API_KEY"
\`\`\`

**Response** (200 OK):
\`\`\`json
{
  "data": [...],
  "total": 100,
  "limit": 10
}
\`\`\`

## Error Handling

Standard error format:
\`\`\`json
{
  "error": {
    "code": "invalid_request",
    "message": "The 'name' field is required"
  }
}
\`\`\`

## Support

- Documentation: [url]
- Support: [email]
```

**GraphQL Template** (adjust để show):
- Schema definitions
- Query examples
- Mutation examples
- Subscription examples
- Variables + directives

### Phase 4: Documentation Best Practices

- ✅ Start với working example (copy-paste ready)
- ✅ Show cả request và response
- ✅ Use realistic example data
- ✅ Include error cases
- ✅ Explain mỗi parameter
- ✅ Provide code examples (JavaScript, Python, PHP)
- ✅ Use consistent formatting
- ✅ Add interactive examples (nếu possible)
- ✅ Link related endpoints
- ✅ Include changelog + versioning

### Phase 5: Developer Experience Tips

- **Quick Start**: Working example trong 60 giây
- **Postman Collection**: Include OpenAPI/Swagger spec
- **Common Use Cases**: Show realistic workflows
- **Troubleshooting**: FAQ section
- **Testing Environment**: Sandbox/test URLs
- **SDKs**: Installation instructions
- **Rate Limiting**: Details upfront
- **Pagination**: Show patterns
- **Filtering & Sorting**: Explain options

## Available Tools / Commands

- **REST APIs**: Use OpenAPI/Swagger format
- **GraphQL**: Use GraphQL Schema Documentation standard
- **Webhooks**: Include setup guide + payload examples
- **Authentication**: Clear examples cho mỗi method
- **Code Examples**: Cung cấp JavaScript, Python, cURL

## Documentation Quality Checklist

- [ ] Starts with working example
- [ ] Explains every parameter
- [ ] Shows realistic request/response examples
- [ ] Includes error handling
- [ ] Provides code samples (3+ languages)
- [ ] Uses consistent formatting
- [ ] Organized logically (common operations first)
- [ ] Authentication documented clearly
- [ ] Covers edge cases + limitations
- [ ] Follows REST/GraphQL best practices
- [ ] Scannable with good headers
- [ ] Interactive examples included (nếu possible)

## Common Patterns

### Pattern 1: REST API with Pagination

Document pagination approach:
- Offset-based
- Cursor-based
- Page-based

Show filtering + sorting options.

### Pattern 2: GraphQL Schema

Document schema với:
- Type definitions
- Resolver examples
- Error handling
- Real-world queries

### Pattern 3: Webhook Documentation

Include:
- Available events
- Payload examples
- Signature verification
- Retry logic
- Testing environment

## Examples

### Example 1: Basic REST API Doc

```bash
# GET request với authentication
curl -X GET "https://api.wincellar.com/v1/products" \
  -H "Authorization: Bearer YOUR_API_KEY"

# Response
{
  "data": [
    {
      "id": "123",
      "name": "Chateau Margaux",
      "price": 199.99
    }
  ],
  "total": 50
}
```

### Example 2: GraphQL Query

```graphql
query GetProducts {
  products(limit: 10) {
    id
    name
    price
    vintage
  }
}
```

### Example 3: WebSocket Connection

```javascript
const ws = new WebSocket('wss://api.wincellar.com/ws');

ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  console.log('Real-time update:', data);
};
```

## Key Principles

1. **Developer First**: Giả định developers muốn copy-paste examples
2. **Show Before Tell**: Ví dụ trước, sau đó mới giải thích
3. **Consistent Format**: Mỗi endpoint follow cùng template
4. **Error Focus**: Include error codes + solutions
5. **Real Data**: Use realistic examples, không generic placeholders
6. **Scannable**: Headers + short descriptions cho quick lookup
7. **Complete**: Cover happy path + edge cases
8. **Multi-Language**: Code examples trong JavaScript, Python, cURL

## Supplementary Resources

For comprehensive API design principles: `read .claude/skills/api-design-principles/SKILL.md`

For cache invalidation strategies: `read .claude/skills/api-cache-invalidation/SKILL.md`

## Related Skills

- **api-design-principles**: Sử dụng cho API design + architecture
- **api-cache-invalidation**: Sync data giữa frontend-backend
- **docs-seeker**: Tìm documentation reference từ internet
