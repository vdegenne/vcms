<?php
namespace vcms\database;

class DatabaseDriver
{
    const POSTGRESQL = 0;
    const MYSQL = 1;

    function __get ($name)
    {
        switch ($name) {
            case 'pgsql':
                return self::POSTGRESQL;
                break;
            case 'mysql':
                return self::MYSQL;
                break;
        }
    }


}