<?php

declare(strict_types=1);

namespace CApp\Tests\Unit\Session;

use CApp\Session\NativeSessionStore;
use PHPUnit\Framework\TestCase;

final class NativeSessionStoreTest extends TestCase
{
    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $_SESSION = [];
        session_id('');
        session_name('PHPSESSID');
    }

    public function testStartsNativeSessionWithSecureProductionCookiePolicy(): void
    {
        $store = NativeSessionStore::start('production', 'capp_session');

        $params = session_get_cookie_params();
        self::assertSame('capp_session', session_name());
        self::assertSame('1', ini_get('session.use_strict_mode'));
        self::assertTrue($params['httponly']);
        self::assertTrue($params['secure']);
        self::assertSame('Strict', $params['samesite']);
        self::assertNotSame('', $store->id());
    }

    public function testDevelopmentCookieIsNotSecureAndValuesCanRotateWithoutOutput(): void
    {
        $this->expectOutputString('');
        $store = NativeSessionStore::start('development', 'capp_session');
        $store->set('user_id', 7);
        $before = $store->id();

        $store->regenerate();

        self::assertFalse(session_get_cookie_params()['secure']);
        self::assertNotSame($before, $store->id());
        self::assertSame(7, $store->get('user_id'));
    }

    public function testClearAndRemoveMutateNativeSession(): void
    {
        $store = NativeSessionStore::start('test', 'capp_session');
        $store->set('first', 1);
        $store->set('second', 2);
        $store->remove('first');

        self::assertNull($store->get('first'));
        self::assertSame(2, $store->get('second'));

        $store->clear();
        self::assertNull($store->get('second'));
    }
}
