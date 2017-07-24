<?php
namespace vcms\resources\implementations;

use Exception;
use vcms\Config;
use vcms\resources\ResourceException;

class ResourceConfig extends Config
{
    const CONFIGURATION_FILENAME = 'resource.json';


    public $type;
    public $stringType;

    public $mimetype;

    public $needs_database;
    public $database;

    public $needs_authentication;
    public $authentication_uri;
    public $is_auth_page;
    public $authentication_db;
    public $authentication_table;


    public $get_params;
    public $post_params;


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
        $this->stringType = $this->type;
        $this->type = ResourceType::from_string($this->stringType);
    }
}