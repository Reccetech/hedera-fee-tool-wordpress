<?php

namespace HederaFeeCalculator\Core;

use HederaFeeCalculator\Calculators\BaseCalculator;
use HederaFeeCalculator\Calculators\SimpleCalculator;
use HederaFeeCalculator\Calculators\EntityCreateCalculator;
use HederaFeeCalculator\Calculators\EntityUpdateCalculator;
use HederaFeeCalculator\Calculators\CryptoTransferCalculator;
use HederaFeeCalculator\Calculators\TokenMintCalculator;
use HederaFeeCalculator\Calculators\TokenBurnCalculator;
use HederaFeeCalculator\Calculators\TokenWipeCalculator;
use HederaFeeCalculator\Calculators\FileOperationsCalculator;
use HederaFeeCalculator\Calculators\ContractCreateCalculator;
use HederaFeeCalculator\Calculators\ContractBasedOnGasCalculator;
use HederaFeeCalculator\Calculators\LambdaSStoreCalculator;

/**
 * Main fee calculator that routes to appropriate calculator
 */
class FeeCalculator {
    private $loader;
    private $calculators = [];
    
    public function __construct(FeeScheduleLoader $loader = null) {
        $this->loader = $loader ?? FeeScheduleLoader::getInstance();
        $this->initializeCalculators();
    }
    
    private function initializeCalculators() {
        $this->calculators['Simple'] = new SimpleCalculator($this->loader);
        $this->calculators['EntityCreate'] = new EntityCreateCalculator($this->loader);
        $this->calculators['EntityUpdate'] = new EntityUpdateCalculator($this->loader);
        $this->calculators['CryptoTransfer'] = new CryptoTransferCalculator($this->loader);
        $this->calculators['TokenMint'] = new TokenMintCalculator($this->loader);
        $this->calculators['TokenBurn'] = new TokenBurnCalculator($this->loader);
        $this->calculators['TokenWipe'] = new TokenWipeCalculator($this->loader);
        $this->calculators['FileOperations'] = new FileOperationsCalculator($this->loader);
        $this->calculators['ContractCreate'] = new ContractCreateCalculator($this->loader);
        $this->calculators['ContractBasedOnGas'] = new ContractBasedOnGasCalculator($this->loader);
        $this->calculators['LambdaSStore'] = new LambdaSStoreCalculator($this->loader);
    }
    
    /**
     * Calculate fee for a transaction
     * @param string $transactionName Transaction name
     * @param array $parameters User-provided parameters
     * @return FeeResult
     */
    public function calculateFee($transactionName, $parameters) {
        $metadata = $this->loader->getTransactionMetadata($transactionName);
        
        if (!$metadata) {
            throw new \Exception("Transaction metadata not found: " . $transactionName);
        }
        
        $calculatorType = $metadata['calculatorType'] ?? 'Simple';
        
        if (!isset($this->calculators[$calculatorType])) {
            throw new \Exception("Calculator not found for type: " . $calculatorType);
        }
        
        $calculator = $this->calculators[$calculatorType];
        return $calculator->calculate($transactionName, $parameters);
    }
    
    /**
     * Get services map
     */
    public function getServices() {
        return $this->loader->getServices();
    }
    
    /**
     * Get transaction description
     */
    public function getDescription($transactionName) {
        $metadata = $this->loader->getTransactionMetadata($transactionName);
        return $metadata['description'] ?? '';
    }
    
    /**
     * Get transaction parameters
     */
    public function getParameters($transactionName) {
        $metadata = $this->loader->getTransactionMetadata($transactionName);
        return $metadata['parameters'] ?? [];
    }
}

