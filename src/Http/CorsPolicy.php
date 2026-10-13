<?php

declare(strict_types=1);

namespace CApp\Http;

use RuntimeException;

final class CorsPolicy
{
    /** @param list<string> $allowedOrigins */
    private function __construct(private readonly array $allowedOrigins)
    {
    }

    public static function fromCommaSeparated(string $origins): self
    {
        $allowedOrigins = array_values(array_filter(array_map('trim', explode(',', $origins))));
        if (in_array('*', $allowedOrigins, true)) {
            throw new RuntimeException('CORS wildcard origins are not allowed.');
        }

        return new self($allowedOrigins);
    }

    public function isOriginAllowed(?string $origin): bool
    {
        return $origin !== null && in_array($origin, $this->allowedOrigins, true);
    }

    /** @return list<string> */
    public function allowedRequestHeaders(string $requestedHeaders): array
    {
        $allowed = [
            'content-type' => 'Content-Type',
            'authorization' => 'Authorization',
            'x-csrf-token' => 'X-CSRF-Token',
        ];
        $headers = array_values(array_filter(array_map('trim', explode(',', $requestedHeaders))));
        $normalized = [];

        foreach ($headers as $header) {
            $name = strtolower($header);
            if (!isset($allowed[$name])) {
                return [];
            }
            $normalized[] = $allowed[$name];
        }

        return $normalized;
    }

    public function apply(Request $request, Response $response): Response
    {
        $headers = [];
        $origin = $request->header('Origin');
        if ($origin !== null && $this->isOriginAllowed($origin)) {
            $headers['Access-Control-Allow-Origin'] = $origin;
            $headers['Vary'] = 'Origin';
        }

        return $response->withHeaders($headers);
    }
}
