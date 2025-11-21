<?php

namespace HederaFeeCalculator\Core;

/**
 * Loads and caches the fee schedule JSON file
 * Detects file changes and reloads automatically
 */
class FeeScheduleLoader {
    private static $instance = null;
    private $scheduleData = null;
    private $metadataData = null;
    private $scheduleFilePath;
    private $metadataFilePath;
    private $scheduleLastModTime = 0;
    private $metadataLastModTime = 0;
    private $cacheExpiry = 3600; // 1 hour default
    
    private function __construct($schedulePath = null, $metadataPath = null) {
        $baseDir = dirname(__DIR__) . '/data';
        $this->scheduleFilePath = $schedulePath ?? $baseDir . '/simpleFeesSchedules.json';
        $this->metadataFilePath = $metadataPath ?? $baseDir . '/transactionMetadata.json';
    }
    
    public static function getInstance($schedulePath = null, $metadataPath = null) {
        if (self::$instance === null) {
            self::$instance = new self($schedulePath, $metadataPath);
        }
        return self::$instance;
    }
    
    /**
     * Load fee schedule data
     * @param bool $forceReload Force reload even if file hasn't changed
     * @return array Fee schedule data
     */
    public function loadSchedule($forceReload = false) {
        $currentModTime = @filemtime($this->scheduleFilePath);
        
        if ($forceReload || 
            $this->scheduleData === null || 
            $currentModTime > $this->scheduleLastModTime ||
            $this->isCacheExpired()) {
            
            $this->reloadSchedule();
            $this->scheduleLastModTime = $currentModTime ?: 0;
        }
        
        return $this->scheduleData;
    }
    
    /**
     * Load transaction metadata
     * @param bool $forceReload Force reload even if file hasn't changed
     * @return array Metadata data
     */
    public function loadMetadata($forceReload = false) {
        $currentModTime = @filemtime($this->metadataFilePath);
        
        if ($forceReload || 
            $this->metadataData === null || 
            $currentModTime > $this->metadataLastModTime ||
            $this->isCacheExpired()) {
            
            $this->reloadMetadata();
            $this->metadataLastModTime = $currentModTime ?: 0;
        }
        
        return $this->metadataData;
    }
    
    private function reloadSchedule() {
        $json = @file_get_contents($this->scheduleFilePath);
        
        if ($json === false) {
            if ($this->scheduleData !== null) {
                error_log("Cannot read fee schedule file, using cached version");
                return; // Keep existing data
            }
            throw new \Exception("Cannot read fee schedule file: " . $this->scheduleFilePath);
        }
        
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            if ($this->scheduleData !== null) {
                error_log("Invalid JSON in fee schedule, using cached version: " . json_last_error_msg());
                return; // Keep existing data
            }
            throw new \Exception("Invalid JSON in fee schedule: " . json_last_error_msg());
        }
        
        $this->validateScheduleStructure($data);
        $this->scheduleData = $data;
    }
    
    private function reloadMetadata() {
        $json = @file_get_contents($this->metadataFilePath);
        
        if ($json === false) {
            if ($this->metadataData !== null) {
                error_log("Cannot read metadata file, using cached version");
                return; // Keep existing data
            }
            throw new \Exception("Cannot read metadata file: " . $this->metadataFilePath);
        }
        
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            if ($this->metadataData !== null) {
                error_log("Invalid JSON in metadata, using cached version: " . json_last_error_msg());
                return; // Keep existing data
            }
            throw new \Exception("Invalid JSON in metadata: " . json_last_error_msg());
        }
        
        $this->validateMetadataStructure($data);
        $this->metadataData = $data;
    }
    
    private function validateScheduleStructure($data) {
        if (!isset($data['extras']) || !is_array($data['extras'])) {
            throw new \Exception("Missing or invalid 'extras' in fee schedule");
        }
        if (!isset($data['services']) || !is_array($data['services'])) {
            throw new \Exception("Missing or invalid 'services' in fee schedule");
        }
    }
    
    private function validateMetadataStructure($data) {
        if (!isset($data['transactions']) || !is_array($data['transactions'])) {
            throw new \Exception("Missing or invalid 'transactions' in metadata");
        }
    }
    
    private function isCacheExpired() {
        // For now, always check file modification time
        // Could add time-based expiry here if needed
        return false;
    }
    
    public function clearCache() {
        $this->scheduleData = null;
        $this->metadataData = null;
        $this->scheduleLastModTime = 0;
        $this->metadataLastModTime = 0;
    }
    
    /**
     * Get services map (service name => array of transaction names)
     */
    public function getServices() {
        $schedule = $this->loadSchedule();
        $result = [];
        
        foreach ($schedule['services'] as $service) {
            $serviceName = $service['name'];
            $result[$serviceName] = [];
            foreach ($service['schedule'] as $transaction) {
                $result[$serviceName][] = $transaction['name'];
            }
        }
        
        return $result;
    }
    
    /**
     * Get transaction from schedule
     */
    public function getTransaction($transactionName) {
        $schedule = $this->loadSchedule();
        
        foreach ($schedule['services'] as $service) {
            foreach ($service['schedule'] as $transaction) {
                if ($transaction['name'] === $transactionName) {
                    return $transaction;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get transaction metadata
     */
    public function getTransactionMetadata($transactionName) {
        $metadata = $this->loadMetadata();
        return $metadata['transactions'][$transactionName] ?? null;
    }
    
    /**
     * Get extra fee by name
     */
    public function getExtraFee($extraName) {
        $schedule = $this->loadSchedule();
        
        foreach ($schedule['extras'] as $extra) {
            if ($extra['name'] === $extraName) {
                return $extra['fee'];
            }
        }
        
        return 0;
    }
}

