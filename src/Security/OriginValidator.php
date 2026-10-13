<?php

declare(strict_types=1);

namespace CApp\Security;

use RuntimeException;

final class OriginValidator
{
    public static function validate(string $origin, string $environment): string
    {
        $parts = parse_url($origin);
        if ($parts === false
            || !isset($parts['scheme'], $parts['host'])
            || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
            || isset($parts['user'], $parts['pass'], $parts['query'], $parts['fragment'])
            || (isset($parts['path']) && $parts['path'] !== '')
        ) {
            throw new RuntimeException('CORS origin must be an HTTP or HTTPS origin without path, userinfo, query, or fragment.');
        }

        $scheme = strtolower($parts['scheme']);
        if ($environment === 'production' && $scheme !== 'https') {
            throw new RuntimeException('Production CORS origins must use HTTPS.');
        }

        $normalized = $scheme . '://' . strtolower($parts['host']);
        if (isset($parts['port'])) {
            $normalized .= ':' . $parts['port'];
        }

        if ($normalized !== strtolower($origin)) {
            throw new RuntimeException('CORS origin must contain only scheme, host, and optional port.');
        }

        return $normalized;
    }
}
