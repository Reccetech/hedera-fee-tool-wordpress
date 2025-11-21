<?php

namespace HederaFeeCalculator\Calculators;

use HederaFeeCalculator\Core\FeeConstants;
use HederaFeeCalculator\Core\FeeResult;

/**
 * Calculator for EntityCreate transactions (CryptoCreate, TokenCreate, ConsensusCreateTopic, ScheduleCreate)
 * Handles: numFreeSignatures = numFreeKeys + 1
 */
class EntityCreateCalculator extends BaseCalculator {
    
    public function calculate($transactionName, $parameters) {
        $transaction = $this->loader->getTransaction($transactionName);
        if (!$transaction) {
            throw new \Exception("Transaction not found: " . $transactionName);
        }
        
        $metadata = $this->loader->getTransactionMetadata($transactionName);
        $numFreeKeys = $this->getNumFreeKeys($transactionName);
        
        $fee = new FeeResult();
        
        // Handle custom fee
        $hasCustomFee = false;
        if ($metadata && isset($metadata['customFee']['capable']) && $metadata['customFee']['capable']) {
            $hasCustomFee = isset($parameters['hasCustomFee']) && 
                           (strtolower($parameters['hasCustomFee']) === 'yes' || $parameters['hasCustomFee'] === 'Yes');
            if ($hasCustomFee && isset($metadata['customFee']['baseFee'])) {
                $fee->addDetail('Base fee', 1, $metadata['customFee']['baseFee']);
            } else {
                $fee->addDetail('Base fee', 1, $transaction['baseFee']);
            }
        } else {
            $fee->addDetail('Base fee', 1, $transaction['baseFee']);
        }
        
        // Calculate keys
        $numKeys = $this->getParam($parameters, 'numKeys', $numFreeKeys);
        if ($numKeys > $numFreeKeys) {
            $additional = $numKeys - $numFreeKeys;
            $keyFee = $this->loader->getExtraFee('KEYS');
            $fee->addDetail('Additional keys', $additional, $additional * $keyFee);
        }
        
        // Handle hooks for CryptoCreate
        if ($transactionName === 'CryptoCreate') {
            $numHooksCreated = $this->getParam($parameters, 'numHooksCreated', 0);
            if ($numHooksCreated > 0) {
                $hookFee = $this->loader->getExtraFee('HOOK_CREATE');
                $fee->addDetail('Hook creation', $numHooksCreated, $numHooksCreated * $hookFee);
            }
        }
        
        // Add signature fees - EntityCreate: numFreeSignatures = numFreeKeys + 1
        $numSignatures = $this->getParam($parameters, 'numSignatures', $numFreeKeys + 1);
        $this->addSignatureFees($fee, $numSignatures, $numFreeKeys + 1);
        
        return $fee;
    }
    
    private function getNumFreeKeys($transactionName) {
        // TokenCreate has 7 free keys, others have 1
        return $transactionName === 'TokenCreate' ? FeeConstants::FREE_KEYS_TOKEN : FeeConstants::FREE_KEYS_DEFAULT;
    }
}

