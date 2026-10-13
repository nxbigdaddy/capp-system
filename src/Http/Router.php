<?php

declare(strict_types=1);

namespace CApp\Http;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use function FastRoute\simpleDispatcher;

final class Router
{
    private function __construct(private readonly Dispatcher $dispatcher)
    {
    }

    public static function application(): self
    {
        $dispatcher = simpleDispatcher(static function (RouteCollector $routes): void {
            $routes->addRoute('GET', '/health', 'system.health');
            $routes->addRoute('GET', '/mobile[/]', 'legacy.mobile.home');

            $routes->addRoute('GET', '/api/v1/process-routes', 'legacy.process-routes.index');
            $routes->addRoute('POST', '/api/v1/process-routes', 'legacy.process-routes.store');
            $routes->addRoute('GET', '/api/v1/process-routes/{id:\\d+}', 'legacy.process-routes.show');
            $routes->addRoute('PUT', '/api/v1/process-routes/{id:\\d+}', 'legacy.process-routes.update');
            $routes->addRoute('DELETE', '/api/v1/process-routes/{id:\\d+}', 'legacy.process-routes.destroy');
            $routes->addRoute('POST', '/api/v1/process-routes/{id:\\d+}/submit', 'legacy.process-routes.submit');
            $routes->addRoute('POST', '/api/v1/process-routes/{id:\\d+}/approve', 'legacy.process-routes.approve');
            $routes->addRoute('POST', '/api/v1/process-routes/{id:\\d+}/reject', 'legacy.process-routes.reject');
            $routes->addRoute('POST', '/api/v1/process-routes/{id:\\d+}/publish', 'legacy.process-routes.publish');

            $routes->addRoute('GET', '/api/v1/approvals/todo', 'legacy.approvals.todo');
            $routes->addRoute('GET', '/api/v1/approvals/history', 'legacy.approvals.history');

            $routes->addRoute('GET', '/mobile/route/{id:\\d+}', 'legacy.mobile.route');
            $routes->addRoute('GET', '/mobile/operation/{id:\\d+}', 'legacy.mobile.operation');
            $routes->addRoute('GET', '/mobile/scan', 'legacy.mobile.scan');
            $routes->addRoute('GET', '/mobile/todos', 'legacy.mobile.todos');
            $routes->addRoute('GET', '/mobile/profile', 'legacy.mobile.profile');
        });

        return new self($dispatcher);
    }

    public function dispatch(Request $request): RouteResult
    {
        $route = $this->dispatcher->dispatch($request->method(), $request->path());

        return match ($route[0]) {
            Dispatcher::FOUND => RouteResult::found((string) $route[1], $route[2]),
            Dispatcher::METHOD_NOT_ALLOWED => RouteResult::methodNotAllowed($route[1]),
            default => RouteResult::notFound(),
        };
    }
}
