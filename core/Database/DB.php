<?php

namespace Core\Database;

use PDO;

class DB
{
    protected static ?PDO $connection = null;

    public static function connect(array $config): void
    {
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";

        self::$connection = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            throw new \RuntimeException('Database connection not established');
        }

        return self::$connection;
    }

    public static function statement(string $sql, array $bindings = []): bool
    {
        $statement = self::connection()->prepare($sql);
        return $statement->execute($bindings);
    }
}