<?php

namespace Meirelles\EnumMapper\Enum;

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

    /**
     * @return bool
     */
    public function execute(): bool
    {
        $template = file_get_contents('src/templates/default_enum.mustache');

        if (!$template) {
            throw new RuntimeException('Failed to read template');
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

        $hasText = $this->config->hasText;

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
            'enumName' => $enumName,
            'enumType' => $enumType,
            'constants' => $constants,
            'hasText' => $hasText
        ]);

        return (bool)file_put_contents("src/Enum/$enumName.php", $output);
    }
}