<?php

declare(strict_types=1);

namespace CApp\Auth;

interface UserProvider
{
    public function findByUsername(string $username): ?UserIdentity;
}
