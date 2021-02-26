<?php

namespace app\controllers;

use app\base\Engine;

class Controller
{
    public function json(array $data, int $status)
    {
        return Engine::$engine->response->json($data, $status);
    }
}
