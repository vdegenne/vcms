<?php
namespace vcms\resources;

use vcms\VcmsObject;


require_once __DIR__ . "/../VcmsObject.class.php";


class ResourceConfig extends VcmsObject
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
}