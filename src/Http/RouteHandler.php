<?php

declare(strict_types=1);

namespace CApp\Http;

interface RouteHandler
{
    /** @param array<string, string> $params */
    public function handle(string $handler, array $params): Response;
}
