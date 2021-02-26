<?php

require __DIR__ .'/vendor/autoload.php';

use app\base\Engine;

$engine = new Engine();

$engine->database->applyMigrations();
