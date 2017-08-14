<?php
declare(strict_types=1);

namespace vcms\database;

use PDO;
use PDOException;
use Exception;

class Database extends PDO {
    const DEFAULT_DRIVER = DatabaseDriver::POSTGRESQL;


    static function get_from_handler (string $handler)
    {
        $Credentials = Credential::build_list_from_files();

        $matchingCreds = array_filter($Credentials, function ($C) use ($handler) {
            if ($C->handler === $handler) return true;
            return false;
        });

        if (count($matchingCreds) === 0) {
            throw new \Exception('no matching credentials for the database.');
        }

        $matchingCreds = array_values($matchingCreds);
        return new Database($matchingCreds[0]);
    }

    function __construct (Credential $Credential)
    {

        switch ($Credential->driver) {

            case DatabaseDriver::POSTGRESQL:
                $dsn = "pgsql:host=$Credential->ip;dbname=$Credential->databaseName";
                break;

            case DatabaseDriver::MYSQL:
                $dsn = "mysql:host=$Credential->ip;dbname=$Credential->databaseName";
                break;

            default:
                throw new Exception ('no appropriate drivers.');
        }


        try {
            parent::__construct($dsn, $Credential->user, $Credential->get_password());
            $this->setAttribute(parent::ATTR_ERRMODE, parent::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            throw new Exception('error creating the database : ' . $e->getMessage());
        }
    }

}