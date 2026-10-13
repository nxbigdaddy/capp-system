<?php

declare(strict_types=1);

namespace CApp\Tests\Api;

use CApp\Http\Application;
use PHPUnit\Framework\TestCase;

final class BootstrapTest extends TestCase
{
    protected function tearDown(): void
    {
        foreach (['APP_ENV', 'APP_URL', 'APP_KEY', 'APP_ALLOWED_ORIGINS', 'SESSION_NAME'] as $name) {
            unset($_ENV[$name], $_SERVER[$name]);
            putenv($name);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    public function testBootstrapBuildsApplicationFromEnvironment(): void
    {
        $_ENV['APP_ENV'] = 'test';
        $_ENV['APP_URL'] = 'http://localhost:8080';
        $_ENV['APP_KEY'] = str_repeat('k', 32);
        $_ENV['APP_ALLOWED_ORIGINS'] = 'https://allowed.example';
        $_ENV['SESSION_NAME'] = 'capp_test_session';

        $application = require dirname(__DIR__, 2) . '/src/bootstrap.php';

        self::assertInstanceOf(Application::class, $application);
        self::assertSame('capp_test_session', session_name());
    }
}
