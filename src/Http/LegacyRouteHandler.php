<?php

declare(strict_types=1);

namespace CApp\Http;

final class LegacyRouteHandler implements RouteHandler
{
    /** @param array<string, string> $params */
    public function handle(string $handler, array $params): Response
    {
        if ($handler === 'legacy.mobile.home') {
            return $this->mobileShell();
        }

        if (str_starts_with($handler, 'legacy.mobile.')) {
            return new Response(
                $this->unavailableMobilePage(),
                503,
                ['Content-Type' => 'text/html; charset=utf-8', 'Retry-After' => '300'],
            );
        }

        return JsonResponse::create([
            'message' => 'Legacy handler is not enabled in this delivery increment.',
            'handler' => $handler,
        ], 501);
    }

    private function mobileShell(): Response
    {
        $body = <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CAPP 移动端</title>
    <link rel="stylesheet" href="/assets/css/mobile.css">
</head>
<body>
    <main class="mobile-container">
        <header class="mobile-header"><h1>CAPP 工艺管理</h1></header>
        <section class="mobile-content">
            <p>移动端功能正在迁移中。</p>
        </section>
    </main>
</body>
</html>
HTML;

        return new Response($body, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    private function unavailableMobilePage(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CAPP 移动端暂不可用</title>
</head>
<body><main><h1>此移动端页面暂不可用</h1><p>请稍后重试。</p></main></body>
</html>
HTML;
    }
}
