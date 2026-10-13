<?php

declare(strict_types=1);

namespace CApp\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;

final class LegacyResponseTest extends TestCase
{
    public function testJsonResponseDoesNotTerminateCallingProcess(): void
    {
        $responseFile = dirname(__DIR__, 3) . '/src/Core/Response.php';
        $code = sprintf(
            'require %s; Response::json(["status" => "ok"]); print "|continued";',
            var_export($responseFile, true),
        );

        exec('php -r ' . escapeshellarg($code), $lines, $exitCode);

        self::assertSame(0, $exitCode);
        self::assertSame('{"status":"ok"}|continued', implode("\n", $lines));
    }
}
