<?php

declare(strict_types=1);

namespace CApp\Tests\Api;

use CApp\Http\Application;
use CApp\Http\Request;
use CApp\Http\Response;
use CApp\Http\RouteHandler;
use PHPUnit\Framework\TestCase;

final class ApplicationTest extends TestCase
{
    private Application $application;

    protected function setUp(): void
    {
        $this->application = Application::create(
            dirname(__DIR__, 2),
            'https://allowed.example,https://second.example',
        );
    }

    public function testHealthReturnsJsonWithoutDatabase(): void
    {
        $response = $this->application->handle(Request::create('GET', '/health'));

        self::assertSame(200, $response->status());
        self::assertSame(['status' => 'ok'], json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testUnknownApiReturnsJsonNotFound(): void
    {
        $response = $this->application->handle(Request::create('GET', '/api/v1/unknown'));

        self::assertSame(404, $response->status());
        self::assertSame('Not Found', json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR)['message']);
    }

    public function testUnsupportedMethodReturnsJsonAndAllowHeader(): void
    {
        $response = $this->application->handle(Request::create('PATCH', '/api/v1/process-routes'));

        self::assertSame(405, $response->status());
        self::assertSame('GET, POST', $response->headers()['Allow']);
    }

    public function testAllowedOriginReceivesCorsHeader(): void
    {
        $request = Request::create('GET', '/health', ['Origin' => 'https://allowed.example']);

        $response = $this->application->handle($request);

        self::assertSame('https://allowed.example', $response->headers()['Access-Control-Allow-Origin']);
        self::assertSame('Origin', $response->headers()['Vary']);
    }

    public function testUnlistedOriginReceivesVaryWithoutAllowOrigin(): void
    {
        $request = Request::create('GET', '/health', ['Origin' => 'https://blocked.example']);

        $response = $this->application->handle($request);

        self::assertArrayNotHasKey('Access-Control-Allow-Origin', $response->headers());
        self::assertSame('Origin', $response->headers()['Vary']);
    }

    public function testPreflightReturnsNoContentForAllowedRouteMethodAndHeaders(): void
    {
        $request = Request::create('OPTIONS', '/api/v1/process-routes', [
            'Origin' => 'https://allowed.example',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type, Authorization, X-CSRF-Token',
        ]);

        $response = $this->application->handle($request);

        self::assertSame(204, $response->status());
        self::assertSame('', $response->body());
        self::assertSame('POST', $response->headers()['Access-Control-Allow-Methods']);
        self::assertSame('Content-Type, Authorization, X-CSRF-Token', $response->headers()['Access-Control-Allow-Headers']);
    }

    public function testPreflightRejectsUnknownRoute(): void
    {
        $response = $this->application->handle($this->preflight('/api/v1/unknown', 'GET'));

        self::assertSame(404, $response->status());
    }

    public function testPreflightRejectsUnlistedOrigin(): void
    {
        $request = Request::create('OPTIONS', '/health', [
            'Origin' => 'https://blocked.example',
            'Access-Control-Request-Method' => 'GET',
        ]);

        $response = $this->application->handle($request);

        self::assertSame(403, $response->status());
        self::assertArrayNotHasKey('Access-Control-Allow-Origin', $response->headers());
    }

    public function testPreflightRejectsMethodNotAllowedByTargetRoute(): void
    {
        $response = $this->application->handle($this->preflight('/health', 'POST'));

        self::assertSame(405, $response->status());
        self::assertSame('GET', $response->headers()['Allow']);
    }

    public function testPreflightRejectsUnsupportedRequestHeader(): void
    {
        $request = Request::create('OPTIONS', '/health', [
            'Origin' => 'https://allowed.example',
            'Access-Control-Request-Method' => 'GET',
            'Access-Control-Request-Headers' => 'X-Admin',
        ]);

        self::assertSame(403, $this->application->handle($request)->status());
    }

    public function testLegacyApiRouteIsAnExplicitPlaceholder(): void
    {
        $response = $this->application->handle(Request::create('POST', '/api/v1/process-routes/42/approve'));

        self::assertSame(501, $response->status());
        self::assertSame('legacy.process-routes.approve', json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR)['handler']);
    }

    public function testMobileHomeRendersControlledShellWithAndWithoutTrailingSlash(): void
    {
        foreach (['/mobile', '/mobile/'] as $path) {
            $response = $this->application->handle(Request::create('GET', $path));

            self::assertSame(200, $response->status());
            self::assertStringContainsString('<title>CAPP', $response->body());
            self::assertStringNotContainsString('html5-qrcode', $response->body());
        }
    }

    public function testUnmigratedMobilePagesReturnControlledServiceUnavailable(): void
    {
        foreach (['/mobile/route/42', '/mobile/operation/7', '/mobile/todos', '/mobile/profile'] as $path) {
            $response = $this->application->handle(Request::create('GET', $path));

            self::assertSame(503, $response->status(), $path);
            self::assertStringContainsString('暂不可用', $response->body());
        }
    }

    public function testBadRequestIsSanitizedAndCorsIsApplied(): void
    {
        $application = Application::create(
            dirname(__DIR__, 2),
            'https://allowed.example',
        );
        $request = Request::create('POST', '/api/v1/process-routes', [
            'Origin' => 'https://allowed.example',
            'Content-Type' => 'application/json',
        ], body: '{invalid');

        $response = $application->handle($request);

        self::assertSame(400, $response->status());
        self::assertSame('Bad Request', json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR)['message']);
        self::assertSame('https://allowed.example', $response->headers()['Access-Control-Allow-Origin']);
    }

    public function testUnexpectedExceptionIsSanitizedAndCorsIsApplied(): void
    {
        $application = Application::create(
            dirname(__DIR__, 2),
            'https://allowed.example',
            new class implements RouteHandler {
                public function handle(string $handler, array $params): Response
                {
                    throw new \RuntimeException('database password leaked');
                }
            },
        );
        $request = Request::create('GET', '/api/v1/process-routes', ['Origin' => 'https://allowed.example']);

        $response = $application->handle($request);

        self::assertSame(500, $response->status());
        self::assertStringNotContainsString('password', $response->body());
        self::assertSame('https://allowed.example', $response->headers()['Access-Control-Allow-Origin']);
    }

    private function preflight(string $path, string $method): Request
    {
        return Request::create('OPTIONS', $path, [
            'Origin' => 'https://allowed.example',
            'Access-Control-Request-Method' => $method,
        ]);
    }
}
