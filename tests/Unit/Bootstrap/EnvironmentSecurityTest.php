<?php

declare(strict_types=1);

namespace CApp\Tests\Unit\Bootstrap;

use CApp\Bootstrap\Environment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EnvironmentSecurityTest extends TestCase
{
    /** @return iterable<string, array{string, string, string, string}> */
    public static function invalidConfiguration(): iterable
    {
        yield 'short application key' => ['test', 'http://localhost', 'short', 'APP_KEY'];
        yield 'production must use https' => ['production', 'http://capp.example', str_repeat('k', 32), 'HTTPS'];
        yield 'invalid URL' => ['test', 'not-a-url', str_repeat('k', 32), 'APP_URL'];
    }

    #[DataProvider('invalidConfiguration')]
    public function testRejectsUnsafeApplicationConfiguration(
        string $environment,
        string $url,
        string $key,
        string $message,
    ): void {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        Environment::validateSecurity($environment, $url, $key);
    }

    public function testAcceptsHttpsProductionConfiguration(): void
    {
        Environment::validateSecurity('production', 'https://capp.example', str_repeat('k', 32));

        self::addToAssertionCount(1);
    }

    public function testSecurityConfigurationRequiresApplicationAndSessionValues(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SESSION_NAME');

        Environment::securityConfiguration([
            'APP_ENV' => 'test',
            'APP_URL' => 'http://localhost:8080',
            'APP_KEY' => str_repeat('k', 32),
            'APP_ALLOWED_ORIGINS' => 'http://localhost:8080',
        ]);
    }

    public function testProductionRejectsWildcardAllowedOrigin(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_ALLOWED_ORIGINS');

        Environment::securityConfiguration([
            'APP_ENV' => 'production',
            'APP_URL' => 'https://capp.example',
            'APP_KEY' => str_repeat('k', 32),
            'APP_ALLOWED_ORIGINS' => '*',
            'SESSION_NAME' => 'capp_session',
        ]);
    }

    public function testNormalizesEnvironmentBeforeApplyingProductionRules(): void
    {
        $configuration = Environment::securityConfiguration([
            'APP_ENV' => ' PrOdUcTiOn ',
            'APP_URL' => 'https://capp.example',
            'APP_KEY' => str_repeat('k', 32),
            'APP_ALLOWED_ORIGINS' => 'https://client.example',
            'SESSION_NAME' => 'capp_session',
        ]);

        self::assertSame('production', $configuration['APP_ENV']);
    }

    public function testWhitespaceProductionCannotBypassHttpsRequirement(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTPS');

        Environment::validateSecurity(' production ', 'http://capp.example', str_repeat('k', 32));
    }

    #[DataProvider('unknownEnvironments')]
    public function testRejectsUnknownEnvironment(string $environment): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_ENV');

        Environment::validateSecurity($environment, 'https://capp.example', str_repeat('k', 32));
    }

    /** @return iterable<string, array{string}> */
    public static function unknownEnvironments(): iterable
    {
        yield 'prod alias' => ['prod'];
        yield 'staging unsupported' => ['staging'];
        yield 'unknown' => ['sandbox'];
    }

    public function testDevelopmentAllowsHttpWithExplicitOrigin(): void
    {
        $configuration = Environment::securityConfiguration([
            'APP_ENV' => 'development',
            'APP_URL' => 'http://localhost:8080',
            'APP_KEY' => str_repeat('k', 32),
            'APP_ALLOWED_ORIGINS' => 'http://localhost:8080',
            'SESSION_NAME' => 'capp_session',
        ]);

        self::assertSame('development', $configuration['APP_ENV']);
    }
}
