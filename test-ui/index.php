<?php
/**
 * Simple router for test UI
 * Routes API requests to api.php, everything else to React UI
 */

$request = $_SERVER['REQUEST_URI'];
$requestPath = parse_url($request, PHP_URL_PATH);

// Remove base path if running in subdirectory
$basePath = '/test-ui';
if (strpos($requestPath, $basePath) === 0) {
    $requestPath = substr($requestPath, strlen($basePath));
}

// Route to API
if (strpos($requestPath, '/api/') === 0) {
    require __DIR__ . '/api.php';
    exit;
}

// For development: redirect to React dev server if running
// For production: serve built files from ui/ directory
// For now, show instructions
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hedera Fee Calculator - Test UI</title>
    <style>
        body {
            margin: 0;
            padding: 40px;
            font-family: Arial, sans-serif;
            background: #1c1c1c;
            color: white;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .info {
            background: #2a2a2a;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info h1 {
            margin-top: 0;
            color: #60a5fa;
        }
        .info code {
            background: #1c1c1c;
            padding: 2px 6px;
            border-radius: 4px;
            color: #60a5fa;
        }
        .steps {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .steps ol {
            line-height: 2;
        }
        .test-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .test-link:hover {
            background: #4338ca;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="info">
            <h1>Hedera Fee Calculator - Test UI</h1>
            <p>To use the React UI, you have two options:</p>
            
            <div class="steps">
                <h2>Option 1: React Development Server (Recommended)</h2>
                <ol>
                    <li>Open Terminal 1: <code>cd test-ui</code> then <code>php -S localhost:8000</code></li>
                    <li>Open Terminal 2: <code>cd test-ui/react-ui</code> then <code>npm install</code> then <code>npm run dev</code></li>
                    <li>Open <a href="http://localhost:3000" style="color: #60a5fa;">http://localhost:3000</a> in your browser</li>
                </ol>
                
                <h2>Option 2: Build and Serve</h2>
                <ol>
                    <li>Build React: <code>cd test-ui/react-ui</code> then <code>npm install</code> then <code>npm run build</code></li>
                    <li>Copy build files: <code>Copy-Item dist\* ..\ui\ -Recurse -Force</code></li>
                    <li>Start PHP server: <code>cd test-ui</code> then <code>php -S localhost:8000</code></li>
                    <li>Open <a href="http://localhost:8000" style="color: #60a5fa;">http://localhost:8000</a> in your browser</li>
                </ol>
            </div>
            
            <p><strong>API Endpoint:</strong> <code>http://localhost:8000/api/v1/transactions</code></p>
            <a href="/test-ui/api.php/api/v1/transactions" class="test-link">Test API Endpoint</a>
        </div>
    </div>
</body>
</html>

