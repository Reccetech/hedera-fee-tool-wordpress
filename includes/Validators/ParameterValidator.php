<?php

namespace HederaFeeCalculator\Validators;

use HederaFeeCalculator\Core\FeeScheduleLoader;

/**
 * Validates parameters for transactions
 */
class ParameterValidator {
    private $loader;
    
    public function __construct(FeeScheduleLoader $loader = null) {
        $this->loader = $loader ?? \HederaFeeCalculator\Core\FeeScheduleLoader::getInstance();
    }
    
    /**
     * Validate parameters
     * @param string $transactionName Transaction name
     * @param array $parameters Parameters to validate
     * @return array ['result' => true/false, 'message' => 'error message if false']
     */
    public function validate($transactionName, $parameters) {
        $metadata = $this->loader->getTransactionMetadata($transactionName);
        
        if (!$metadata) {
            return ['result' => false, 'message' => 'Transaction not found: ' . $transactionName];
        }
        
        $paramDefs = $metadata['parameters'] ?? [];
        
        // Validate each parameter
        foreach ($paramDefs as $paramDef) {
            $name = $paramDef['name'];
            
            // Skip if parameter not provided (will use default)
            if (!isset($parameters[$name])) {
                continue;
            }
            
            $value = $parameters[$name];
            $type = $paramDef['type'];
            
            if ($type === 'number') {
                if (!is_numeric($value)) {
                    return ['result' => false, 'message' => "Parameter {$name} must be a number"];
                }
                
                $intValue = (int)$value;
                if ($intValue < $paramDef['min'] || $intValue > $paramDef['max']) {
                    return [
                        'result' => false, 
                        'message' => "Parameter {$name} must be in range [{$paramDef['min']}, {$paramDef['max']}]"
                    ];
                }
            } else if ($type === 'list') {
                if (!in_array($value, $paramDef['values'])) {
                    return [
                        'result' => false,
                        'message' => "Parameter {$name} must be one of: " . implode(', ', $paramDef['values'])
                    ];
                }
            }
        }
        
        // Custom validation rules
        $customValidation = $this->customValidate($transactionName, $parameters);
        if (!$customValidation['result']) {
            return $customValidation;
        }
        
        return ['result' => true, 'message' => ''];
    }
    
    /**
     * Custom validation rules for specific transactions
     */
    private function customValidate($transactionName, $parameters) {
        // CryptoTransfer validation: must have at least 2 entries
        if ($transactionName === 'CryptoTransfer' || 
            $transactionName === 'TokenTransfer' || 
            $transactionName === 'TokenAirdrop') {
            
            $numAccounts = isset($parameters['numAccountsInvolved']) ? (int)$parameters['numAccountsInvolved'] : 2;
            $ftNoCustom = isset($parameters['numFTNoCustomFeeEntries']) ? (int)$parameters['numFTNoCustomFeeEntries'] : 0;
            $nftNoCustom = isset($parameters['numNFTNoCustomFeeEntries']) ? (int)$parameters['numNFTNoCustomFeeEntries'] : 0;
            $nftWithCustom = isset($parameters['numNFTWithCustomFeeEntries']) ? (int)$parameters['numNFTWithCustomFeeEntries'] : 0;
            
            if ($numAccounts < 2 && 
                $ftNoCustom < 2 && 
                $nftNoCustom < 2 && 
                $nftWithCustom < 2) {
                return [
                    'result' => false,
                    'message' => 'There must be at least 2 entries of hbar or token transfers.'
                ];
            }
        }
        
        return ['result' => true, 'message' => ''];
    }
}

