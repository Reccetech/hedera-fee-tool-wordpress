# Testing the Standalone UI

## Step-by-Step Instructions

### Step 1: Install React Dependencies

Open a terminal and run:
```bash
cd C:\repos\fee-calculator-test1\test-ui\react-ui
npm install
```

This will install all required React dependencies (takes 1-2 minutes).

### Step 2: Start the PHP API Server

Open a **new terminal** (keep the first one open) and run:
```bash
cd C:\repos\fee-calculator-test1\test-ui
php -S localhost:8000
```

You should see:
```
PHP 7.x.x Development Server started at ...
Listening on http://localhost:8000
```

**Keep this terminal open** - the server must stay running.

### Step 3: Start the React Development Server

In the **first terminal** (where you ran `npm install`), run:
```bash
npm run dev
```

You should see:
```
  VITE v6.x.x  ready in xxx ms

  ➜  Local:   http://localhost:3000/
  ➜  Network: use --host to expose
```

### Step 4: Open in Browser

Open your web browser and go to:
```
http://localhost:3000
```

You should see the Hedera Fee Calculator UI!

## Testing the Calculator

1. **Select a Service**: Click on a service like "CRYPTO SERVICE"
2. **Select a Transaction**: Click on a transaction like "CryptoCreate"
3. **Enter Parameters**: Fill in the parameter fields
4. **View Results**: The fee is calculated automatically and displayed at the bottom

## Testing the API Directly

You can also test the API endpoints directly:

- **Get all services**: http://localhost:8000/api/v1/transactions
- **Get parameters for CryptoCreate**: http://localhost:8000/api/v1/transactions/CryptoCreate/parameters
- **Get description**: http://localhost:8000/api/v1/transactions/CryptoCreate/description

## Troubleshooting

### "npm: command not found"
- Install Node.js from https://nodejs.org/ (version 16 or higher)

### "php: command not found"
- PHP should be in your PATH, or use full path: `C:\php\php.exe -S localhost:8000`
- Or install PHP if not available

### "Port 8000 already in use"
- Change the port in the PHP command: `php -S localhost:8001`
- Update `.env` file: `VITE_BACKEND_URL=http://localhost:8001/api/v1/transactions`

### "Port 3000 already in use"
- Vite will automatically use the next available port (3001, 3002, etc.)
- Check the terminal output for the actual port

### CORS Errors
- Make sure the PHP server is running on port 8000
- Check that `.env` file has the correct URL

### API Returns 404
- Verify `api.php` exists in `test-ui/` directory
- Check the URL path matches exactly: `/api/v1/transactions`

## Quick Test Commands

**Test API is working:**
```bash
curl http://localhost:8000/api/v1/transactions
```

**Test specific transaction:**
```bash
curl http://localhost:8000/api/v1/transactions/CryptoCreate/parameters
```

## Stopping the Servers

- **PHP Server**: Press `Ctrl+C` in the terminal running PHP
- **React Server**: Press `Ctrl+C` in the terminal running `npm run dev`

