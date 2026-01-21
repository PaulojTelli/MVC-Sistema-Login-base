<?php

namespace core;

class Router
{
    private $controller = 'Inicio';
    private $method = 'index';
    private $param = [];

    public function __construct()
    {
        $router = $this->url();

        if (!empty($router[0]) && file_exists(__DIR__ . '/../app/controllers/' . ucfirst($router[0]) . '.php')) {
            $this->controller = ucfirst($router[0]);
            unset($router[0]);
        }

        $class = "\\app\\controllers\\" . $this->controller;

        if (class_exists($class)) {
            $object = new $class;

            if (isset($router[1]) && method_exists($object, $router[1])) {
                $this->method = $router[1];
                unset($router[1]);
            }

            $this->param = $router ? array_values($router) : [];

            call_user_func_array([$object, $this->method], $this->param);
        } else {
            echo "Controller not found!";
        }
    }

    private function url(): array
    {
        $router = filter_input(INPUT_GET, 'router', FILTER_SANITIZE_URL) ?? '';
        $router = trim($router, '/');

        if ($router === '') {
            return [];
        }

        return explode('/', $router);
    }
}
