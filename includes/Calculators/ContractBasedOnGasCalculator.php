<?php

namespace HederaFeeCalculator\Calculators;

use HederaFeeCalculator\Core\FeeConstants;
use HederaFeeCalculator\Core\FeeResult;

/**
 * Calculator for ContractCall and EthereumTransaction
 * isMinGasFree = false (no free gas)
 */
class ContractBasedOnGasCalculator extends BaseCalculator {
    
    public function calculate($transactionName, $parameters) {
        $transaction = $this->loader->getTransaction($transactionName);
        if (!$transaction) {
            throw new \Exception("Transaction not found: " . $transactionName);
        }
        
        $fee = new FeeResult();
        $fee->addDetail('Base fee', 1, $transaction['baseFee']);
        
        // Gas - no free gas for ContractCall/EthereumTransaction
        $gas = $this->getParam($parameters, 'gas', FeeConstants::MIN_GAS);
        $gasToCharge = max($gas - 0, 0); // No free gas
        
        if ($gasToCharge > 0) {
            $gasFee = $this->loader->getExtraFee('GAS');
            $fee->addDetail('Additional Gas fee', $gasToCharge, $gasToCharge * $gasFee);
        }
        
        // Add signature fees
        $numSignatures = $this->getParam($parameters, 'numSignatures', 1);
        $this->addSignatureFees($fee, $numSignatures);
        
        return $fee;
    }
}

