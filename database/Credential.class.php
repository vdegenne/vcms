<?php
namespace vcms\database;


use vcms\VcmsObject;

class Credential extends VcmsObject
{

    protected $raw;
    protected $handler;
    protected $driver;
    protected $ip;
    protected $databaseName;
    protected $user;
    protected $password;


    static function build_list_from_files ($filepath) {

        if (!file_exists($filepath)) {
            throw new \Exception('credentials file not found.');
        }

        $fileContent = file_get_contents($filepath);

        $Credentials = [];
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