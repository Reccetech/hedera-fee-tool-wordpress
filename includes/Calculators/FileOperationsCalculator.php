<?php

namespace HederaFeeCalculator\Calculators;

use HederaFeeCalculator\Core\FeeConstants;
use HederaFeeCalculator\Core\FeeResult;

/**
 * Calculator for FileOperations (FileCreate, FileUpdate, FileAppend)
 */
class FileOperationsCalculator extends BaseCalculator {
    
    public function calculate($transactionName, $parameters) {
        $transaction = $this->loader->getTransaction($transactionName);
        if (!$transaction) {
            throw new \Exception("Transaction not found: " . $transactionName);
        }
        
        $fee = new FeeResult();
        $fee->addDetail('Base fee', 1, $transaction['baseFee']);
        
        // Calculate keys
        $numKeys = $this->getParam($parameters, 'numKeys', 1);
        $numFreeKeys = 1;
        if ($numKeys > $numFreeKeys) {
            $additional = $numKeys - $numFreeKeys;
            $keyFee = $this->loader->getExtraFee('KEYS');
            $fee->addDetail('Additional keys', $additional, $additional * $keyFee);
        }
        
        // Calculate bytes
        $numBytes = $this->getParam($parameters, 'numBytes', FeeConstants::FILE_FREE_BYTES);
        if ($numBytes > FeeConstants::FILE_FREE_BYTES) {
            $additional = $numBytes - FeeConstants::FILE_FREE_BYTES;
            $byteFee = $this->loader->getExtraFee('PER_FILE_BYTE');
            $fee->addDetail('Additional file size', $additional, $additional * $byteFee);
        }
        
        // Add signature fees
        $numSignatures = $this->getParam($parameters, 'numSignatures', 1);
        $this->addSignatureFees($fee, $numSignatures);
        
        return $fee;
    }
}

