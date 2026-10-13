<?php

declare(strict_types=1);

namespace CApp\Tests\Unit\Http;

use CApp\Http\JsonResponse;
use PHPUnit\Framework\TestCase;

final class JsonResponseTest extends TestCase
{
    public function testCarriesHttpStatusAndJsonContentType(): void
    {
        $response = JsonResponse::create(['created' => true], 201);

        self::assertSame(201, $response->status());
        self::assertSame('application/json; charset=utf-8', $response->headers()['Content-Type']);
        self::assertSame(['created' => true], json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testEncodesUnicodeWithoutEscapingIt(): void
    {
        $response = JsonResponse::create(['message' => '接口不存在'], 404);

        self::assertStringContainsString('接口不存在', $response->body());
        self::assertSame(404, $response->status());
    }
}
