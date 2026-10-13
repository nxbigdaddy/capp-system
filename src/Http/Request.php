<?php

declare(strict_types=1);

namespace CApp\Http;

final class Request
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $query
     */
    private function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $headers,
        private readonly array $query,
        private readonly string $body,
    ) {
    }

    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $query
     */
    public static function create(
        string $method,
        string $uri,
        array $headers = [],
        array $query = [],
        string $body = '',
    ): self {
        $path = parse_url($uri, PHP_URL_PATH);

        return new self(strtoupper($method), is_string($path) ? $path : '/', $headers, $query, $body);
    }

    /** @param array<string, mixed> $server */
    public static function fromGlobals(array $server = [], ?string $body = null): self
    {
        $server = $server === [] ? $_SERVER : $server;
        $headers = [];
        foreach ($server as $name => $value) {
            if (str_starts_with($name, 'HTTP_') && is_string($value)) {
                $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$header] = $value;
            }
        }

        foreach (['CONTENT_TYPE' => 'Content-Type', 'CONTENT_LENGTH' => 'Content-Length'] as $key => $header) {
            if (isset($server[$key]) && is_string($server[$key])) {
                $headers[$header] = $server[$key];
            }
        }

        foreach (['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION', 'AUTHORIZATION'] as $key) {
            if (isset($server[$key]) && is_string($server[$key]) && $server[$key] !== '') {
                $headers['Authorization'] = $server[$key];
                break;
            }
        }

        return self::create(
            (string) ($server['REQUEST_METHOD'] ?? 'GET'),
            (string) ($server['REQUEST_URI'] ?? '/'),
            $headers,
            $_GET,
            $body ?? (string) file_get_contents('php://input'),
        );
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $header => $value) {
            if (strcasecmp($header, $name) === 0) {
                return $value;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    public function query(): array
    {
        return $this->query;
    }

    public function body(): string
    {
        return $this->body;
    }

    /** @return array<string, mixed> */
    public function json(): array
    {
        if (trim($this->body) === '') {
            throw new BadRequestException('JSON body must not be empty');
        }

        try {
            $object = json_decode($this->body, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new BadRequestException('Invalid JSON', previous: $exception);
        }

        if (!$object instanceof \stdClass) {
            throw new BadRequestException('JSON body must be an object');
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);

        return $data;
    }
}
