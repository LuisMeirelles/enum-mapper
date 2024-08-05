<?php

namespace Meirelles\EnumMapper\Core;

readonly class Config
{
    /**
     * @param string $host host to connect to the database
     * @param string $database database name
     * @param string $username database username
     * @param string $password database password
     * @param string $tableName table name to store the enums
     * @param bool $hasText if the enum has a text column
     * @param string $textColumnName text column name
     * @param string $valueColumnName value column name
     * @param string $idColumnName id column name
     * @param int $mbCaseFlag mb_case flag to use in the enum member names
     */
    public function __construct(
        public string $host,
        public string $database,
        public string $username,
        public string $password,
        public string $tableName,
        public string $enumName,
        public string $enumType = 'int',
        public bool   $hasText = false,
        public string $textColumnName = 'description',
        public string $valueColumnName = 'value',
        public string $idColumnName = 'id',
        public int    $mbCaseFlag = MB_CASE_UPPER,
        public string $enumPath = __DIR__ . '/../Enums',
        public string $namespace = 'Meirelles\EnumMapper\Enums',
    )
    {
    }
}