# Hedera Fee Calculator WordPress Plugin

A WordPress plugin for calculating Hedera network transaction fees, easily updatable via JSON files.

## Features

- **Easy Updates**: Update fees by simply replacing JSON files
- **WordPress Integration**: Full REST API for WordPress
- **Standalone Test UI**: Test without WordPress installation
- **Complete Transaction Support**: All 50+ Hedera transactions
- **Complex Logic Support**: Handles CryptoTransfer, TokenMint, and other complex calculations

## Structure

```
hedera-fee-calculator/
├── core/                    # WordPress-independent core classes
├── wordpress/               # WordPress integration layer
├── test-ui/                 # Standalone test interface
├── data/                    # JSON data files (updatable)
│   ├── simpleFeesSchedules.json
│   └── transactionMetadata.json
└── hedera-fee-calculator.php
```

## Installation

### WordPress Plugin

1. Copy the entire plugin folder to `wp-content/plugins/hedera-fee-calculator/`
2. Activate the plugin in WordPress admin
3. REST API endpoints available at: `/wp-json/hedera-fees/v1/`

### Standalone Test UI

1. Navigate to `test-ui/` directory
2. Run PHP built-in server:
   ```bash
   php -S localhost:8000
   ```
3. Open `http://localhost:8000` in browser

## Updating Fees

### Method 1: File System
1. Edit `data/simpleFeesSchedules.json` or `data/transactionMetadata.json`
2. Replace files in plugin directory
3. Changes are detected automatically on next request

### Method 2: WordPress Admin (Coming Soon)
- Admin UI for uploading/editing JSON files

## API Endpoints

### WordPress REST API
- Base URL: `/wp-json/hedera-fees/v1/`
- GET `/transactions` - Get all services and transactions
- GET `/transactions/{type}/description` - Get transaction description
- GET `/transactions/{type}/parameters` - Get transaction parameters
- POST `/transactions/{type}/check` - Validate parameters
- POST `/transactions/{type}/fee` - Calculate fee

### Standalone Test UI API
- Base URL: `/api/v1/`
- Same endpoints as above

## Data Files

### simpleFeesSchedules.json
Contains:
- Base fees for all transactions (in ucents)
- Extra/unit fees (SIGNATURES, BYTES, KEYS, etc.)
- Included counts per transaction

### transactionMetadata.json
Contains:
- Transaction descriptions
- Parameter definitions
- Custom fee information
- Calculator type assignments

## Development

### Core Classes (WordPress-independent)
- `FeeScheduleLoader` - Loads and caches JSON files
- `FeeCalculator` - Main calculation orchestrator
- `FeeResult` - Result object
- `FeeConstants` - Hardcoded constants

### Calculator Classes
- `SimpleCalculator` - For simple transactions
- `EntityCreateCalculator` - EntityCreate logic
- `EntityUpdateCalculator` - EntityUpdate logic
- `CryptoTransferCalculator` - Complex CryptoTransfer logic
- `TokenMintCalculator` - TokenMint logic
- And more...

## Notes

- All fees stored internally in ucents (micro-cents)
- Converted to USD only for API responses
- File modification time detection for automatic reloads
- Graceful error handling with cache fallback

