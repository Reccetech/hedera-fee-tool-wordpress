# WordPress Plugin Installation Guide

## Installation Steps

### Step 1: Copy Plugin to WordPress

1. **Locate your WordPress installation:**
   - Usually in: `C:\xampp\htdocs\your-site\wp-content\plugins\`
   - Or: `C:\wamp\www\your-site\wp-content\plugins\`
   - Or your custom WordPress installation path

2. **Copy the plugin folder:**
   - Copy the entire `fee-calculator-test1` folder
   - Rename it to `hedera-fee-calculator` (optional, but recommended)
   - Place it in: `wp-content/plugins/hedera-fee-calculator/`

   **Final structure should be:**
   ```
   wp-content/plugins/hedera-fee-calculator/
   ├── core/
   ├── wordpress/
   ├── data/
   ├── includes/
   ├── test-ui/
   └── hedera-fee-calculator.php
   ```

### Step 2: Activate the Plugin

1. **Log into WordPress Admin:**
   - Go to: `http://localhost/your-site/wp-admin`
   - Or your WordPress admin URL

2. **Navigate to Plugins:**
   - Click "Plugins" in the left sidebar
   - Find "Hedera Fee Calculator"
   - Click "Activate"

3. **Verify Activation:**
   - You should see "Plugin activated successfully"
   - The plugin should appear in your plugins list

### Step 3: Test the REST API

The plugin automatically registers REST API endpoints at:
```
/wp-json/hedera-fees/v1/
```

**Test endpoints:**

1. **Get all services and transactions:**
   ```
   http://localhost/your-site/wp-json/hedera-fees/v1/transactions
   ```

2. **Get transaction parameters:**
   ```
   http://localhost/your-site/wp-json/hedera-fees/v1/transactions/CryptoCreate/parameters
   ```

3. **Get transaction description:**
   ```
   http://localhost/your-site/wp-json/hedera-fees/v1/transactions/CryptoCreate/description
   ```

4. **Calculate fee (POST request):**
   ```
   POST http://localhost/your-site/wp-json/hedera-fees/v1/transactions/CryptoCreate/fee
   Body (JSON):
   {
     "numKeys": 1,
     "numHooksCreated": 0,
     "numSignatures": 2
   }
   ```

## Using with Frontend

### Option 1: Use WordPress REST API

Update your frontend `.env` file:
```
VITE_BACKEND_URL=http://localhost/your-site/wp-json/hedera-fees/v1/transactions
```

### Option 2: Create a WordPress Page/Shortcode

You can create a custom WordPress page that uses the REST API, or create a shortcode.

## Updating Fees

### Method 1: File System

1. Edit JSON files in:
   ```
   wp-content/plugins/hedera-fee-calculator/data/
   ```
   - `simpleFeesSchedules.json`
   - `transactionMetadata.json`

2. Changes are detected automatically on next request

### Method 2: WordPress Admin (Future)

Admin UI for editing JSON files (coming soon)

## File Permissions

Make sure WordPress can read the JSON files:
- Files should be readable (644 permissions)
- WordPress user should have read access

## Troubleshooting

### Plugin doesn't appear in WordPress
- Check that `hedera-fee-calculator.php` is in the plugin root
- Verify PHP version (7.4+ required)
- Check WordPress error logs

### REST API returns 404
- Go to WordPress Admin → Settings → Permalinks
- Click "Save Changes" to flush rewrite rules
- This registers the REST API routes

### JSON files not loading
- Check file permissions
- Verify files exist in `data/` directory
- Check WordPress debug log for errors

### CORS errors (if calling from external site)
- WordPress REST API handles CORS automatically
- If issues persist, check `.htaccess` or server configuration

## Testing the Plugin

### Using Browser

1. **Test GET endpoint:**
   ```
   http://localhost/your-site/wp-json/hedera-fees/v1/transactions
   ```
   Should return JSON with all services and transactions

2. **Test parameters:**
   ```
   http://localhost/your-site/wp-json/hedera-fees/v1/transactions/CryptoCreate/parameters
   ```
   Should return parameter definitions

### Using cURL

```bash
# Get all transactions
curl http://localhost/your-site/wp-json/hedera-fees/v1/transactions

# Calculate fee
curl -X POST http://localhost/your-site/wp-json/hedera-fees/v1/transactions/CryptoCreate/fee \
  -H "Content-Type: application/json" \
  -d '{"numKeys":1,"numHooksCreated":0,"numSignatures":2}'
```

### Using JavaScript/Fetch

```javascript
// Get all transactions
fetch('http://localhost/your-site/wp-json/hedera-fees/v1/transactions')
  .then(res => res.json())
  .then(data => console.log(data));

// Calculate fee
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

## Plugin Structure in WordPress

```
wp-content/plugins/hedera-fee-calculator/
├── hedera-fee-calculator.php    # Main plugin file
├── core/                         # Core classes (WordPress-independent)
├── wordpress/
│   └── REST_API/
│       └── REST_Controller.php  # WordPress REST API integration
├── includes/
│   ├── Calculators/             # Fee calculation logic
│   └── Validators/              # Parameter validation
└── data/                         # JSON data files (updatable)
    ├── simpleFeesSchedules.json
    └── transactionMetadata.json
```

## Security Notes

- REST API endpoints are publicly accessible by default
- For production, consider adding authentication if needed
- JSON files are read-only from WordPress perspective
- No database writes - all data comes from JSON files

## Next Steps

1. ✅ Install and activate plugin
2. ✅ Test REST API endpoints
3. ✅ Integrate with your frontend
4. ✅ Update fees via JSON files as needed

