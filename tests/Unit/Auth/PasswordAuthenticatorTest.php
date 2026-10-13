<?php

declare(strict_types=1);

namespace CApp\Tests\Unit\Auth;

use CApp\Auth\AuthenticationException;
use CApp\Auth\PasswordAuthenticator;
use CApp\Auth\UserIdentity;
use CApp\Auth\UserProvider;
use PHPUnit\Framework\TestCase;

final class PasswordAuthenticatorTest extends TestCase
{
    private UserProvider $users;

    protected function setUp(): void
    {
        $enabled = new UserIdentity(7, 'enabled', password_hash('correct-password', PASSWORD_DEFAULT), true);
        $disabled = new UserIdentity(8, 'disabled', password_hash('correct-password', PASSWORD_DEFAULT), false);

        $this->users = new class ($enabled, $disabled) implements UserProvider {
            public function __construct(private UserIdentity $enabled, private UserIdentity $disabled)
            {
            }

            public function findByUsername(string $username): ?UserIdentity
            {
                return match ($username) {
                    'enabled' => $this->enabled,
                    'disabled' => $this->disabled,
                    default => null,
                };
            }
        };
    }

    public function testReturnsEnabledUserForCorrectPassword(): void
    {
        $identity = (new PasswordAuthenticator($this->users))->authenticate('enabled', 'correct-password');

        self::assertSame(7, $identity->id());
        self::assertSame('enabled', $identity->username());
    }

    public function testRejectsWrongPasswordWithUnifiedMessage(): void
    {
        try {
            (new PasswordAuthenticator($this->users))->authenticate('enabled', 'wrong-password');
            self::fail('Expected authentication to fail.');
        } catch (AuthenticationException $exception) {
            self::assertSame(AuthenticationException::INVALID_CREDENTIALS, $exception->reason());
            self::assertSame('Authentication failed', $exception->getMessage());
        }
    }

    public function testRejectsDisabledUserWithoutPasswordDetails(): void
    {
        try {
            (new PasswordAuthenticator($this->users))->authenticate('disabled', 'correct-password');
            self::fail('Expected authentication to fail.');
        } catch (AuthenticationException $exception) {
            self::assertSame(AuthenticationException::USER_DISABLED, $exception->reason());
            self::assertSame('Authentication failed', $exception->getMessage());
            self::assertStringNotContainsString('password', strtolower($exception->getMessage()));
        }
    }
}
