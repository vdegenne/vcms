<?php
namespace vcms;


require_once __DIR__ . '/Config.class.php';


class ProjectConfig extends Config
{
    const CONFIGURATION_FILENAME = 'project.json';

    public $name;

    /**
     * @var boolean
     */
    public $translation_support;
    public $langs;


    public $env;

    /**
     *
     * @var array
     */
    public $db_credentials_search_paths;

    public $session_user_classname;


    function process_attributes ()
    {
        $this->translation_support = isset($this->langs);

        parent::process_attributes();
    }


    function check_required (array $required = [])
    {
        $required = array_merge($required, ['name', 'env']);
        parent::check_required($required);
    }
}