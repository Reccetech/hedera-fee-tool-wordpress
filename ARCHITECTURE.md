# Architecture Overview

## Design Decisions

### 1. JSON-Based Architecture
- **Data**: `simpleFeesSchedules.json` contains all fee data
- **Metadata**: `transactionMetadata.json` contains parameter definitions, descriptions, custom fee info
- **Business Logic**: Hardcoded in PHP calculator classes
- **Update Process**: Simply replace JSON files, changes detected automatically

### 2. Layered Architecture

```
┌─────────────────────────────────────┐
│   WordPress REST API / Test UI     │
│   (REST_Controller.php / api.php)   │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│   FeeCalculator (Orchestrator)      │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│   Specialized Calculators           │
│   (EntityCreate, CryptoTransfer...) │
└──────────────┬──────────────────────┘
               │
┌──────────────▼──────────────────────┐
│   FeeScheduleLoader                 │
│   (JSON loading & caching)           │
└─────────────────────────────────────┘
```

### 3. Core Classes (WordPress-Independent)

- **FeeScheduleLoader**: Singleton that loads and caches JSON files
  - Detects file modifications automatically
  - Falls back to cached version on error
  - Validates JSON structure

- **FeeCalculator**: Main orchestrator
  - Routes to appropriate calculator based on `calculatorType`
  - Provides service/transaction metadata

- **FeeResult**: Result object
  - Stores fee in ucents internally
  - Converts to USD for API responses

- **FeeConstants**: Hardcoded constants
  - Gas limits, free amounts, conversion factors
  - Cannot be updated via JSON

### 4. Calculator Classes

Each calculator handles specific transaction logic:

- **SimpleCalculator**: Base fee + extras (most transactions)
- **EntityCreateCalculator**: EntityCreate logic (numFreeSignatures = numFreeKeys + 1)
- **EntityUpdateCalculator**: EntityUpdate logic
- **CryptoTransferCalculator**: Complex CryptoTransfer/TokenTransfer logic
- **TokenMintCalculator**: Different base fees for Fungible/NonFungible
- **TokenBurnCalculator**: TokenBurn logic
- **TokenWipeCalculator**: TokenAccountWipe logic
- **FileOperationsCalculator**: File operations with keys + bytes
- **ContractCreateCalculator**: ContractCreate with gas + keys
- **ContractBasedOnGasCalculator**: ContractCall/EthereumTransaction
- **LambdaSStoreCalculator**: LambdaSStore (gas only)

### 5. Update Mechanism

**Automatic Detection**:
- File modification time checked on each request
- If file changed, reload automatically
- No manual cache clearing needed

**Error Handling**:
- If JSON invalid, fall back to cached version
- Log error for admin review
- Show notification in WordPress admin (future)

**Update Methods**:
1. **File System**: Replace JSON files directly
2. **WordPress Admin UI**: Upload/edit JSON files (future)

### 6. Unit Conversion

- **Internal**: All fees stored in ucents (micro-cents)
- **API Response**: Converted to USD for display
- **Conversion**: 1 USD = 10,000,000,000 ucents

### 7. WordPress Integration

**REST API Endpoints**:
- Base: `/wp-json/hedera-fees/v1/`
- Matches Java API structure but different namespace
- Frontend can be configured with different URLs

**Autoloading**:
- PSR-4 style autoloader
- Namespace-based class loading

### 8. Standalone Test UI

**Bootstrap**:
- `bootstrap.php` loads core classes without WordPress
- `api.php` provides REST API endpoints matching Java API
- `index.php` routes requests

**API Endpoints**:
- Base: `/api/v1/` (matches Java API)
- Same endpoints as WordPress REST API

### 9. Parameter Validation

**Validation Rules**:
- Type checking (number, list)
- Range validation (min/max)
- Custom rules (e.g., CryptoTransfer must have 2+ entries)

**Error Messages**:
- Clear, user-friendly error messages
- Returned in API response

### 10. Included Defaults

**Helper Class**:
- `IncludedDefaultsHelper` calculates what's included in base fee
- Used by UI to display "Included in base fee" section
- Business rules hardcoded (matches Java logic)

## File Structure

```
hedera-fee-calculator/
├── core/                          # WordPress-independent core
│   ├── FeeCalculator.php         # Main orchestrator
│   ├── FeeConstants.php          # Hardcoded constants
│   ├── FeeResult.php             # Result object
│   ├── FeeScheduleLoader.php     # JSON loader & cache
│   └── IncludedDefaultsHelper.php # UI helper
│
├── includes/
│   ├── Calculators/              # Specialized calculators
│   │   ├── BaseCalculator.php
│   │   ├── SimpleCalculator.php
│   │   ├── EntityCreateCalculator.php
│   │   ├── EntityUpdateCalculator.php
│   │   ├── CryptoTransferCalculator.php
│   │   ├── TokenMintCalculator.php
│   │   ├── TokenBurnCalculator.php
│   │   ├── TokenWipeCalculator.php
│   │   ├── FileOperationsCalculator.php
│   │   ├── ContractCreateCalculator.php
│   │   ├── ContractBasedOnGasCalculator.php
│   │   └── LambdaSStoreCalculator.php
│   │
│   └── Validators/
│       └── ParameterValidator.php
│
├── wordpress/
│   └── REST_API/
│       └── REST_Controller.php   # WordPress REST API
│
├── test-ui/                       # Standalone test interface
│   ├── bootstrap.php
│   ├── api.php
│   ├── index.php
│   └── ui/
│       └── index.html
│
├── data/                          # Updatable JSON files
│   ├── simpleFeesSchedules.json
│   └── transactionMetadata.json
│
└── hedera-fee-calculator.php      # Main plugin file
```

## Key Features

✅ **Easy Updates**: Just replace JSON files  
✅ **Automatic Detection**: File changes detected automatically  
✅ **Error Handling**: Graceful fallback to cached version  
✅ **WordPress Integration**: Full REST API  
✅ **Standalone Testing**: Test without WordPress  
✅ **Complete Logic**: All complex calculations supported  
✅ **Type Safety**: Parameter validation  
✅ **Performance**: Caching with file modification time  

## Future Enhancements

- WordPress admin UI for JSON editing
- JSON schema validation
- Version checking
- Admin notifications for errors
- Transaction history/logging

