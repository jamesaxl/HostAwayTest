<?php

namespace app\base;

/**
 * Class Router
 * @package app\base
 */
class Router
{
    private array $routes = [];
    public Request $request;
    public Response $response;

    /**
     * Router constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @param string $path
     * @param $callback
     */
    public function get(string $path, $callback) :void
    {
        $this->routes['get'][$path] = $callback;
        $this->routes['get'][$path.'/'] = $callback;
    }

    /**
     * @param string $path
     * @param $callback
     */
    public function post(string $path, $callback) :void
    {
        $this->routes['post'][$path] = $callback;
        $this->routes['post'][$path.'/'] = $callback;
    }

    /**
     * @param string $path
     * @param $callback
     */
    public function delete(string $path, $callback) :void
    {
        $this->routes['delete'][$path] = $callback;
        $this->routes['post'][$path.'/'] = $callback;
    }

    /**
     *
     */
    public function resolve() :void
    {
        $method = $this->request->getMethod();
        $path = $this->request->getPath();

        $callback = $this->routes[$method][$path] ?? null;

        if (null === $callback) {
            echo $this->response->json([
                'error' => 1,
                'errmsg' => 'resource not found'], 404);
            exit;
        }

        $callback[0] = new $callback[0]();

        echo call_user_func($callback, $this->request);
    }
}