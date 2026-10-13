<?php

declare(strict_types=1);

namespace CApp\Session;

use RuntimeException;

final class NativeSessionStore implements SessionStore
{
    private function __construct()
    {
    }

    public static function start(string $environment, string $name): self
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return new self();
        }

        if (headers_sent($file, $line)) {
            throw new RuntimeException(sprintf('Cannot start session after headers were sent at %s:%d.', $file, $line));
        }

        if (ini_set('session.use_strict_mode', '1') === false) {
            throw new RuntimeException('Unable to enable strict session mode.');
        }

        session_name($name);
        session_set_cookie_params([
            'httponly' => true,
            'secure' => strtolower($environment) === 'production',
            'samesite' => 'Strict',
        ]);

        if (!session_start()) {
            throw new RuntimeException('Unable to start session.');
        }

        return new self();
    }

    public function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function clear(): void
    {
        $_SESSION = [];
    }

    public function regenerate(): void
    {
        if (!session_regenerate_id(true)) {
            throw new RuntimeException('Unable to regenerate session identifier.');
        }
    }

    public function id(): string
    {
        $identifier = session_id();
        if ($identifier === false) {
            throw new RuntimeException('Unable to read session identifier.');
        }

        return $identifier;
    }
}
