<?php

session_start();

require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../routes/web.php';

use App\Core\Router;

$router = new Router();

// Load routes
require_once __DIR__ . '/../routes/web.php';

// Dispatch the request
$router->dispatch(); 