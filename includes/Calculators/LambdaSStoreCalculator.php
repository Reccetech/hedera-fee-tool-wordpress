<?php

namespace HederaFeeCalculator\Calculators;

use HederaFeeCalculator\Core\FeeResult;

/**
 * Calculator for LambdaSStore
 * Only charges for gas consumed, no base fee
 */
class LambdaSStoreCalculator extends BaseCalculator {
    
    public function calculate($transactionName, $parameters) {
        $transaction = $this->loader->getTransaction($transactionName);
        if (!$transaction) {
            throw new \Exception("Transaction not found: " . $transactionName);
        }
        
        $fee = new FeeResult();
        
        // Only charge for gas consumed
        $gas = $this->getParam($parameters, 'gas', 0);
        
        if ($gas > 0) {
            $gasFee = $this->loader->getExtraFee('GAS');
            $totalGasFee = $gas * $gasFee;
            $fee->addDetail('Gas consumed', $gas, $totalGasFee);
        } else {
            // No gas consumed, fee is 0
            $fee->addDetail('Base fee', 1, 0);
        }
        
        // Add signature fees
        $numSignatures = $this->getParam($parameters, 'numSignatures', 1);
        $this->addSignatureFees($fee, $numSignatures);
        
        return $fee;
    }
}

