<?php
require '../vendor/autoload.php';

use app\base\Engine;

use app\controllers\Api\V1\PhoneBookController;

$engine = new Engine();

$engine->router->get('/get-all', [PhoneBookController::class, 'getAll']);
$engine->router->get('/get', [PhoneBookController::class, 'get']);
$engine->router->get('/search', [PhoneBookController::class, 'search']);
$engine->router->post('/store', [PhoneBookController::class, 'store']);
$engine->router->post('/update', [PhoneBookController::class, 'update']);
$engine->router->delete('/delete', [PhoneBookController::class, 'delete']);


$engine->run();