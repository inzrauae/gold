<?php

namespace App\Core;

class Request
{
    public string $method;
    public string $path;
    public array $query;
    public array $body;
    public array $server;
    public array $headers;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->path = rtrim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');
        if ($this->path === '') {
            $this->path = '/';
        }
        $this->query = $_GET;
        $this->server = $_SERVER;
        $this->headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];

        if ($this->method === 'POST') {
            $contentType = $this->server['CONTENT_TYPE'] ?? '';
            if (str_contains($contentType, 'application/json')) {
                $raw = file_get_contents('php://input');
                $this->body = json_decode($raw, true) ?: [];
            } else {
                $this->body = $_POST;
            }
        } else {
            $this->body = [];
        }
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }
        return null;
    }

    public function isSecure(): bool
    {
        if (Env::bool('TRUST_PROXY_HEADERS') && ($this->server['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
            return true;
        }
        return ($this->server['HTTPS'] ?? '') === 'on' || ($this->server['SERVER_PORT'] ?? '') === '443';
    }

    public function ip(): string
    {
        if (Env::bool('TRUST_PROXY_HEADERS') && !empty($this->server['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $this->server['HTTP_X_FORWARDED_FOR']);
            return trim($parts[0]);
        }
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
