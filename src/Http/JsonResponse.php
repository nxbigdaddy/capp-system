<?php

declare(strict_types=1);

namespace CApp\Http;

use JsonException;

final class JsonResponse extends Response
{
    /** @param array<string, mixed> $data */
    private function __construct(array $data, int $status)
    {
        parent::__construct(
            self::encode($data),
            $status,
            ['Content-Type' => 'application/json; charset=utf-8'],
        );
    }

    /** @param array<string, mixed> $data */
    public static function create(array $data, int $status = 200): self
    {
        return new self($data, $status);
    }

    /** @param array<string, mixed> $data */
    private static function encode(array $data): string
    {
        try {
            return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new JsonException('Unable to encode JSON response.', previous: $exception);
        }
    }
}
