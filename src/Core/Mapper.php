<?php

namespace Meirelles\EnumMapper\Core;

use Meirelles\EnumMapper\DB\Connector;
use Mustache_Engine;
use PDO;
use PDOException;
use RuntimeException;

readonly class Mapper
{
    public function __construct(
        private Config $config
    )
    {
    }

    public function execute(): void
    {
        $template = file_get_contents('src/templates/default_enum.mustache');

        if (!$template) {
            throw new RuntimeException('Failed to read template');
        }

        $enumPath = $this->config->enumPath;
        $enumPath = rtrim($enumPath, '/');

        if (!is_dir($enumPath)) {
            mkdir($enumPath, 0755, true);
        }

        try {
            $pdo = Connector::getInstance(
                host: $this->config->host,
                database: $this->config->database,
                username: $this->config->username,
                password: $this->config->password
            );
        } catch (PDOException $e) {
            throw new RuntimeException(
                message: 'Failed to connect to database',
                previous: $e
            );
        }

        $mustacheEngine = new Mustache_Engine;

        $enumName = $this->config->enumName;
        $enumName = mb_ucfirst($enumName);

        $namespace = $this->config->namespace;

        $enumType = $this->config->enumType;

        try {
            $tableName = $this->config->tableName;

            $result = $pdo->query("SELECT * FROM `$tableName`")
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new RuntimeException(
                message: 'Failed to fetch data from database',
                previous: $e
            );
        }

        $constants = [];

        $idColumnName = $this->config->idColumnName;

        $valueColumnName = $this->config->valueColumnName;
        $mbCaseFlag = $this->config->mbCaseFlag;

        $hasText = $this->config->hasDescription;

        foreach ($result as $item) {
            $id = $item[$idColumnName];

            $memberName = $item[$valueColumnName];
            $memberName = mb_convert_case($memberName, $mbCaseFlag);

            $constants[] = [
                'name' => $memberName,
                'value' => $id,
            ];

            if ($hasText) {
                $textColumnName = $this->config->textColumnName;
                $textColumnName = $item[$textColumnName];

                $lastIndex = count($constants) - 1;

                $constants[$lastIndex]['text'] = "'$textColumnName'";
            }
        }

        $output = $mustacheEngine->render($template, [
            'namespace' => $namespace,
            'enumName' => $enumName,
            'enumType' => $enumType,
            'constants' => $constants,
            'hasText' => $hasText
        ]);

        $createFile = (bool)file_put_contents("$enumPath/$enumName.php", $output);

        if (!$createFile) {
            throw new RuntimeException('Failed to create enum file');
        }

        $changeMode = chmod(realpath("$enumPath/$enumName.php"), 0644);

        if (!$changeMode) {
            throw new RuntimeException('Failed to change mode of enum file');
        }
    }
}