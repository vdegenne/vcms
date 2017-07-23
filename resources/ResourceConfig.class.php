<?php
namespace vcms\resources\implementations;

use Exception;

class ResourceConfig
{
    protected $type;

    protected $mimetype;

    protected $needs_database;
    protected $database;

    protected $needs_authentication;
    protected $authentication_uri;
    protected $is_auth_page;
    protected $authentication_db;
    protected $authentication_table;


    function check_required (array $required = [])
    {
        $required = array_merge($required, ['type']);

        foreach ($required as $r) {
            if (!isset($this->{$r})) {
                throw new Exception("property \"$r\" missing from the configuration file.");
            }
        }
    }
}