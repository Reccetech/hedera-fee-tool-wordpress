<?php

namespace HederaFeeCalculator\Calculators;

use HederaFeeCalculator\Core\FeeConstants;
use HederaFeeCalculator\Core\FeeResult;

/**
 * Calculator for TokenMint
 * Handles different base fees for Fungible vs NonFungible
 */
class TokenMintCalculator extends BaseCalculator {
    
    public function calculate($transactionName, $parameters) {
        $transaction = $this->loader->getTransaction($transactionName);
        if (!$transaction) {
            throw new \Exception("Transaction not found: " . $transactionName);
        }
        
        $fee = new FeeResult();
        
        // Get fungible/non-fungible type
        $fungibleOrNonFungible = isset($parameters['fungibleOrNonFungible']) ? 
            $parameters['fungibleOrNonFungible'] : 'Fungible';
        
        // Base fees from BaseFeeRegistry (in ucents)
        // TokenMintFungible: 0.00100 USD = 10,000,000 ucents
        // TokenMintNonFungible: 0.02000 USD = 200,000,000 ucents
        $baseFeeFungible = 10000000;
        $baseFeeNonFungible = 200000000;
        
        $baseFeeForMint = ($fungibleOrNonFungible === 'Fungible') ? $baseFeeFungible : $baseFeeNonFungible;
        $fee->addDetail('Base fee', 1, $baseFeeForMint);
        
        // For NonFungible, charge for additional tokens beyond 1
        $numTokens = $this->getParam($parameters, 'numTokens', 1);
        $numFreeTokens = 1;
        
        if ($fungibleOrNonFungible === 'NonFungible' && $numTokens > $numFreeTokens) {
            $additional = $numTokens - $numFreeTokens;
            $fee->addDetail('Additional NFTs', $additional, $additional * $baseFeeForMint);
        }
        
        // Add signature fees
        $numSignatures = $this->getParam($parameters, 'numSignatures', 1);
        $this->addSignatureFees($fee, $numSignatures);
        
        return $fee;
    }
}

