<?php

declare(strict_types=1);

namespace CApp\Http;

final class Pagination
{
    private function __construct(private readonly int $page, private readonly int $pageSize)
    {
    }

    public static function from(int $page, int $pageSize): self
    {
        return new self(max(1, $page), min(100, max(1, $pageSize)));
    }

    public function page(): int
    {
        return $this->page;
    }

    public function pageSize(): int
    {
        return $this->pageSize;
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->pageSize;
    }
}
