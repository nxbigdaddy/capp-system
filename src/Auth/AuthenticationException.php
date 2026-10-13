<?php

declare(strict_types=1);

namespace CApp\Auth;

use RuntimeException;

final class AuthenticationException extends RuntimeException
{
    public const string INVALID_CREDENTIALS = 'invalid_credentials';
    public const string USER_DISABLED = 'user_disabled';

    public function __construct(private readonly string $reason)
    {
        parent::__construct('Authentication failed');
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
