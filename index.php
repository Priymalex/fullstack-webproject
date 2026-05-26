<?php
// index.php — Единая точка входа

session_start();

include('./settings.php');
require_once __DIR__ . '/scripts/init.php';
require_once __DIR__ . '/modules/front.php';

$realMethod = $_SERVER['REQUEST_METHOD'];
$rawInput = file_get_contents('php://input'); // сырой json содержимое тела запроса
$parsedInput = [];

if (!empty($rawInput)) {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $parsedInput = json_decode($rawInput, true) ?? [];
    } else {
        parse_str($rawInput, $parsedInput);
    }
}

$request = [
    'get'    => $_GET,
    'post'   => $_POST,
    'put'    => ($realMethod === 'PUT') ? $parsedInput : [],
    'method' => $realMethod,
];

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // достём url 

$pathParts = explode('fullstack-webproject', $requestUri); // отрезаем от него fullstack
$path = end($pathParts); // берем хвост

// Очищаем от слэшей. Главная страница станет пустой строкой ''
$path = trim($path, '/');

// --- Маршруты ---

if ($path === '' || $path === 'index.php') {
    $response = front_get($request);
}
elseif ((strpos($path, 'api/users') !== false || $path === 'form-fallback') && $realMethod === 'POST') {
    $response = front_post($request);
}
elseif (preg_match('#api/users/(\d+)#', $path, $matches) && $realMethod === 'PUT') {
    $request['user_id'] = (int)$matches[1];
    $response = front_put($request);
}
elseif (strpos($path, 'login.php') !== false || strpos($path, 'modules/login.php') !== false) {
    require __DIR__ . '/modules/login.php';
    exit;
}
elseif (strpos($path, 'logout.php') !== false || strpos($path, 'modules/logout.php') !== false) {
    require __DIR__ . '/modules/logout.php';
    exit;
}
elseif (strpos($path, 'admin.php') !== false || strpos($path, 'modules/admin.php') !== false) {
    require __DIR__ . '/modules/admin.php';
    exit;
}
elseif (strpos($path, 'edit.php') !== false || strpos($path, 'modules/edit.php') !== false) {
    require __DIR__ . '/modules/edit.php';
    exit;
}
else {
    http_response_code(404);
    $response = [
        'headers' => ['Content-Type' => 'application/json'], 
        'entity' => json_encode([
            'status' => 'error', 
            'message' => 'Маршрут не найден',
            'debug_path' => $path
        ])
    ];
}

if (isset($response['headers'])) {
    foreach ($response['headers'] as $key => $value) {
        header(is_string($key) ? "$key: $value" : $value);
    }
}
if (isset($response['entity'])) {
    print $response['entity'];
}
?>