<?php
namespace vcms\resources;

use Exception;
use vcms\Config;
use vcms\resources\ResourceException;

class VResourceConfig extends Config
{
    const CONFIGURATION_FILENAME = 'resource.json';


    public $type = 'V';
    public $typeString = 10;

    public $mimetype;

    public $needs_database;
    public $database;

    public $needs_authentication;
    public $authentication_uri;
    public $is_auth_page;
    public $authentication_db;
    public $authentication_table;

    public $translations_schema;


    public $get_params;
    public $post_params;

    public $session_user_object;

    function check_required (array $required = [])
    {
        $required = array_merge($required, ['type']);

        try {
            parent::check_required($required);
        }
        catch (Exception $e) {
            throw new ResourceException($e->getMessage(), 1);
        }
    }

    function process_attributes () {
        /* intervert type attributes */
        $typeString = strtolower($this->type);
        $typeString[0] = strtoupper($typeString[0]);
        $this->typeString = $typeString;
        $this->type = ResourceType::from_string($this->typeString);
    }
}