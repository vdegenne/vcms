<?php
namespace vcms\database;


use vcms\VcmsObject;

class Credential extends VcmsObject
{
    const CREDENTIALS_FILENAME = '.db_credentials';

    protected $raw;
    protected $handler;
    protected $driver;
    protected $ip;
    protected $databaseName;
    protected $user;
    protected $password;

    static $search_in = [];

    static function build_list_from_files () {

        $credentialsFilepaths = [];
        foreach (self::$search_in as $dirpath) {
            if (file_exists($dirpath.'/'.self::CREDENTIALS_FILENAME)) {
                $credentialsFilepaths[] = $dirpath.'/'.self::CREDENTIALS_FILENAME;
            }
        }

        if (count($credentialsFilepaths) === 0) {
            throw new \Exception('no credentials files was found.');
        }

        $Credentials = [];
        foreach ($credentialsFilepaths as $filepath)
        {
            $fileContent = file_get_contents($filepath);

            foreach (explode("\r\n", $fileContent) as $dsnRaw) {
                $C = new Credential();
                $C->raw = $dsnRaw;

                list($C->handler, $C->driver, $C->ip, $C->databaseName, $C->user, $C->password) = explode(':', $dsnRaw);

                $C->driver = (new DatabaseDriver())->{$C->driver};
                $C->password = trim($C->password);

                $Credentials[] = $C;
            }

            return $Credentials;
        }
    }
}