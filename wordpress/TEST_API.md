# Testing WordPress REST API

## Quick Test URLs

Replace `your-site` with your WordPress site name:

### 1. Get All Services and Transactions
```
http://localhost/your-site/wp-json/hedera-fees/v1/transactions
```

**Expected Response:**
```json
{
  "Crypto": ["CryptoCreate", "CryptoUpdate", ...],
  "Token": ["TokenCreate", "TokenMint", ...],
  ...
}
```

### 2. Get Transaction Parameters
```
http://localhost/your-site/wp-json/hedera-fees/v1/transactions/CryptoCreate/parameters
```

**Expected Response:**
```json
[
  {
    "name": "numKeys",
    "type": "number",
    "defaultValue": 1,
    "min": 1,
    "max": 100,
    "prompt": "Number of keys"
  },
  ...
]
```

### 3. Get Transaction Description
```
http://localhost/your-site/wp-json/hedera-fees/v1/transactions/CryptoCreate/description
```

**Expected Response:**
```json
"Create a new Account"
```

### 4. Calculate Fee (POST)

**URL:**
```
http://localhost/your-site/wp-json/hedera-fees/v1/transactions/CryptoCreate/fee
```

**Method:** POST  
**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "numKeys": 1,
  "numHooksCreated": 0,
  "numSignatures": 2
}
```

**Expected Response:**
```json
{
  "fee": 0.05000,
  "details": {
    "Base fee": {
      "value": 1,
      "fee": 0.05000
    },
    "Additional signature verifications": {
      "value": 1,
      "fee": 0.00010
    }
  }
}
```

## Using cURL

```bash
# Get all transactions
curl http://localhost/your-site/wp-json/hedera-fees/v1/transactions

# Get parameters
curl http://localhost/your-site/wp-json/hedera-fees/v1/transactions/CryptoCreate/parameters

# Calculate fee
curl -X POST http://localhost/your-site/wp-json/hedera-fees/v1/transactions/CryptoCreate/fee \
  -H "Content-Type: application/json" \
  -d "{\"numKeys\":1,\"numHooksCreated\":0,\"numSignatures\":2}"
```

## Using Postman

1. Create new request
2. Set method to GET or POST
3. Enter URL: `http://localhost/your-site/wp-json/hedera-fees/v1/transactions/CryptoCreate/fee`
4. For POST: Add header `Content-Type: application/json`
5. For POST: Add JSON body in Body tab
6. Send request

## Using Browser

GET requests work directly in browser:
- Just paste the URL in address bar
- JSON will be displayed

For POST requests, use browser console:
```javascript
fetch('http://localhost/your-site/wp-json/hedera-fees/v1/transactions/CryptoCreate/fee', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    numKeys: 1,
    numHooksCreated: 0,
    numSignatures: 2
  })
})
  .then(res => res.json())
  .then(data => console.log(data));
```

## Common Issues

**404 Not Found:**
- Go to WordPress Admin → Settings → Permalinks
- Click "Save Changes" to flush rewrite rules

**500 Internal Server Error:**
- Check WordPress debug log
- Verify JSON files exist in `data/` folder
- Check file permissions

**CORS Errors:**
- WordPress REST API handles CORS automatically
- If calling from external domain, may need additional configuration

