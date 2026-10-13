<?php

declare(strict_types=1);

namespace CApp\Http;

use Throwable;

final class Application
{
    private function __construct(
        private readonly Router $router,
        private readonly CorsPolicy $cors,
        private readonly RouteHandler $routeHandler,
    ) {
    }

    public static function create(
        string $projectRoot,
        string $allowedOrigins,
        ?RouteHandler $routeHandler = null,
    ): self {
        return new self(
            Router::application(),
            CorsPolicy::fromCommaSeparated($allowedOrigins),
            $routeHandler ?? new LegacyRouteHandler(),
        );
    }

    public function handle(Request $request): Response
    {
        try {
            $contentType = strtolower(trim(explode(';', $request->header('Content-Type') ?? '', 2)[0]));
            if ($contentType === 'application/json') {
                $request->json();
            }
            $response = $this->dispatch($request);
        } catch (BadRequestException) {
            $response = JsonResponse::create(['message' => 'Bad Request'], 400);
        } catch (Throwable) {
            $response = JsonResponse::create(['message' => 'Internal Server Error'], 500);
        }

        return $this->cors->apply($request, $response);
    }

    private function dispatch(Request $request): Response
    {
        if ($request->method() === 'OPTIONS') {
            return $this->preflight($request);
        }

        $route = $this->router->dispatch($request);
        if ($route->status() === 404) {
            return JsonResponse::create(['message' => 'Not Found'], 404);
        }

        if ($route->status() === 405) {
            return JsonResponse::create(['message' => 'Method Not Allowed'], 405)
                ->withHeaders(['Allow' => implode(', ', $route->allowedMethods())]);
        }

        $handler = $route->handler();
        if ($handler === 'system.health') {
            return JsonResponse::create(['status' => 'ok']);
        }

        if ($handler === null) {
            return JsonResponse::create(['message' => 'Not Found'], 404);
        }

        return $this->routeHandler->handle($handler, $route->params());
    }

    private function preflight(Request $request): Response
    {
        if (!$this->cors->isOriginAllowed($request->header('Origin'))) {
            return JsonResponse::create(['message' => 'Forbidden'], 403);
        }

        $method = strtoupper(trim($request->header('Access-Control-Request-Method') ?? ''));
        if ($method === '') {
            return JsonResponse::create(['message' => 'Bad Request'], 400);
        }

        $target = Request::create($method, $request->path());
        $route = $this->router->dispatch($target);
        if ($route->status() === 404) {
            return JsonResponse::create(['message' => 'Not Found'], 404);
        }

        if ($route->status() === 405) {
            return JsonResponse::create(['message' => 'Method Not Allowed'], 405)
                ->withHeaders(['Allow' => implode(', ', $route->allowedMethods())]);
        }

        $requestedHeaders = $request->header('Access-Control-Request-Headers') ?? '';
        $allowedHeaders = $this->cors->allowedRequestHeaders($requestedHeaders);
        if ($requestedHeaders !== '' && $allowedHeaders === []) {
            return JsonResponse::create(['message' => 'Forbidden'], 403);
        }

        return new Response(status: 204, headers: [
            'Access-Control-Allow-Methods' => $method,
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-CSRF-Token',
        ]);
    }
}
