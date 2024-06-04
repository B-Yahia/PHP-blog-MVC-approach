<?php

namespace Framework;

class Router
{
    protected $routes = [];
    protected $middlewares = [];

    function add(string $method, string $path, array $controller)
    {
        $path = $this->normalisePath($path);
        $this->routes[] = [
            "path" => $path,
            "method" => strtoupper($method),
            "controller" => $controller,
        ];
    }

    function normalisePath(string $path)
    {
        $path = trim($path, "/");
        $path = "/" . $path . "/";
        $path = preg_replace('#[/]{2,}#', '/', $path);

        return $path;
    }

    public function dispatch(string $path, string $method, Container $container = null)
    {
        $path = $this->normalisePath($path);
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if (
                !preg_match("#^{$route['path']}$#", $path) ||
                $route['method'] !== $method
            ) {
                continue;
            }

            [$class, $function] = $route['controller'];

            $controllerInstance = $container ?
                $container->resolve($class) : new $class;

            $action = fn () => $controllerInstance->{$function}();

            foreach ($this->middlewares as $middleware) {

                $middlewareInstance = $container ?
                    $container->resolve($middleware) :
                    new $middleware;

                $action = fn () => $middlewareInstance->process($action);
            }

            $action();
            return;
        }
    }

    public function addMiddleware(string $middleware)
    {
        $this->middlewares[] = $middleware;
    }
}
