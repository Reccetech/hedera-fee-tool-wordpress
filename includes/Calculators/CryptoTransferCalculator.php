<?php

namespace HederaFeeCalculator\Calculators;

use HederaFeeCalculator\Core\FeeConstants;
use HederaFeeCalculator\Core\FeeResult;

/**
 * Complex calculator for CryptoTransfer, TokenTransfer, TokenAirdrop
 * Handles conditional logic for token detection, custom fees, priority ordering
 */
class CryptoTransferCalculator extends BaseCalculator {
    
    public function calculate($transactionName, $parameters) {
        $transaction = $this->loader->getTransaction($transactionName);
        if (!$transaction) {
            throw new \Exception("Transaction not found: " . $transactionName);
        }
        
        $fee = new FeeResult();
        
        // Extract values
        $ftNoCustom = $this->getParam($parameters, 'numFTNoCustomFeeEntries', 0);
        $nftNoCustom = $this->getParam($parameters, 'numNFTNoCustomFeeEntries', 0);
        $ftWithCustom = $this->getParam($parameters, 'numFTWithCustomFeeEntries', 0);
        $nftWithCustom = $this->getParam($parameters, 'numNFTWithCustomFeeEntries', 0);
        $customTokens = $ftWithCustom + $nftWithCustom;
        $tokenTransfersPresent = ($ftNoCustom + $nftNoCustom + $ftWithCustom + $nftWithCustom) > 0;
        
        $numFreeTokens = FeeConstants::TOKEN_FREE_TOKENS;
        $effectiveApi = $transactionName;
        
        // Determine effective API
        if ($tokenTransfersPresent) {
            if ($transactionName === 'CryptoTransfer') {
                $effectiveApi = 'TokenTransfer';
            }
        } else {
            $effectiveApi = 'CryptoTransfer';
        }
        
        // Get base fees
        $baseFeeCryptoTransfer = $this->loader->getTransaction('CryptoTransfer')['baseFee'];
        $baseFeeTokenTransfer = $this->loader->getTransaction('TokenTransfer')['baseFee'];
        $baseFeeTokenAirdrop = $this->loader->getTransaction('TokenAirdrop')['baseFee'];
        
        // Get custom fee base fees (hardcoded from BaseFeeRegistry)
        // TokenTransferWithCustomFee: 0.002 USD = 20,000,000 ucents
        // TokenAirdropWithCustomFee: 0.10100 USD = 1,010,000,000 ucents
        $baseFeeTokenTransferWithCustom = 20000000; // 0.002 USD in ucents
        $baseFeeTokenAirdropWithCustom = 1010000000; // 0.10100 USD in ucents
        
        // Determine base fee
        if ($customTokens > 0) {
            if ($effectiveApi === 'TokenTransfer') {
                $fee->addDetail('Base fee for ' . $effectiveApi . ' (with Custom fee)', 1, $baseFeeTokenTransferWithCustom);
            } else if ($effectiveApi === 'TokenAirdrop') {
                $fee->addDetail('Base fee for ' . $effectiveApi . ' (with Custom fee)', 1, $baseFeeTokenAirdropWithCustom);
            }
        } else {
            if ($effectiveApi === 'CryptoTransfer') {
                $fee->addDetail('Base fee for ' . $effectiveApi, 1, $baseFeeCryptoTransfer);
            } else if ($effectiveApi === 'TokenTransfer') {
                $fee->addDetail('Base fee for ' . $effectiveApi, 1, $baseFeeTokenTransfer);
            } else if ($effectiveApi === 'TokenAirdrop') {
                $fee->addDetail('Base fee for ' . $effectiveApi, 1, $baseFeeTokenAirdrop);
            }
        }
        
        // Accounts involved (over 2)
        $numAccounts = $this->getParam($parameters, 'numAccountsInvolved', 2);
        if ($numAccounts > 2) {
            $additional = $numAccounts - 2;
            $accountFee = $this->loader->getExtraFee('PER_CRYPTO_TRANSFER_ACCOUNT');
            $fee->addDetail('Accounts involved', $additional, $additional * $accountFee);
        }
        
        // Process tokens with custom fee first (priority)
        if ($ftWithCustom > 0) {
            if (($ftWithCustom - $numFreeTokens) > 0) {
                $additional = $ftWithCustom - $numFreeTokens;
                $fee->addDetail('FT with custom fee', $additional, $additional * $baseFeeTokenTransferWithCustom);
            }
            $numFreeTokens = 0;
        }
        if ($nftWithCustom > 0) {
            if (($nftWithCustom - $numFreeTokens) > 0) {
                $additional = $nftWithCustom - $numFreeTokens;
                $fee->addDetail('NFT with custom fee', $additional, $additional * $baseFeeTokenTransferWithCustom);
            }
            $numFreeTokens = 0;
        }
        if ($ftNoCustom > 0) {
            if (($ftNoCustom - $numFreeTokens) > 0) {
                $additional = $ftNoCustom - $numFreeTokens;
                $fee->addDetail('FT no custom fee', $additional, $additional * $baseFeeTokenTransfer);
            }
            $numFreeTokens = 0;
        }
        if ($nftNoCustom > 0) {
            if (($nftNoCustom - $numFreeTokens) > 0) {
                $additional = $nftNoCustom - $numFreeTokens;
                $fee->addDetail('NFT no custom fee', $additional, $additional * $baseFeeTokenTransfer);
            }
        }
        
        // Auto-created associations
        $numAutoAssociations = $this->getParam($parameters, 'numAutoAssociationsCreated', 0);
        if ($numAutoAssociations > 0) {
            $associateTransaction = $this->loader->getTransaction('TokenAssociateToAccount');
            $associateFee = $associateTransaction['baseFee'];
            $fee->addDetail('Auto token associations', $numAutoAssociations, $numAutoAssociations * $associateFee);
        }
        
        // Auto-created accounts
        $numAutoAccounts = $this->getParam($parameters, 'numAutoAccountsCreated', 0);
        if ($numAutoAccounts > 0) {
            $createTransaction = $this->loader->getTransaction('CryptoCreate');
            $createFee = $createTransaction['baseFee'];
            $fee->addDetail('Auto account creations', $numAutoAccounts, $numAutoAccounts * $createFee);
        }
        
        // Hook invocation fees
        $numHooksInvoked = $this->getParam($parameters, 'numHooksInvoked', 0);
        if ($numHooksInvoked > 0) {
            $hookInvokeFee = $this->loader->getExtraFee('HOOK_INVOKE');
            $fee->addDetail('Hook invocation', $numHooksInvoked, $numHooksInvoked * $hookInvokeFee);
        }
        
        // Gas consumed
        $gasConsumed = $this->getParam($parameters, 'gasConsumed', 0);
        if ($gasConsumed > 0) {
            $gasFee = $this->loader->getExtraFee('GAS');
            $totalGasFee = $gasConsumed * $gasFee;
            $fee->addDetail('Hook gas consumed', $gasConsumed, $totalGasFee);
        }
        
        // Add signature fees
        $numSignatures = $this->getParam($parameters, 'numSignatures', 1);
        $this->addSignatureFees($fee, $numSignatures);
        
        return $fee;
    }
}

