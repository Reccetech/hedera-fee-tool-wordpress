<?php

namespace HederaFeeCalculator\WordPress\REST_API;

use HederaFeeCalculator\Core\FeeCalculator;
use HederaFeeCalculator\Core\FeeScheduleLoader;
use HederaFeeCalculator\Core\IncludedDefaultsHelper;
use HederaFeeCalculator\Validators\ParameterValidator;

/**
 * WordPress REST API Controller
 */
class REST_Controller {
    private $calculator;
    private $validator;
    private $namespace = 'hedera-fees/v1'; // WordPress REST API namespace
    // Note: Frontend should use /wp-json/hedera-fees/v1/ for WordPress
    // or /api/v1/ for standalone test UI
    
    public function __construct() {
        $loader = FeeScheduleLoader::getInstance();
        $this->calculator = new FeeCalculator($loader);
        $this->validator = new ParameterValidator($loader);
        
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    public function register_routes() {
        // GET /transactions - Get all services and transactions
        register_rest_route($this->namespace, '/transactions', [
            'methods' => 'GET',
            'callback' => [$this, 'get_transactions'],
            'permission_callback' => '__return_true'
        ]);
        
        // GET /transactions/{type}/description - Get transaction description
        register_rest_route($this->namespace, '/transactions/(?P<type>[a-zA-Z0-9]+)/description', [
            'methods' => 'GET',
            'callback' => [$this, 'get_description'],
            'permission_callback' => '__return_true',
            'args' => [
                'type' => [
                    'required' => true,
                    'type' => 'string'
                ]
            ]
        ]);
        
        // GET /transactions/{type}/parameters - Get transaction parameters
        register_rest_route($this->namespace, '/transactions/(?P<type>[a-zA-Z0-9]+)/parameters', [
            'methods' => 'GET',
            'callback' => [$this, 'get_parameters'],
            'permission_callback' => '__return_true',
            'args' => [
                'type' => [
                    'required' => true,
                    'type' => 'string'
                ]
            ]
        ]);
        
        // POST /transactions/{type}/check - Validate parameters
        register_rest_route($this->namespace, '/transactions/(?P<type>[a-zA-Z0-9]+)/check', [
            'methods' => 'POST',
            'callback' => [$this, 'check_parameters'],
            'permission_callback' => '__return_true',
            'args' => [
                'type' => [
                    'required' => true,
                    'type' => 'string'
                ]
            ]
        ]);
        
        // POST /transactions/{type}/fee - Calculate fee
        register_rest_route($this->namespace, '/transactions/(?P<type>[a-zA-Z0-9]+)/fee', [
            'methods' => 'POST',
            'callback' => [$this, 'compute_fee'],
            'permission_callback' => '__return_true',
            'args' => [
                'type' => [
                    'required' => true,
                    'type' => 'string'
                ]
            ]
        ]);
        
        // GET /transactions/{type}/included-defaults - Get included defaults
        register_rest_route($this->namespace, '/transactions/(?P<type>[a-zA-Z0-9]+)/included-defaults', [
            'methods' => 'GET',
            'callback' => [$this, 'get_included_defaults'],
            'permission_callback' => '__return_true',
            'args' => [
                'type' => [
                    'required' => true,
                    'type' => 'string'
                ]
            ]
        ]);
    }
    
    public function get_transactions($request) {
        try {
            $services = $this->calculator->getServices();
            return rest_ensure_response($services);
        } catch (\Exception $e) {
            return new \WP_Error('fee_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public function get_description($request) {
        $type = $request->get_param('type');
        
        try {
            $description = $this->calculator->getDescription($type);
            if (empty($description)) {
                return new \WP_Error('not_found', 'Transaction not found', ['status' => 404]);
            }
            return rest_ensure_response($description);
        } catch (\Exception $e) {
            return new \WP_Error('fee_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public function get_parameters($request) {
        $type = $request->get_param('type');
        
        try {
            $parameters = $this->calculator->getParameters($type);
            if (empty($parameters)) {
                return new \WP_Error('not_found', 'Transaction not found', ['status' => 404]);
            }
            return rest_ensure_response($parameters);
        } catch (\Exception $e) {
            return new \WP_Error('fee_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public function check_parameters($request) {
        $type = $request->get_param('type');
        $parameters = $request->get_json_params();
        
        try {
            $result = $this->validator->validate($type, $parameters);
            return rest_ensure_response($result);
        } catch (\Exception $e) {
            return new \WP_Error('fee_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public function compute_fee($request) {
        $type = $request->get_param('type');
        $parameters = $request->get_json_params();
        
        try {
            // Validate first
            $validation = $this->validator->validate($type, $parameters);
            if (!$validation['result']) {
                return new \WP_Error('validation_error', $validation['message'], ['status' => 400]);
            }
            
            // Calculate fee
            $feeResult = $this->calculator->calculateFee($type, $parameters);
            return rest_ensure_response($feeResult->toJSON());
        } catch (\Exception $e) {
            return new \WP_Error('fee_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public function get_included_defaults($request) {
        $type = $request->get_param('type');
        
        try {
            $loader = FeeScheduleLoader::getInstance();
            $defaults = IncludedDefaultsHelper::getIncludedDefaults($type, $loader);
            return rest_ensure_response($defaults);
        } catch (\Exception $e) {
            return new \WP_Error('fee_error', $e->getMessage(), ['status' => 500]);
        }
    }
}

