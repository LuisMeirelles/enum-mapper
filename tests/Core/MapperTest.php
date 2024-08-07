<?php

namespace Tests\Core;

use Meirelles\EnumMapper\Core\Config;
use Meirelles\EnumMapper\Core\Mapper;
use Meirelles\EnumMapper\DB\Connector;
use Mockery;
use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(Mapper::class)]
#[UsesClass(Config::class)]
class MapperTest extends TestCase
{
    /**
     * @param string $dirPath
     * @return string
     */
    private static function previousDirectory(string $dirPath): string
    {
        $pathParts = explode('/', $dirPath);
        array_pop($pathParts);
        return implode('/', $pathParts);
    }

    #[After]
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testIfEnumFileIsCreatedWithDefaultConfigs(): void
    {
        $pdoStatementMock = Mockery::mock(PDOStatement::class);
        $pdoStatementMock->shouldReceive('fetchAll')
            ->andReturn([
                ['id' => 1, 'value' => 'admin'],
                ['id' => 2, 'value' => 'guest'],
            ]);

        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('query')
            ->andReturn($pdoStatementMock);

        $connectorMock = Mockery::mock('alias:' . Connector::class);
        $connectorMock->shouldReceive('getInstance')
            ->andReturn($pdoMock);

        $enumName = 'Role';

        $config = new Config(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
            tableName: 'roles',
            enumName: $enumName,
        );

        $enumPath = $config->enumPath;
        $outputFilePath = "$enumPath/$enumName.php";

        $mapper = new Mapper($config);

        $mapper->execute();

        $this->assertFileExists($outputFilePath);

        $this->removeDirectory($enumPath);
    }

    public function testIfEnumFileIsCreatedWithRightNamespace(): void
    {
        $pdoStatementMock = Mockery::mock(PDOStatement::class);
        $pdoStatementMock->shouldReceive('fetchAll')
            ->andReturn([
                ['id' => 1, 'value' => 'admin'],
                ['id' => 2, 'value' => 'guest'],
            ]);

        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('query')
            ->andReturn($pdoStatementMock);

        $connectorMock = Mockery::mock('alias:' . Connector::class);
        $connectorMock->shouldReceive('getInstance')
            ->andReturn($pdoMock);

        $enumName = 'Role';

        $config = new Config(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
            tableName: 'roles',
            enumName: $enumName,
        );

        $enumPath = $config->enumPath;
        $outputFilePath = "$enumPath/$enumName.php";

        $mapper = new Mapper($config);

        $mapper->execute();

        $fileContent = file_get_contents($outputFilePath);

        $this->assertStringContainsString('namespace Meirelles\EnumMapper\Enums;', $fileContent);

        $this->removeDirectory($enumPath);
    }

    public function testIfEnumFileIsCreatedInNonExistentDirectory(): void
    {
        $pdoStatementMock = Mockery::mock(PDOStatement::class);
        $pdoStatementMock->shouldReceive('fetchAll')
            ->andReturn([
                ['id' => 1, 'value' => 'admin'],
                ['id' => 2, 'value' => 'guest'],
            ]);

        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('query')
            ->andReturn($pdoStatementMock);

        $connectorMock = Mockery::mock('alias:' . Connector::class);
        $connectorMock->shouldReceive('getInstance')
            ->andReturn($pdoMock);

        $enumName = 'Role';

        $config = new Config(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
            tableName: 'roles',
            enumName: $enumName,
            enumPath: __DIR__ . '/../../src/Enums/Not/Existent/Directory',
            namespace: 'Meirelles\EnumMapper\Enums\Not\Existent\Directory'
        );

        $enumPath = $config->enumPath;
        $outputFilePath = "$enumPath/$enumName.php";

        $mapper = new Mapper($config);

        $mapper->execute();

        $this->assertFileExists($outputFilePath);

        $this->removeDirectory($enumPath);
    }

    public function testIfEnumFileIsCreatedWithDescriptionColumn(): void
    {
        $pdoStatementMock = Mockery::mock(PDOStatement::class);
        $pdoStatementMock->shouldReceive('fetchAll')
            ->andReturn([
                ['id' => 1, 'value' => 'admin', 'description' => 'Administrador'],
                ['id' => 2, 'value' => 'guest', 'description' => 'Visitante'],
            ]);

        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('query')
            ->andReturn($pdoStatementMock);

        $connectorMock = Mockery::mock('alias:' . Connector::class);
        $connectorMock->shouldReceive('getInstance')
            ->andReturn($pdoMock);

        $enumName = 'Role';

        $config = new Config(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
            tableName: 'roles',
            enumName: $enumName,
            hasDescription: true,
        );

        $enumPath = $config->enumPath;
        $outputFilePath = "$enumPath/$enumName.php";

        $mapper = new Mapper($config);

        $mapper->execute();

        $this->assertFileExists($outputFilePath);

        $this->removeDirectory($enumPath);
    }

    public function testExecuteThrowsExceptionWhenTemplateFileNotFound(): void
    {
        $pdoStatementMock = Mockery::mock(PDOStatement::class);
        $pdoStatementMock->shouldReceive('fetchAll')
            ->andReturn([
                ['id' => 1, 'value' => 'admin'],
                ['id' => 2, 'value' => 'guest'],
            ]);

        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('query')
            ->andReturn($pdoStatementMock);

        $connectorMock = Mockery::mock('alias:' . Connector::class);
        $connectorMock->shouldReceive('getInstance')
            ->andReturn($pdoMock);

        $enumName = 'Role';

        $config = new Config(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
            tableName: 'roles',
            enumName: $enumName,
            templatePath: 'non/existing/directory/enum.mustache',
        );

        $mapper = new Mapper($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to read template');

        $mapper->execute();
    }

    public function testExecuteThrowsRuntimeExceptionWhenConnectorFails(): void
    {
        Mockery::mock('alias:' . Connector::class)
            ->shouldReceive('getInstance')
            ->andThrow(PDOException::class);

        $enumName = 'Role';

        $config = new Config(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
            tableName: 'roles',
            enumName: $enumName,
        );

        $mapper = new Mapper($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to connect to database');

        $mapper->execute();
    }

    public function testExecuteThrowsRuntimeExceptionWhenFetchingDataFails(): void
    {
        $pdoStatementMock = Mockery::mock(PDOStatement::class);
        $pdoStatementMock->shouldReceive('fetchAll')
            ->andThrow(PDOException::class);

        $pdoMock = Mockery::mock(PDO::class);
        $pdoMock->shouldReceive('query')
            ->andReturn($pdoStatementMock);

        Mockery::mock('alias:' . Connector::class)
            ->shouldReceive('getInstance')
            ->andReturn($pdoMock);

        $enumName = 'Role';

        $config = new Config(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
            tableName: 'roles',
            enumName: $enumName,
        );

        $mapper = new Mapper($config);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch data from database');

        $mapper->execute();
    }

    private static function removeFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            $pathParts = explode('.', $filePath);
            $extension = $pathParts[count($pathParts) - 1];

            $isGitKeep = $extension === 'gitkeep';

            if (!$isGitKeep && !unlink($filePath)) {
                throw new RuntimeException("Failed to remove file `$filePath`");
            }
        }
    }

    private static function removeDirectory(string $dirPath): void
    {
        if (is_dir($dirPath)) {
            $allItems = scandir($dirPath);
            $files = array_diff($allItems, ['.', '..']);

            foreach ($files as $file) {
                $path = "$dirPath/$file";

                if (is_dir($path)) {
                    self::removeDirectory($path);
                } else {
                    self::removeFile($path);
                }
            }

            $hasGitKeep = in_array('.gitkeep', $files);

            if ($hasGitKeep) {
                return;
            }

            if (!rmdir($dirPath)) {
                throw new RuntimeException("Failed to remove directory `$dirPath`");
            }

            $dirPath = self::previousDirectory($dirPath);

            self::removeDirectory($dirPath);
        }
    }
}