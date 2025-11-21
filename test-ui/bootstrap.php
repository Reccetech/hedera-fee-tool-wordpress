<?php
/**
 * Bootstrap file for standalone test UI
 * Loads all core classes without WordPress
 */

// Set up paths
$baseDir = dirname(__DIR__);
$coreDir = $baseDir . '/core';
$includesDir = $baseDir . '/includes';

// Autoloader
spl_autoload_register(function ($class) {
    $prefixes = [
        'HederaFeeCalculator\\Core\\' => dirname(__DIR__) . '/core/',
        'HederaFeeCalculator\\Calculators\\' => dirname(__DIR__) . '/includes/Calculators/',
        'HederaFeeCalculator\\Validators\\' => dirname(__DIR__) . '/includes/Validators/',
    ];
    
    foreach ($prefixes as $prefix => $base_dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }
        
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        if (file_exists($file)) {
            require $file;
            break;
        }
    }
});

// Initialize loader with data file paths
$schedulePath = $baseDir . '/data/simpleFeesSchedules.json';
$metadataPath = $baseDir . '/data/transactionMetadata.json';

\HederaFeeCalculator\Core\FeeScheduleLoader::getInstance($schedulePath, $metadataPath);

