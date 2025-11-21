# Test UI - Quick Start

## Easiest Way: Use the Batch Scripts

### Option 1: Start Everything at Once
Double-click: **`start-all.bat`**

This opens two windows:
- PHP API Server (port 8000)
- React Dev Server (port 3000)

Then open: **http://localhost:3000**

### Option 2: Start Separately

1. **Start API Server**: Double-click `start-api.bat`
2. **Start React UI**: Double-click `start-react.bat` (in a new window)
3. Open: **http://localhost:3000**

## Manual Start (Command Line)

### Terminal 1 - PHP API:
```bash
cd C:\repos\fee-calculator-test1\test-ui
php -S localhost:8000
```

### Terminal 2 - React UI:
```bash
cd C:\repos\fee-calculator-test1\test-ui\react-ui
npm install    # First time only
npm run dev
```

Then open: **http://localhost:3000**

## What You'll See

1. **Step 1**: Select a Hedera service (Crypto, Token, etc.)
2. **Step 2**: Select a transaction (CryptoCreate, TokenMint, etc.)
3. **Step 3**: Enter parameters
4. **Bottom**: Fee is calculated automatically

## Testing the API

Test the API directly:
- http://localhost:8000/api/v1/transactions
- http://localhost:8000/api/v1/transactions/CryptoCreate/parameters
- http://localhost:8000/api/v1/transactions/CryptoCreate/fee (POST with JSON body)

## Troubleshooting

See `TEST_INSTRUCTIONS.md` for detailed troubleshooting.

## Files

- `start-all.bat` - Start both servers
- `start-api.bat` - Start PHP API only
- `start-react.bat` - Start React UI only
- `api.php` - PHP REST API
- `react-ui/` - React frontend source

