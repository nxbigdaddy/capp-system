<?php
/**
 * 数据库连接管理类
 * 支持 MySQL 和 SQL Server
 */
class Database
{
    private static $instances = [];

    /**
     * 获取MySQL连接
     */
    public static function getMysql(): PDO
    {
        if (!isset(self::$instances['mysql'])) {
            $config = require __DIR__ . '/../config/database.php';
            $cfg = $config['mysql'];
            $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['database']};charset={$cfg['charset']}";
            self::$instances['mysql'] = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
        }
        return self::$instances['mysql'];
    }

    /**
     * 获取SQL Server连接
     */
    public static function getSqlsrv(): PDO
    {
        if (!isset(self::$instances['sqlsrv'])) {
            $config = require __DIR__ . '/../config/database.php';
            $cfg = $config['sqlsrv'];
            $dsn = "sqlsrv:Server={$cfg['host']},{$cfg['port']};Database={$cfg['database']}";
            self::$instances['sqlsrv'] = new PDO($dsn, $cfg['username'], $cfg['password']);
            self::$instances['sqlsrv']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$instances['sqlsrv'];
    }

    /**
     * 获取当前使用的数据库连接
     */
    public static function get(): PDO
    {
        $driver = $_ENV['DB_DRIVER'] ?? 'mysql';
        return $driver === 'sqlsrv' ? self::getSqlsrv() : self::getMysql();
    }
}
