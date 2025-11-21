<?php

namespace HederaFeeCalculator\Core;

use HederaFeeCalculator\Core\FeeConstants;

/**
 * Helper to calculate what's included in base fee for display
 */
class IncludedDefaultsHelper {
    
    /**
     * Get included defaults for a transaction
     * @param string $transactionName
     * @param FeeScheduleLoader $loader
     * @return array Array of strings describing what's included
     */
    public static function getIncludedDefaults($transactionName, FeeScheduleLoader $loader) {
        $defaults = [];
        $transaction = $loader->getTransaction($transactionName);
        $metadata = $loader->getTransactionMetadata($transactionName);
        
        if (!$transaction || !$metadata) {
            return $defaults;
        }
        
        // EntityCreate transactions - numFreeSignatures = numFreeKeys + 1
        if (in_array($transactionName, ['CryptoCreate', 'ConsensusCreateTopic', 'ScheduleCreate'])) {
            $numFreeKeys = 1;
            foreach ($transaction['extras'] as $extra) {
                if ($extra['name'] === 'KEYS') {
                    $numFreeKeys = $extra['includedCount'];
                    $defaults[] = $numFreeKeys . " key" . ($numFreeKeys > 1 ? "s" : "");
                    break;
                }
            }
            $defaults[] = ($numFreeKeys + 1) . " signatures";
            return $defaults;
        }
        
        if ($transactionName === 'TokenCreate') {
            $numFreeKeys = 7;
            $defaults[] = "7 keys";
            $defaults[] = "8 signatures";
            return $defaults;
        }
        
        // EntityUpdate transactions
        if (in_array($transactionName, ['CryptoUpdate', 'ConsensusUpdateTopic', 'ContractUpdate'])) {
            $numFreeKeys = 1;
            foreach ($transaction['extras'] as $extra) {
                if ($extra['name'] === 'KEYS') {
                    $numFreeKeys = $extra['includedCount'];
                    $defaults[] = $numFreeKeys . " key" . ($numFreeKeys > 1 ? "s" : "");
                    break;
                }
            }
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        if ($transactionName === 'TokenUpdate') {
            $defaults[] = "7 keys";
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // FileOperations
        if (in_array($transactionName, ['FileCreate', 'FileUpdate', 'FileAppend'])) {
            $defaults[] = "1 key";
            $defaults[] = "1000 bytes";
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // ContractCreate
        if ($transactionName === 'ContractCreate') {
            $defaults[] = "1 key";
            $defaults[] = "21,000 gas";
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // ContractCall/EthereumTransaction
        if (in_array($transactionName, ['ContractCall', 'EthereumTransaction'])) {
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // ConsensusSubmitMessage
        if ($transactionName === 'ConsensusSubmitMessage') {
            $defaults[] = "1024 bytes";
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // CryptoTransfer/TokenTransfer/TokenAirdrop
        if (in_array($transactionName, ['CryptoTransfer', 'TokenTransfer', 'TokenAirdrop'])) {
            $defaults[] = "1 token";
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // TokenMint
        if ($transactionName === 'TokenMint') {
            $defaults[] = "1 token";
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // TokenBurn
        if ($transactionName === 'TokenBurn') {
            $defaults[] = "1 NFT";
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // TokenAccountWipe
        if ($transactionName === 'TokenAccountWipe') {
            $defaults[] = "1 NFT";
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // TokenAssociate/Dissociate
        if (in_array($transactionName, ['TokenAssociateToAccount', 'TokenDissociateFromAccount'])) {
            $defaults[] = "1 token type";
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // TokenAirdropOperations
        if (in_array($transactionName, ['TokenClaimAirdrop', 'TokenCancelAirdrop', 'TokenReject'])) {
            $defaults[] = "1 token type";
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // TokenGetNftInfos
        if ($transactionName === 'TokenGetNftInfos') {
            $defaults[] = "1 token";
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // CryptoAllowance
        if (in_array($transactionName, ['CryptoApproveAllowance', 'CryptoDeleteAllowance'])) {
            $defaults[] = "1 allowance";
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // LambdaSStore
        if ($transactionName === 'LambdaSStore') {
            $defaults[] = "1 signature";
            return $defaults;
        }
        
        // All other transactions - just 1 signature
        $defaults[] = "1 signature";
        return $defaults;
    }
}

