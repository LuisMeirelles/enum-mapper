<?php

namespace Tests\DB;

use Meirelles\EnumMapper\DB\Connector;
use Mockery;
use PDO;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\TestCase;

class ConnectorTest extends TestCase
{
    #[After]
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testReturnPDOInstance(): void
    {
        $pdo = Connector::getInstance(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
        );

        $this->assertInstanceOf(PDO::class, $pdo);
    }

    public function testReturnSameInstance(): void
    {
        $pdo1 = Connector::getInstance(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
        );

        $pdo2 = Connector::getInstance(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
        );

        $this->assertSame($pdo1, $pdo2);
    }
}
