<?php

declare(strict_types=1);

namespace CApp\Auth;

final class UserIdentity
{
    public function __construct(
        private readonly int $id,
        private readonly string $username,
        private readonly string $passwordHash,
        private readonly bool $enabled,
    ) {
    }

    public function id(): int
    {
        return $this->id;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
