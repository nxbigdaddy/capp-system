<?php

declare(strict_types=1);

namespace CApp\Tests\Unit\Http;

use CApp\Http\Request;
use CApp\Http\Router;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    /** @return iterable<string, array{string, string, string, array<string, string>}> */
    public static function routes(): iterable
    {
        yield 'list' => ['GET', '/api/v1/process-routes', 'legacy.process-routes.index', []];
        yield 'create' => ['POST', '/api/v1/process-routes', 'legacy.process-routes.store', []];
        yield 'show' => ['GET', '/api/v1/process-routes/42', 'legacy.process-routes.show', ['id' => '42']];
        yield 'update' => ['PUT', '/api/v1/process-routes/42', 'legacy.process-routes.update', ['id' => '42']];
        yield 'delete' => ['DELETE', '/api/v1/process-routes/42', 'legacy.process-routes.destroy', ['id' => '42']];
        yield 'approve' => ['POST', '/api/v1/process-routes/42/approve', 'legacy.process-routes.approve', ['id' => '42']];
        yield 'mobile without slash' => ['GET', '/mobile', 'legacy.mobile.home', []];
        yield 'mobile with slash' => ['GET', '/mobile/', 'legacy.mobile.home', []];
    }

    /** @param array<string, string> $params */
    #[DataProvider('routes')]
    public function testResolvesApplicationRoutes(string $method, string $uri, string $handler, array $params): void
    {
        $result = Router::application()->dispatch(Request::create($method, $uri));

        self::assertTrue($result->isFound());
        self::assertSame($handler, $result->handler());
        self::assertSame($params, $result->params());
    }

    public function testReturnsNotFoundForUnknownPath(): void
    {
        $result = Router::application()->dispatch(Request::create('GET', '/api/v1/unknown'));

        self::assertSame(404, $result->status());
        self::assertFalse($result->isFound());
    }

    public function testReturnsMethodNotAllowedWithAllowedMethods(): void
    {
        $result = Router::application()->dispatch(Request::create('PATCH', '/api/v1/process-routes/42'));

        self::assertSame(405, $result->status());
        self::assertEqualsCanonicalizing(['GET', 'PUT', 'DELETE'], $result->allowedMethods());
    }
}
