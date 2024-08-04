<?php

namespace Meirelles\EnumMapper\DB;

use PDO;

class Connector
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(string $host, string $database, string $username, string $password): PDO
    {
        if (self::$instance === null) {
            self::$instance = new PDO(
                "mysql:host=$host;dbname=$database",
                $username,
                $password
            );
        }

        return self::$instance;
    }
}