<?php

declare(strict_types=1);

namespace CApp\Tests\Api;

use CApp\Http\Request;
use CApp\Http\Router;
use PHPUnit\Framework\TestCase;

final class ApplicationRoutingTest extends TestCase
{
    public function testHealthRouteIsAvailableWithoutDatabase(): void
    {
        $result = Router::application()->dispatch(Request::create('GET', '/health'));

        self::assertTrue($result->isFound());
        self::assertSame('system.health', $result->handler());
    }

    public function testUnknownApiRouteReturnsNotFound(): void
    {
        $result = Router::application()->dispatch(Request::create('GET', '/api/v1/not-real'));

        self::assertSame(404, $result->status());
    }

    public function testKnownApiPathRejectsUnsupportedMethod(): void
    {
        $result = Router::application()->dispatch(Request::create('PATCH', '/api/v1/process-routes'));

        self::assertSame(405, $result->status());
        self::assertEqualsCanonicalizing(['GET', 'POST'], $result->allowedMethods());
    }
}
