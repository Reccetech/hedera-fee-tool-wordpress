# Quick WordPress Installation

## Fast Setup (3 Steps)

### 1. Copy Plugin Folder

Copy `fee-calculator-test1` to your WordPress plugins directory:

**Windows:**
```powershell
# Example paths (adjust to your WordPress installation)
Copy-Item -Path "C:\repos\fee-calculator-test1" -Destination "C:\xampp\htdocs\mysite\wp-content\plugins\hedera-fee-calculator" -Recurse
```

**Or manually:**
- Copy the entire `fee-calculator-test1` folder
- Paste into: `wp-content/plugins/`
- Rename to: `hedera-fee-calculator` (optional)

### 2. Activate in WordPress

1. Go to: `http://localhost/mysite/wp-admin`
2. Click **Plugins** → **Installed Plugins**
3. Find **Hedera Fee Calculator**
4. Click **Activate**

### 3. Test It

Open in browser:
```
http://localhost/mysite/wp-json/hedera-fees/v1/transactions
```

You should see JSON with all services and transactions!

## Common WordPress Paths

- **XAMPP**: `C:\xampp\htdocs\your-site\wp-content\plugins\`
- **WAMP**: `C:\wamp\www\your-site\wp-content\plugins\`
- **Local by Flywheel**: `C:\Users\YourName\Local Sites\your-site\app\public\wp-content\plugins\`
- **Custom**: `C:\path\to\wordpress\wp-content\plugins\`

## Verify Installation

After activation, test these URLs:

✅ **All transactions:**
```
http://localhost/mysite/wp-json/hedera-fees/v1/transactions
```

✅ **CryptoCreate parameters:**
```
http://localhost/mysite/wp-json/hedera-fees/v1/transactions/CryptoCreate/parameters
```

✅ **Calculate fee (use Postman or curl):**
```
POST http://localhost/mysite/wp-json/hedera-fees/v1/transactions/CryptoCreate/fee
Content-Type: application/json

{
  "numKeys": 1,
  "numHooksCreated": 0,
  "numSignatures": 2
}
```

## Troubleshooting

**404 Error?**
- Go to: WordPress Admin → Settings → Permalinks
- Click "Save Changes" (flushes rewrite rules)

**Plugin not showing?**
- Check PHP version (needs 7.4+)
- Check WordPress error log
- Verify `hedera-fee-calculator.php` exists in plugin root

**API not working?**
- Make sure plugin is activated
- Check file permissions on `data/` folder
- Enable WordPress debug mode to see errors

## Using with React UI

Update React UI `.env`:
```
VITE_BACKEND_URL=http://localhost/mysite/wp-json/hedera-fees/v1/transactions
```

Then start React dev server - it will use WordPress API instead of standalone PHP API.

