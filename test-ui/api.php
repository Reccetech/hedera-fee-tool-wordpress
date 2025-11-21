<?php
/**
 * Standalone REST API for test UI (no WordPress)
 */

require_once __DIR__ . '/bootstrap.php';

use HederaFeeCalculator\Core\FeeCalculator;
use HederaFeeCalculator\Core\FeeScheduleLoader;
use HederaFeeCalculator\Core\IncludedDefaultsHelper;
use HederaFeeCalculator\Validators\ParameterValidator;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$loader = FeeScheduleLoader::getInstance();
$calculator = new FeeCalculator($loader);
$validator = new ParameterValidator($loader);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string and base path
$path = str_replace('/test-ui/api.php', '', $path);
$path = preg_replace('/\?.*$/', '', $path);

try {
    // GET /api/v1/transactions
    if ($path === '/api/v1/transactions' && $method === 'GET') {
        $services = $calculator->getServices();
        echo json_encode($services);
        exit;
    }
    
    // GET /api/v1/transactions/{type}/description
    if (preg_match('#^/api/v1/transactions/([^/]+)/description$#', $path, $matches) && $method === 'GET') {
        $type = $matches[1];
        $description = $calculator->getDescription($type);
        if (empty($description)) {
            http_response_code(404);
            echo json_encode(['error' => 'Transaction not found']);
        } else {
            echo json_encode($description);
        }
        exit;
    }
    
    // GET /api/v1/transactions/{type}/parameters
    if (preg_match('#^/api/v1/transactions/([^/]+)/parameters$#', $path, $matches) && $method === 'GET') {
        $type = $matches[1];
        $parameters = $calculator->getParameters($type);
        if (empty($parameters)) {
            http_response_code(404);
            echo json_encode(['error' => 'Transaction not found']);
        } else {
            echo json_encode($parameters);
        }
        exit;
    }
    
    // POST /api/v1/transactions/{type}/check
    if (preg_match('#^/api/v1/transactions/([^/]+)/check$#', $path, $matches) && $method === 'POST') {
        $type = $matches[1];
        $parameters = json_decode(file_get_contents('php://input'), true);
        $result = $validator->validate($type, $parameters);
        echo json_encode($result);
        exit;
    }
    
    // POST /api/v1/transactions/{type}/fee
    if (preg_match('#^/api/v1/transactions/([^/]+)/fee$#', $path, $matches) && $method === 'POST') {
        $type = $matches[1];
        $parameters = json_decode(file_get_contents('php://input'), true);
        
        // Validate first
        $validation = $validator->validate($type, $parameters);
        if (!$validation['result']) {
            http_response_code(400);
            echo json_encode(['error' => $validation['message']]);
            exit;
        }
        
        // Calculate fee
        $feeResult = $calculator->calculateFee($type, $parameters);
        echo json_encode($feeResult->toJSON());
        exit;
    }
    
    // GET /api/v1/transactions/{type}/included-defaults
    if (preg_match('#^/api/v1/transactions/([^/]+)/included-defaults$#', $path, $matches) && $method === 'GET') {
        $type = $matches[1];
        $defaults = \HederaFeeCalculator\Core\IncludedDefaultsHelper::getIncludedDefaults($type, $loader);
        echo json_encode($defaults);
        exit;
    }
    
    // 404
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

