<?php

declare(strict_types=1);

namespace CApp\Tests\Unit\Session;

use CApp\Session\ArraySessionStore;
use PHPUnit\Framework\TestCase;

final class ArraySessionStoreTest extends TestCase
{
    public function testRegenerateChangesSessionIdentifierAndPreservesValues(): void
    {
        $store = new ArraySessionStore(['user_id' => 7]);
        $before = $store->id();

        $store->regenerate();

        self::assertNotSame($before, $store->id());
        self::assertSame(7, $store->get('user_id'));
    }

    public function testClearRemovesAllValues(): void
    {
        $store = new ArraySessionStore(['user_id' => 7, 'csrf' => 'token']);

        $store->clear();

        self::assertNull($store->get('user_id'));
        self::assertNull($store->get('csrf'));
    }
}
