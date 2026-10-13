<?php

declare(strict_types=1);

namespace CApp\Tests\Unit\Http;

use CApp\Http\CorsPolicy;
use CApp\Http\Request;
use CApp\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CorsPolicyTest extends TestCase
{
    public function testRejectsWildcardConfiguration(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('wildcard');

        CorsPolicy::fromCommaSeparated('*', 'test');
    }

    /** @return iterable<string, array{string}> */
    public static function invalidOrigins(): iterable
    {
        yield 'path' => ['https://client.example/path'];
        yield 'userinfo' => ['https://user@client.example'];
        yield 'query' => ['https://client.example?x=1'];
        yield 'fragment' => ['https://client.example#fragment'];
        yield 'unsupported scheme' => ['ftp://client.example'];
        yield 'missing host' => ['https://'];
        yield 'opaque null' => ['null'];
    }

    #[DataProvider('invalidOrigins')]
    public function testRejectsInvalidConfiguredOrigin(string $origin): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('origin');

        CorsPolicy::fromCommaSeparated($origin, 'test');
    }

    public function testProductionRejectsHttpOrigin(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTPS');

        CorsPolicy::fromCommaSeparated('http://client.example', 'production');
    }

    public function testTestEnvironmentAllowsExplicitHttpOrigin(): void
    {
        $policy = CorsPolicy::fromCommaSeparated('http://localhost:8080', 'test');

        self::assertTrue($policy->isOriginAllowed('http://localhost:8080'));
    }

    public function testRejectedOriginStillAddsAndMergesVaryOrigin(): void
    {
        $policy = CorsPolicy::fromCommaSeparated('https://allowed.example', 'production');
        $request = Request::create('GET', '/', ['Origin' => 'https://blocked.example']);
        $response = new Response(headers: ['Vary' => 'Accept-Encoding']);

        $result = $policy->apply($request, $response);

        self::assertSame('Accept-Encoding, Origin', $result->headers()['Vary']);
        self::assertArrayNotHasKey('Access-Control-Allow-Origin', $result->headers());
    }

    public function testRequestOriginWithPathIsNeverAllowed(): void
    {
        $policy = CorsPolicy::fromCommaSeparated('https://allowed.example', 'production');

        self::assertFalse($policy->isOriginAllowed('https://allowed.example/path'));
        self::assertFalse($policy->isOriginAllowed(null));
    }
}
