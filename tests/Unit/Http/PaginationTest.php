<?php

declare(strict_types=1);

namespace CApp\Tests\Unit\Http;

use CApp\Http\Pagination;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PaginationTest extends TestCase
{
    /** @return iterable<string, array{int, int, int, int}> */
    public static function normalizedValues(): iterable
    {
        yield 'lower bounds' => [0, 0, 1, 1];
        yield 'ordinary values' => [3, 25, 3, 25];
        yield 'upper page size' => [2, 101, 2, 100];
    }

    #[DataProvider('normalizedValues')]
    public function testNormalizesPageAndPageSize(int $page, int $pageSize, int $expectedPage, int $expectedPageSize): void
    {
        $pagination = Pagination::from($page, $pageSize);

        self::assertSame($expectedPage, $pagination->page());
        self::assertSame($expectedPageSize, $pagination->pageSize());
        self::assertSame(($expectedPage - 1) * $expectedPageSize, $pagination->offset());
    }
}
