<?php

declare(strict_types=1);

namespace CApp\Tests\Api;

use PHPUnit\Framework\TestCase;

final class PublicIndexTest extends TestCase
{
    public function testConfigurationFailureReturnsSanitizedServerError(): void
    {
        $root = dirname(__DIR__, 2);
        $command = sprintf(
            'env -i PATH=%s APP_ENV=production APP_URL=http://insecure.example APP_KEY=short APP_ALLOWED_ORIGINS="*" SESSION_NAME=capp php %s 2>&1',
            escapeshellarg((string) getenv('PATH')),
            escapeshellarg($root . '/public/index.php'),
        );

        exec($command, $lines, $exitCode);
        $output = implode("\n", $lines);

        self::assertSame(0, $exitCode);
        self::assertSame('{"message":"Internal Server Error"}', $output);
        self::assertStringNotContainsString('APP_KEY', $output);
        self::assertStringNotContainsString('Stack trace', $output);
    }
}
