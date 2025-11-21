<?php

namespace HederaFeeCalculator\Calculators;

use HederaFeeCalculator\Core\FeeResult;

/**
 * Simple calculator for transactions with just base fee + extras
 */
class SimpleCalculator extends BaseCalculator {
    
    public function calculate($transactionName, $parameters) {
        $transaction = $this->loader->getTransaction($transactionName);
        if (!$transaction) {
            throw new \Exception("Transaction not found: " . $transactionName);
        }
        
        $fee = new FeeResult();
        
        // Add base fee
        $baseFee = $transaction['baseFee'];
        $fee->addDetail('Base fee', 1, $baseFee);
        
        // Handle custom fee if applicable
        $metadata = $this->loader->getTransactionMetadata($transactionName);
        if ($metadata && isset($metadata['customFee']['capable']) && $metadata['customFee']['capable']) {
            $hasCustomFee = isset($parameters['hasCustomFee']) && 
                           (strtolower($parameters['hasCustomFee']) === 'yes' || $parameters['hasCustomFee'] === 'Yes');
            if ($hasCustomFee && isset($metadata['customFee']['baseFee'])) {
                // Replace base fee with custom fee version
                $fee = new FeeResult();
                $fee->addDetail('Base fee', 1, $metadata['customFee']['baseFee']);
            } else {
                // Use regular base fee
                $fee->addDetail('Base fee', 1, $transaction['baseFee']);
            }
        } else {
            // No custom fee capability, use regular base fee
            $fee->addDetail('Base fee', 1, $transaction['baseFee']);
        }
        
        // Calculate extras
        foreach ($transaction['extras'] as $extra) {
            $extraName = $extra['name'];
            $includedCount = $extra['includedCount'];
            $paramValue = $this->getParam($parameters, $this->getParameterName($extraName), $includedCount);
            
            if ($paramValue > $includedCount) {
                $additional = $paramValue - $includedCount;
                $extraFee = $this->loader->getExtraFee($extraName);
                $totalExtraFee = $additional * $extraFee;
                $fee->addDetail($this->getExtraLabel($extraName), $additional, $totalExtraFee);
            }
        }
        
        // Add signature fees
        $numSignatures = $this->getParam($parameters, 'numSignatures', 1);
        $this->addSignatureFees($fee, $numSignatures);
        
        return $fee;
    }
    
    /**
     * Map extra name to parameter name
     */
    private function getParameterName($extraName) {
        $mapping = [
            'KEYS' => 'numKeys',
            'BYTES' => 'numBytes',
            'TOKEN_TYPES' => 'numTokenTypes',
            'NFT_SERIALS' => 'numTokens',
            'ACCOUNTS' => 'numAccountsInvolved',
            'STANDARD_FUNGIBLE_TOKENS' => 'numFTNoCustomFeeEntries',
            'STANDARD_NON_FUNGIBLE_TOKENS' => 'numNFTNoCustomFeeEntries',
            'CUSTOM_FEE_FUNGIBLE_TOKENS' => 'numFTWithCustomFeeEntries',
            'CUSTOM_FEE_NON_FUNGIBLE_TOKENS' => 'numNFTWithCustomFeeEntries',
            'ALLOWANCES' => 'numAllowances',
            'GAS' => 'gas',
            'HOOKS' => 'numHooksCreated'
        ];
        return $mapping[$extraName] ?? strtolower($extraName);
    }
    
    /**
     * Get human-readable label for extra
     */
    private function getExtraLabel($extraName) {
        $labels = [
            'KEYS' => 'Additional keys',
            'BYTES' => 'Additional message size',
            'TOKEN_TYPES' => 'Additional token-types',
            'NFT_SERIALS' => 'Additional NFTs',
            'ACCOUNTS' => 'Accounts involved',
            'STANDARD_FUNGIBLE_TOKENS' => 'FT no custom fee',
            'STANDARD_NON_FUNGIBLE_TOKENS' => 'NFT no custom fee',
            'CUSTOM_FEE_FUNGIBLE_TOKENS' => 'FT with custom fee',
            'CUSTOM_FEE_NON_FUNGIBLE_TOKENS' => 'NFT with custom fee',
            'ALLOWANCES' => 'Additional allowances',
            'GAS' => 'Additional Gas fee',
            'HOOKS' => 'Hook creation'
        ];
        return $labels[$extraName] ?? $extraName;
    }
}

