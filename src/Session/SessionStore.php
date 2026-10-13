<?php

declare(strict_types=1);

namespace CApp\Session;

interface SessionStore
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value): void;

    public function remove(string $key): void;

    public function clear(): void;

    public function regenerate(): void;

    public function id(): string;
}
