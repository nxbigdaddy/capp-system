<?php

declare(strict_types=1);

use CApp\Http\Application;
use CApp\Http\Request;

try {
    /** @var Application $application */
    $application = require dirname(__DIR__) . '/src/bootstrap.php';
    $application->handle(Request::fromGlobals())->send();
} catch (Throwable) {
    http_response_code(500);
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    print '{"message":"Internal Server Error"}';
}
