<?php

declare(strict_types=1);

namespace CApp\Bootstrap;

use Dotenv\Dotenv;
use RuntimeException;

final class Environment
{
    /** @param list<string> $required */
    public static function load(string $directory, array $required = []): void
    {
        if (is_file(rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . '.env')) {
            Dotenv::createImmutable($directory)->safeLoad();
        }

        foreach ($required as $name) {
            $value = self::get($name);
            if ($value === null || trim($value) === '') {
                throw new RuntimeException(sprintf('Required environment variable is missing: %s', $name));
            }
        }
    }

    public static function get(string $name, ?string $default = null): ?string
    {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);

        return $value === false ? $default : (string) $value;
    }

    /**
     * @param array<string, string|null> $values
     * @return array{APP_ENV: string, APP_URL: string, APP_KEY: string, APP_ALLOWED_ORIGINS: string, SESSION_NAME: string}
     */
    public static function securityConfiguration(array $values): array
    {
        $required = ['APP_ENV', 'APP_URL', 'APP_KEY', 'APP_ALLOWED_ORIGINS', 'SESSION_NAME'];
        foreach ($required as $name) {
            if (!isset($values[$name]) || trim((string) $values[$name]) === '') {
                throw new RuntimeException(sprintf('Required security configuration is missing: %s', $name));
            }
        }

        $configuration = [
            'APP_ENV' => (string) $values['APP_ENV'],
            'APP_URL' => (string) $values['APP_URL'],
            'APP_KEY' => (string) $values['APP_KEY'],
            'APP_ALLOWED_ORIGINS' => (string) $values['APP_ALLOWED_ORIGINS'],
            'SESSION_NAME' => (string) $values['SESSION_NAME'],
        ];

        self::validateSecurity(
            $configuration['APP_ENV'],
            $configuration['APP_URL'],
            $configuration['APP_KEY'],
        );

        $origins = array_values(array_filter(array_map('trim', explode(',', $configuration['APP_ALLOWED_ORIGINS']))));
        if ($origins === [] || in_array('*', $origins, true)) {
            throw new RuntimeException('APP_ALLOWED_ORIGINS must contain explicit origins and may not contain a wildcard.');
        }

        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]{0,127}$/', $configuration['SESSION_NAME'])) {
            throw new RuntimeException('SESSION_NAME contains invalid characters.');
        }

        return $configuration;
    }

    public static function validateSecurity(string $environment, string $url, string $applicationKey): void
    {
        $parts = parse_url($url);
        if ($parts === false
            || !isset($parts['scheme'], $parts['host'])
            || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
        ) {
            throw new RuntimeException('APP_URL must be a valid HTTP or HTTPS URL.');
        }

        if (strtolower($environment) === 'production' && strtolower($parts['scheme']) !== 'https') {
            throw new RuntimeException('Production APP_URL must use HTTPS.');
        }

        if (strlen($applicationKey) < 32) {
            throw new RuntimeException('APP_KEY must contain at least 32 characters.');
        }
    }
}
