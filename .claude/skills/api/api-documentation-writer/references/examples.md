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
