<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [$method, $pattern, $handler];
    }

    public function dispatch(Request $request): void
    {
        $path = $request->path === '' ? '/' : $request->path;

        foreach ($this->routes as [$method, $pattern, $handler]) {
            $requestMethod = $request->method === 'HEAD' ? 'GET' : $request->method;
            if ($method !== $requestMethod) {
                continue;
            }
            $params = $this->match($pattern, $path);
            if ($params !== null) {
                $handler($request, ...$params);
                return;
            }
        }

        Response::notFound();
    }

    /** @return array<int, string>|null */
    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches)) {
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[] = $value;
                }
            }
            return $params;
        }

        return null;
    }
}
