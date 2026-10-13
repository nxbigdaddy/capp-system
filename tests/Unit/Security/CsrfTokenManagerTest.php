<?php

declare(strict_types=1);

namespace CApp\Tests\Unit\Security;

use CApp\Security\CsrfTokenManager;
use CApp\Session\ArraySessionStore;
use PHPUnit\Framework\TestCase;

final class CsrfTokenManagerTest extends TestCase
{
    public function testGeneratesAndStoresCryptographicallySizedToken(): void
    {
        $store = new ArraySessionStore();
        $manager = new CsrfTokenManager($store);

        $token = YOUR_SECRET);

        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        self::assertTrue($manager->validate($token));
    }

    public function testRejectsMissingOrIncorrectToken(): void
    {
        $manager = new CsrfTokenManager(new ArraySessionStore());
        $manager->generate();

        self::assertFalse($manager->validate(null));
        self::assertFalse($manager->validate('incorrect'));
    }

    public function testRotationInvalidatesPreviousToken(): void
    {
        $manager = new CsrfTokenManager(new ArraySessionStore());
        $oldToken = $manager->generate();

        $newToken = $manager->rotate();

        self::assertNotSame($oldToken, $newToken);
        self::assertFalse($manager->validate($oldToken));
        self::assertTrue($manager->validate($newToken));
    }
}
