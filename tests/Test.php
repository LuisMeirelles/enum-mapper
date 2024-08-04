<?php

namespace Tests;

use Meirelles\EnumMapper\Enum\Config;
use Meirelles\EnumMapper\Enum\Mapper;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    public function test()
    {
        $config = new Config(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
            tableName: 'roles',
            enumName: 'Role',
            hasText: true
        );

        $mapper = new Mapper($config);

        $success = $mapper->execute();

        $this->assertTrue($success);
    }
}
