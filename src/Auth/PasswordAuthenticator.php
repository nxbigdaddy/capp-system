<?php

declare(strict_types=1);

namespace CApp\Auth;

final class PasswordAuthenticator
{
    public function __construct(private readonly UserProvider $users)
    {
    }

    public function authenticate(string $username, string $password): UserIdentity
    {
        $identity = $this->users->findByUsername($username);
        if ($identity === null || !password_verify($password, $identity->passwordHash())) {
            throw new AuthenticationException(AuthenticationException::INVALID_CREDENTIALS);
        }

        if (!$identity->isEnabled()) {
            throw new AuthenticationException(AuthenticationException::USER_DISABLED);
        }

        return $identity;
    }
}
