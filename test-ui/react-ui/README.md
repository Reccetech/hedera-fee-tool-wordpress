# React UI Setup

This is the React frontend for the Hedera Fee Calculator test UI.

## Quick Start

1. Install dependencies:
   ```bash
   npm install
   ```

2. Start the development server:
   ```bash
   npm run dev
   ```

3. In another terminal, start the PHP API server:
   ```bash
   cd ..
   php -S localhost:8000
   ```

4. Open http://localhost:3000 in your browser

## Configuration

The API URL is configured in `.env`:
```
VITE_BACKEND_URL=http://localhost:8000/api/v1/transactions
```

## Build for Production

```bash
npm run build
```

The built files will be in `dist/` directory. You can copy these to `../ui/` to serve them statically.

