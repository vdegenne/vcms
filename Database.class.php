<?php
declare(strict_types = 1);

namespace vdegenne;

use vdegenne\DatabaseDriver;

class Database extends \PDO {

    /* constantes */
    const CREDENTIALS_FILENAME = '.credentials';
    const DEFAULT_DRIVER = DatabaseDriver::POSTGRE_SQL;

  
    /** @var \PDO
     * In the current state of the system, a singleton object is what we needed
     * But if there are several databases to connect to. This singleton class
     * needs to converted to a normal class.
     */
    static private $Database;

  
    /**
     * @param string $host
     * @param string $dbname
     * @param integer $driver
     * @return \PDO
     */
    static public function get ($host = null, $dbname = null, $driver = self::DEFAULT_DRIVER)
    {

        if (Database::$Database === null) {

            $credentialsFilepath = INCLUDES_PATH . '/' . Database::CREDENTIALS_FILENAME;

            (!file_exists($credentialsFilepath)) && trigger_error('Credentials file not found.', E_USER_ERROR);

            $credentials = explode("\r\n", file_get_contents($credentialsFilepath));
            foreach ($credentials as $credential) {

                list($credhost, $username, $password) = explode(':', $credential);

                $password = trim($password); /* removing the possible ending \n */

                if (preg_match("/$host/", $credhost)) goto found;
            }
            trigger_error('no matching credentials.');


            
          found:
            /* here are the different dsn based on the specified driver */
            switch ($driver) {

            case DatabaseDriver::POSTGRE_SQL:
                $dsn = "pgsql:host=$host;dbname=$dbname";
                break;
            default:
                trigger_error('no appropriate drivers.', E_USER_ERROR);
            }


            try {
                Database::$Database = new Database($dsn, $username, $password/*, [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES TO \'UTF8\';']*/);
                Database::$Database->setAttribute(parent::ATTR_ERRMODE, parent::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                trigger_error('error initializing the database : ' . $e->getMessage());
            }
        }


        return Database::$Database;
    }
}