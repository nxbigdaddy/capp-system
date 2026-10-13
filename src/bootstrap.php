<?php

declare(strict_types=1);

use CApp\Bootstrap\Environment;
use CApp\Http\Application;
use CApp\Session\NativeSessionStore;

$projectRoot = dirname(__DIR__);
$autoload = $projectRoot . '/vendor/autoload.php';
if (!is_file($autoload)) {
    throw new RuntimeException('Composer dependencies are not installed.');
}

require_once $autoload;

Environment::load($projectRoot);
$configuration = Environment::securityConfiguration([
    'APP_ENV' => Environment::get('APP_ENV'),
    'APP_URL' => Environment::get('APP_URL'),
    'APP_KEY' => Environment::get('APP_KEY'),
    'APP_ALLOWED_ORIGINS' => Environment::get('APP_ALLOWED_ORIGINS'),
    'SESSION_NAME' => Environment::get('SESSION_NAME'),
]);

NativeSessionStore::start($configuration['APP_ENV'], $configuration['SESSION_NAME']);

return Application::create(
    $projectRoot,
    $configuration['APP_ALLOWED_ORIGINS'],
);
