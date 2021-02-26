<?php

namespace app\base;

/**
 * Class Engine
 * @package app\base
 */
class Engine
{
    public Router $router;
    public Request $request;
    public Response $response;
    public DataBase $database;
    public static Engine $engine;
    public static string $ROOT_DIR;

    /**
     * Engine constructor.
     */
    public function __construct()
    {
        Logger::enableSystemLogs();
        self::$ROOT_DIR = dirname(__DIR__);
        self::$engine = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->database = new DataBase();

    }

    /**
     *
     */
    public function run(): void
    {
        $this->router->resolve();
    }
}