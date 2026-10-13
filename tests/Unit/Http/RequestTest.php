<?php

declare(strict_types=1);

namespace CApp\Tests\Unit\Http;

use CApp\Http\BadRequestException;
use CApp\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    protected function tearDown(): void
    {
        $_GET = [];
    }

    public function testReadsStandardCgiHeaders(): void
    {
        $request = Request::fromGlobals([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/api/v1/auth/login',
            'CONTENT_TYPE' => 'application/json',
            'CONTENT_LENGTH' => '19',
            'AUTHORIZATION' => 'Bearer token',
            'HTTP_X_CSRF_TOKEN' => 'csrf-token',
        ], '{"username":"a"}');

        self::assertSame('application/json', $request->header('Content-Type'));
        self::assertSame('19', $request->header('Content-Length'));
        self::assertSame('Bearer token', $request->header('Authorization'));
        self::assertSame('csrf-token', $request->header('X-Csrf-Token'));
    }

    public function testReadsAuthorizationFromRedirectFallback(): void
    {
        $request = Request::fromGlobals([
            'REDIRECT_HTTP_AUTHORIZATION' => 'Bearer redirected',
        ], '');

        self::assertSame('Bearer redirected', $request->header('Authorization'));
    }

    public function testDirectAuthorizationHeaderTakesPrecedenceOverRedirectFallback(): void
    {
        $request = Request::fromGlobals([
            'HTTP_AUTHORIZATION' => 'Bearer direct',
            'REDIRECT_HTTP_AUTHORIZATION' => 'Bearer redirected',
        ], '');

        self::assertSame('Bearer direct', $request->header('Authorization'));
    }

    public function testParsesJsonObject(): void
    {
        $request = Request::create('POST', '/', ['Content-Type' => 'application/json'], body: '{"username":"alice"}');

        self::assertSame(['username' => 'alice'], $request->json());
    }

    public function testAcceptsEmptyJsonObject(): void
    {
        self::assertSame([], Request::create('POST', '/', body: '{}')->json());
    }

    /** @return iterable<string, array{string}> */
    public static function invalidJsonBodies(): iterable
    {
        yield 'empty body' => [''];
        yield 'whitespace body' => [" \n\t"];
        yield 'null' => ['null'];
        yield 'string scalar' => ['"scalar"'];
        yield 'number scalar' => ['42'];
        yield 'boolean scalar' => ['true'];
        yield 'list' => ['[]'];
        yield 'non-empty list' => ['[1]'];
        yield 'malformed json' => ['{invalid'];
    }

    #[DataProvider('invalidJsonBodies')]
    public function testRejectsNonObjectJsonAsBadRequest(string $body): void
    {
        $this->expectException(BadRequestException::class);

        Request::create('POST', '/', body: $body)->json();
    }
}
