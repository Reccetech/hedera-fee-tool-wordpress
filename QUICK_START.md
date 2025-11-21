# Quick Start Guide

## WordPress Plugin Installation

1. Copy the entire `fee-calculator-test1` folder to `wp-content/plugins/hedera-fee-calculator/`
2. Activate the plugin in WordPress admin
3. REST API available at: `/wp-json/hedera-fees/v1/`

## Standalone Test UI

### Prerequisites
- PHP 7.4+ (with built-in server)
- Node.js 16+ and npm

### Setup Steps

1. **Install React dependencies:**
   ```bash
   cd C:\repos\fee-calculator-test1\test-ui\react-ui
   npm install
   ```

2. **Create .env file** (if not exists):
   ```bash
   # Copy .env.example to .env
   copy .env.example .env
   ```
   
   Or create `.env` with:
   ```
   VITE_BACKEND_URL=http://localhost:8000/api/v1/transactions
   ```

3. **Start the servers:**

   **Terminal 1 - PHP API Server:**
   ```bash
   cd C:\repos\fee-calculator-test1\test-ui
   php -S localhost:8000
   ```

   **Terminal 2 - React Dev Server:**
   ```bash
   cd C:\repos\fee-calculator-test1\test-ui\react-ui
   npm run dev
   ```

4. **Open in browser:**
   - React UI: http://localhost:3000
   - PHP API: http://localhost:8000/api/v1/transactions

## Testing

1. Select a service (e.g., "Crypto")
2. Select a transaction (e.g., "CryptoCreate")
3. Enter parameters
4. Fee is calculated automatically

## Updating Fees

Simply edit the JSON files in `data/`:
- `simpleFeesSchedules.json` - Fee amounts
- `transactionMetadata.json` - Parameters and descriptions

Changes are detected automatically on next request.

## Troubleshooting

- **CORS errors**: Ensure PHP server is running on port 8000
- **API 404**: Check that `api.php` exists in `test-ui/`
- **React won't start**: Run `npm install` first
- **Build errors**: Check Node.js version (16+ required)

