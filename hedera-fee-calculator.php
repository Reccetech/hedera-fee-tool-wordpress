<?php
/**
 * Plugin Name: Hedera Fee Calculator
 * Plugin URI: https://github.com/hedera/fee-calculator
 * Description: Calculate fees for Hedera network transactions - easily updatable via JSON files
 * Version: 2.0.0
 * Author: Hedera
 * Author URI: https://hedera.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: hedera-fee-calculator
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('HEDERA_FEE_CALCULATOR_VERSION', '2.0.0');
define('HEDERA_FEE_CALCULATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('HEDERA_FEE_CALCULATOR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader for core classes
spl_autoload_register(function ($class) {
    $prefixes = [
        'HederaFeeCalculator\\Core\\' => HEDERA_FEE_CALCULATOR_PLUGIN_DIR . 'core/',
        'HederaFeeCalculator\\Calculators\\' => HEDERA_FEE_CALCULATOR_PLUGIN_DIR . 'includes/Calculators/',
        'HederaFeeCalculator\\Validators\\' => HEDERA_FEE_CALCULATOR_PLUGIN_DIR . 'includes/Validators/',
        'HederaFeeCalculator\\WordPress\\REST_API\\' => HEDERA_FEE_CALCULATOR_PLUGIN_DIR . 'wordpress/REST_API/',
        'HederaFeeCalculator\\WordPress\\Admin\\' => HEDERA_FEE_CALCULATOR_PLUGIN_DIR . 'wordpress/Admin/',
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

// Initialize plugin
add_action('plugins_loaded', 'hedera_fee_calculator_init');

function hedera_fee_calculator_init() {
    // Set JSON file paths
    $schedulePath = HEDERA_FEE_CALCULATOR_PLUGIN_DIR . 'data/simpleFeesSchedules.json';
    $metadataPath = HEDERA_FEE_CALCULATOR_PLUGIN_DIR . 'data/transactionMetadata.json';
    
    // Initialize loader with paths
    \HederaFeeCalculator\Core\FeeScheduleLoader::getInstance($schedulePath, $metadataPath);
    
    // Load REST API
    if (class_exists('HederaFeeCalculator\\WordPress\\REST_API\\REST_Controller')) {
        new \HederaFeeCalculator\WordPress\REST_API\REST_Controller();
    }
    
    // Load Admin (future)
    // if (class_exists('HederaFeeCalculator\\WordPress\\Admin\\AdminPage')) {
    //     new \HederaFeeCalculator\WordPress\Admin\AdminPage();
    // }
}

// Activation hook
register_activation_hook(__FILE__, 'hedera_fee_calculator_activate');

function hedera_fee_calculator_activate() {
    // Flush rewrite rules to register REST API endpoints
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'hedera_fee_calculator_deactivate');

function hedera_fee_calculator_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

