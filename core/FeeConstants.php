<?php

namespace HederaFeeCalculator\Core;

/**
 * Hardcoded constants that cannot be updated via JSON
 */
class FeeConstants {
    // Gas constants
    const MIN_GAS = 21000;
    const MAX_GAS = 15000000;
    
    // Free keys
    const FREE_KEYS_DEFAULT = 1; // First 1 key is included in the base fee: adminKey
    const FREE_KEYS_TOKEN = 7; // First 7 keys are included in the base fee
    
    // Key limits
    const MIN_KEYS = 1;
    const MAX_KEYS = 100;
    
    // Token constants
    const TOKEN_FREE_TOKENS = 1;
    
    // HCS constants
    const HCS_FREE_BYTES = 1024;
    const HCS_MIN_BYTES = 1;
    const HCS_MAX_BYTES = 1024;
    
    // File constants
    const FILE_FREE_BYTES = 1000;
    const FILE_MIN_BYTES = 1;
    const FILE_MAX_BYTES = 131072; // 128 * 1024
    
    // Allowance constants
    const FREE_ALLOWANCES = 1;
    const MIN_ALLOWANCES = 1;
    const MAX_ALLOWANCES = 10;
    
    // Signature constants
    const MIN_SIGNATURES = 1;
    const MAX_SIGNATURES = 100;
    
    // Conversion: ucents to USD (divide by 10,000,000,000)
    const UCENTS_TO_USD = 10000000000;
    
    /**
     * Convert ucents to USD
     */
    public static function ucentsToUsd($ucents) {
        return $ucents / self::UCENTS_TO_USD;
    }
    
    /**
     * Convert USD to ucents
     */
    public static function usdToUcents($usd) {
        return (int)($usd * self::UCENTS_TO_USD);
    }
}

