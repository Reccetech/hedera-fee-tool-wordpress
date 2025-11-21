<?php

namespace HederaFeeCalculator\Calculators;

use HederaFeeCalculator\Core\FeeConstants;
use HederaFeeCalculator\Core\FeeResult;

/**
 * Calculator for EntityUpdate transactions (CryptoUpdate, TokenUpdate, ConsensusUpdateTopic, ContractUpdate)
 */
class EntityUpdateCalculator extends BaseCalculator {
    
    public function calculate($transactionName, $parameters) {
        $transaction = $this->loader->getTransaction($transactionName);
        if (!$transaction) {
            throw new \Exception("Transaction not found: " . $transactionName);
        }
        
        $numFreeKeys = $this->getNumFreeKeys($transactionName);
        
        $fee = new FeeResult();
        $fee->addDetail('Base fee', 1, $transaction['baseFee']);
        
        // Calculate keys
        $numKeys = $this->getParam($parameters, 'numKeys', $numFreeKeys);
        if ($numKeys > $numFreeKeys) {
            $additional = $numKeys - $numFreeKeys;
            $keyFee = $this->loader->getExtraFee('KEYS');
            $fee->addDetail('Additional keys', $additional, $additional * $keyFee);
        }
        
        // Handle hooks for CryptoUpdate
        if ($transactionName === 'CryptoUpdate') {
            $numHooksCreated = $this->getParam($parameters, 'numHooksCreated', 0);
            $numHooksUpdated = $this->getParam($parameters, 'numHooksUpdated', 0);
            $numHooksDeleted = $this->getParam($parameters, 'numHooksDeleted', 0);
            
            if ($numHooksCreated > 0) {
                $hookFee = $this->loader->getExtraFee('HOOK_CREATE');
                $fee->addDetail('Hook creation', $numHooksCreated, $numHooksCreated * $hookFee);
            }
            if ($numHooksUpdated > 0) {
                $hookFee = $this->loader->getExtraFee('HOOK_UPDATE');
                $fee->addDetail('Hook update', $numHooksUpdated, $numHooksUpdated * $hookFee);
            }
            if ($numHooksDeleted > 0) {
                $hookFee = $this->loader->getExtraFee('HOOK_DELETE');
                $fee->addDetail('Hook deletion', $numHooksDeleted, $numHooksDeleted * $hookFee);
            }
        }
        
        // Add signature fees (default 1)
        $numSignatures = $this->getParam($parameters, 'numSignatures', 1);
        $this->addSignatureFees($fee, $numSignatures);
        
        return $fee;
    }
    
    private function getNumFreeKeys($transactionName) {
        // TokenUpdate has 7 free keys, others have 1
        return $transactionName === 'TokenUpdate' ? FeeConstants::FREE_KEYS_TOKEN : FeeConstants::FREE_KEYS_DEFAULT;
    }
}

