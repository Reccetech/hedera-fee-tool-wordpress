# Starting the Test UI

## Option 1: React Development Server (Recommended for Development)

1. **Terminal 1 - Start PHP API Server:**
   ```bash
   cd C:\repos\fee-calculator-test1\test-ui
   php -S localhost:8000
   ```

2. **Terminal 2 - Start React Dev Server:**
   ```bash
   cd C:\repos\fee-calculator-test1\test-ui\react-ui
   npm install
   npm run dev
   ```

3. Open http://localhost:3000 in your browser

## Option 2: Build React and Serve with PHP

1. **Build React App:**
   ```bash
   cd C:\repos\fee-calculator-test1\test-ui\react-ui
   npm install
   npm run build
   ```

2. **Copy built files to ui directory:**
   ```bash
   # Windows PowerShell
   Copy-Item -Path "dist\*" -Destination "..\ui\" -Recurse -Force
   ```

3. **Start PHP Server:**
   ```bash
   cd C:\repos\fee-calculator-test1\test-ui
   php -S localhost:8000
   ```

4. Open http://localhost:8000 in your browser

## API Endpoints

The PHP API server provides endpoints at:
- `http://localhost:8000/api/v1/transactions` - Get all services/transactions
- `http://localhost:8000/api/v1/transactions/{type}/parameters` - Get parameters
- `http://localhost:8000/api/v1/transactions/{type}/fee` - Calculate fee

## Troubleshooting

- **CORS errors**: Make sure the PHP server is running on port 8000
- **API not found**: Check that `api.php` is in the `test-ui` directory
- **React build fails**: Run `npm install` first

