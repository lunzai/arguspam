<?php

namespace App\Enums;

enum Dbms: string
{
    case MYSQL = 'mysql';
    case POSTGRESQL = 'postgresql';
    case SQLSERVER = 'sqlserver';
    case ORACLE = 'oracle';
    case MONGODB = 'mongodb';
    case REDIS = 'redis';
    case MARIADB = 'mariadb';

    public function label(): string
    {
        return match ($this) {
            self::MYSQL => 'MySQL',
            self::POSTGRESQL => 'PostgreSQL',
            self::SQLSERVER => 'SQL Server',
            self::ORACLE => 'Oracle',
            self::MONGODB => 'MongoDB',
            self::REDIS => 'Redis',
            self::MARIADB => 'MariaDB',
        };
    }
}
