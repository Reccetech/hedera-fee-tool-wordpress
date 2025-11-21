<?php

namespace HederaFeeCalculator\Calculators;

use HederaFeeCalculator\Core\FeeConstants;
use HederaFeeCalculator\Core\FeeResult;
use HederaFeeCalculator\Core\FeeScheduleLoader;

/**
 * Base calculator class
 */
abstract class BaseCalculator {
    protected $loader;
    
    public function __construct(FeeScheduleLoader $loader) {
        $this->loader = $loader;
    }
    
    /**
     * Calculate fee for a transaction
     * @param string $transactionName Transaction name
     * @param array $parameters User-provided parameters
     * @return FeeResult
     */
    abstract public function calculate($transactionName, $parameters);
    
    /**
     * Get included count for an extra in a transaction
     */
    protected function getIncludedCount($transaction, $extraName) {
        foreach ($transaction['extras'] as $extra) {
            if ($extra['name'] === $extraName) {
                return $extra['includedCount'];
            }
        }
        return 0;
    }
    
    /**
     * Get parameter value with default
     */
    protected function getParam($parameters, $name, $default = 0) {
        return isset($parameters[$name]) ? (int)$parameters[$name] : $default;
    }
    
    /**
     * Add signature fees (common across all transactions)
     */
    protected function addSignatureFees(FeeResult $fee, $numSignatures, $numFreeSignatures = 1) {
        if ($numSignatures > $numFreeSignatures) {
            $additional = $numSignatures - $numFreeSignatures;
            $signatureFee = $this->loader->getExtraFee('SIGNATURES');
            $fee->addDetail('Additional signature verifications', $additional, $additional * $signatureFee);
        }
    }
}

