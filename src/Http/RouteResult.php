<?php

declare(strict_types=1);

namespace CApp\Http;

final class RouteResult
{
    /**
     * @param array<string, string> $params
     * @param list<string> $allowedMethods
     */
    private function __construct(
        private readonly int $status,
        private readonly ?string $handler = null,
        private readonly array $params = [],
        private readonly array $allowedMethods = [],
    ) {
    }

    /** @param array<string, string> $params */
    public static function found(string $handler, array $params = []): self
    {
        return new self(200, $handler, $params);
    }

    public static function notFound(): self
    {
        return new self(404);
    }

    /** @param list<string> $allowedMethods */
    public static function methodNotAllowed(array $allowedMethods): self
    {
        return new self(405, allowedMethods: $allowedMethods);
    }

    public function status(): int
    {
        return $this->status;
    }

    public function isFound(): bool
    {
        return $this->status === 200;
    }

    public function handler(): ?string
    {
        return $this->handler;
    }

    /** @return array<string, string> */
    public function params(): array
    {
        return $this->params;
    }

    /** @return list<string> */
    public function allowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
