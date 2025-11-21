# Test UI Setup Instructions

## Option 1: Copy React Build (Recommended)

1. Build the React app from `hedera-fee-tool-local/fee-ui`:
   ```bash
   cd hedera-fee-tool-local/fee-ui
   npm install
   npm run build
   ```

2. Copy the `dist/` folder contents to `test-ui/ui/`

3. Update the API URL in the built files to point to `/api/v1/transactions`

## Option 2: Use React Development Server

1. Copy `fee-ui` folder from `hedera-fee-tool-local` to `test-ui/react-ui/`

2. Update `fee-ui/.env`:
   ```
   VITE_BACKEND_URL=http://localhost:8000/api/v1/transactions
   ```

3. Run both servers:
   ```bash
   # Terminal 1: PHP API server
   cd test-ui
   php -S localhost:8000
   
   # Terminal 2: React dev server
   cd test-ui/react-ui
   npm run dev
   ```

## Option 3: Simple HTML (Basic)

A basic HTML file is provided in `test-ui/ui/index.html` that can be enhanced.

