<?php

namespace Meirelles\EnumMapper\DB;

use PDO;

class Connector
{
    private static ?PDO $instance = null;

    private function __construct(string $host, string $database, string $username, string $password)
    {
        if (self::$instance === null) {
            self::$instance = new PDO(
                "mysql:host=$host;dbname=$database",
                $username,
                $password
            );
        }
    }

    /**
     * @throws \PDOException
     */
    public static function getInstance(string $host, string $database, string $username, string $password): PDO
    {
        new self($host, $database, $username, $password);

        return self::$instance;
    }
}