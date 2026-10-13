<?php
/**
 * 数据库配置
 */
return [
    'mysql' => [
        'host'     => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'port'     => $_ENV['DB_PORT'] ?? 3306,
        'database' => $_ENV['DB_NAME'] ?? 'capp_db',
        'username' => $_ENV['DB_USER'] ?? 'capp_user',
        'password' => $_ENV['DB_PASS'] ?? '',
        'charset'  => 'utf8mb4',
        'options'  => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],
    'sqlsrv' => [
        'host'     => $_ENV['MSSQL_HOST'] ?? '127.0.0.1',
        'port'     => $_ENV['MSSQL_PORT'] ?? 1433,
        'database' => $_ENV['MSSQL_NAME'] ?? 'capp_db',
        'username' => $_ENV['MSSQL_USER'] ?? 'sa',
        'password' => $_ENV['MSSQL_PASS'] ?? '',
    ],
];
