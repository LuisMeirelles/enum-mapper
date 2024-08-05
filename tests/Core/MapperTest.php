<?php

namespace Tests\Core;

use Meirelles\EnumMapper\Core\Config;
use Meirelles\EnumMapper\Core\Mapper;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MapperTest extends TestCase
{
    private string $outputFilePath;
    private string $enumPath;

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
    protected function cleanupFile(): void
    {
        $this->removeDirectory($this->enumPath);
    }

    public function testIfEnumFileIsCreatedWithDefaultConfigs()
    {
        $enumName = 'Role';

        $config = new Config(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
            tableName: 'roles',
            enumName: $enumName,
        );

        $this->enumPath = $config->enumPath;
        $this->outputFilePath = "$this->enumPath/$enumName.php";

        $mapper = new Mapper($config);

        $mapper->execute();

        $this->assertFileExists($this->outputFilePath);
    }

    public function testIfEnumFileIsCreatedWithRightNamespace()
    {
        $enumName = 'Role';

        $config = new Config(
            host: $_ENV['DB_HOST'],
            database: $_ENV['DB_DATABASE'],
            username: $_ENV['DB_USERNAME'],
            password: $_ENV['DB_PASSWORD'],
            tableName: 'roles',
            enumName: $enumName,
        );

        $this->enumPath = $config->enumPath;
        $this->outputFilePath = "$this->enumPath/$enumName.php";

        $mapper = new Mapper($config);

        $mapper->execute();

        $fileContent = file_get_contents($this->outputFilePath);

        $this->assertStringContainsString('namespace Meirelles\EnumMapper\Enums;', $fileContent);
    }

    public function testIfEnumFileIsCreatedInNonExistentDirectory()
    {
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

        $this->enumPath = $config->enumPath;
        $this->outputFilePath = "$this->enumPath/$enumName.php";

        $mapper = new Mapper($config);

        $mapper->execute();

        $this->assertFileExists($this->outputFilePath);
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