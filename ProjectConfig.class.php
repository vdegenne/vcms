<?php
namespace vcms;


require __DIR__ . '/Config.class.php';

class ProjectConfig extends Config
{
    const CONFIGURATION_FILENAME = 'project.json';


    public $name;
    public $env;

    public $credentials_file;

    function check_required (array $required = [])
    {
        $required = array_merge($required, ['name', 'env']);
        parent::check_required($required);
    }
}