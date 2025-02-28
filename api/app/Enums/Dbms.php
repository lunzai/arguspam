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
}
