<?php

declare(strict_types=1);

namespace CApp\Session;

final class ArraySessionStore implements SessionStore
{
    /** @var array<string, mixed> */
    private array $values = [];

    private string $identifier;

    /** @param array<string, mixed> $values */
    public function __construct(array $values = [])
    {
        $this->values = $values;
        $this->identifier = bin2hex(random_bytes(16));
    }

    public function get(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->values[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($this->values[$key]);
    }

    public function clear(): void
    {
        $this->values = [];
    }

    public function regenerate(): void
    {
        $this->identifier = bin2hex(random_bytes(16));
    }

    public function id(): string
    {
        return $this->identifier;
    }
}
