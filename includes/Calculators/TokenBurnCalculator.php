<?php

namespace HederaFeeCalculator\Calculators;

use HederaFeeCalculator\Core\FeeResult;

/**
 * Calculator for TokenBurn
 */
class TokenBurnCalculator extends BaseCalculator {
    
    public function calculate($transactionName, $parameters) {
        $transaction = $this->loader->getTransaction($transactionName);
        if (!$transaction) {
            throw new \Exception("Transaction not found: " . $transactionName);
        }
        
        $fee = new FeeResult();
        $baseFee = $transaction['baseFee'];
        $fee->addDetail('Base fee', 1, $baseFee);
        
        // Get fungible/non-fungible type
        $fungibleOrNonFungible = isset($parameters['fungibleOrNonFungible']) ? 
            $parameters['fungibleOrNonFungible'] : 'Fungible';
        
        // For NonFungible, charge for additional tokens beyond 1
        if ($fungibleOrNonFungible === 'NonFungible') {
            $numTokens = $this->getParam($parameters, 'numTokens', 1);
            $numFreeTokens = 1;
            
            if ($numTokens > $numFreeTokens) {
                $additional = $numTokens - $numFreeTokens;
                $fee->addDetail('Additional NFTs', $additional, $additional * $baseFee);
            }
        }
        
        // Add signature fees
        $numSignatures = $this->getParam($parameters, 'numSignatures', 1);
        $this->addSignatureFees($fee, $numSignatures);
        
        return $fee;
    }
}

