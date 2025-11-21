<?php

namespace HederaFeeCalculator\Core;

/**
 * Represents the result of a fee calculation
 */
class FeeResult {
    public $fee; // Total fee in ucents
    public $details; // Array of fee detail objects
    
    public function __construct() {
        $this->fee = 0;
        $this->details = [];
    }
    
    /**
     * Add a fee detail
     * @param string $label Description of the fee component
     * @param int $value Quantity/amount
     * @param int $fee Fee in ucents
     */
    public function addDetail($label, $value, $fee) {
        $this->details[$label] = [
            'value' => $value,
            'fee' => $fee
        ];
        $this->fee += $fee;
    }
    
    /**
     * Convert to JSON format for API response
     * Converts ucents to USD for display
     */
    public function toJSON() {
        $result = [
            'fee' => FeeConstants::ucentsToUsd($this->fee),
            'details' => []
        ];
        
        foreach ($this->details as $label => $detail) {
            $result['details'][$label] = [
                'value' => $detail['value'],
                'fee' => FeeConstants::ucentsToUsd($detail['fee'])
            ];
        }
        
        return $result;
    }
}

