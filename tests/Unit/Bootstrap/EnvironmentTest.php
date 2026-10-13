<?php

declare(strict_types=1);

namespace CApp\Tests\Unit\Bootstrap;

use CApp\Bootstrap\Environment;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EnvironmentTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/capp-env-' . bin2hex(random_bytes(8));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        foreach (['CAPP_TEST_VALUE', 'CAPP_REQUIRED_VALUE'] as $name) {
            unset($_ENV[$name], $_SERVER[$name]);
            putenv($name);
        }

        if (is_file($this->directory . '/.env')) {
            unlink($this->directory . '/.env');
        }
        rmdir($this->directory);
    }

    public function testLoadsValuesFromDotEnvFile(): void
    {
        file_put_contents($this->directory . '/.env', "CAPP_TEST_VALUE=loaded\n");

        Environment::load($this->directory, ['CAPP_TEST_VALUE']);

        self::assertSame('loaded', Environment::get('CAPP_TEST_VALUE'));
    }

    public function testRejectsMissingRequiredValues(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CAPP_REQUIRED_VALUE');

        Environment::load($this->directory, ['CAPP_REQUIRED_VALUE']);
    }
}
