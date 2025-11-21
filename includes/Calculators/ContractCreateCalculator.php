<?php

namespace HederaFeeCalculator\Calculators;

use HederaFeeCalculator\Core\FeeConstants;
use HederaFeeCalculator\Core\FeeResult;

/**
 * Calculator for ContractCreate
 * Handles gas + keys
 */
class ContractCreateCalculator extends BaseCalculator {
    
    public function calculate($transactionName, $parameters) {
        $transaction = $this->loader->getTransaction($transactionName);
        if (!$transaction) {
            throw new \Exception("Transaction not found: " . $transactionName);
        }
        
        $fee = new FeeResult();
        
        // Add base fee
        $baseFee = $transaction['baseFee'];
        $fee->addDetail('Base fee', 1, $baseFee);
        
        // Calculate gas (MIN_GAS is free)
        $gas = $this->getParam($parameters, 'gas', FeeConstants::MIN_GAS);
        $gasToCharge = max($gas - FeeConstants::MIN_GAS, 0);
        
        if ($gasToCharge > 0) {
            $gasFee = $this->loader->getExtraFee('GAS');
            $fee->addDetail('Additional Gas fee', $gasToCharge, $gasToCharge * $gasFee);
        }
        
        // Calculate keys (over 1)
        $numKeys = $this->getParam($parameters, 'numKeys', 1);
        if ($numKeys > 1) {
            $additional = $numKeys - 1;
            $keyFee = $this->loader->getExtraFee('KEYS');
            $fee->addDetail('Additional keys', $additional, $additional * $keyFee);
        }
        
        // Add signature fees (default 1, not numFreeKeys + 1 for ContractCreate)
        $numSignatures = $this->getParam($parameters, 'numSignatures', 1);
        $this->addSignatureFees($fee, $numSignatures);
        
        return $fee;
    }
}

