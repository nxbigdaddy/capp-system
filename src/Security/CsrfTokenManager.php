<?php

declare(strict_types=1);

namespace CApp\Security;

use CApp\Session\SessionStore;

final class CsrfTokenManager
{
    private const string SESSION_KEY = '_csrf_token';

    public function __construct(private readonly SessionStore $session)
    {
    }

    public function generate(): string
    {
        $token = YOUR_SECRET));
        $this->session->set(self::SESSION_KEY, $token);

        return $token;
    }

    public function validate(?string $token): bool
    {
        $stored = $this->session->get(self::SESSION_KEY);

        return is_string($stored) && is_string($token) && $token !== '' && hash_equals($stored, $token);
    }

    public function rotate(): string
    {
        return $this->generate();
    }
}
