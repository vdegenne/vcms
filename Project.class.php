<?php
namespace vcms;


class Project extends ConfigurableObject
{

    const INCLUDES_DIRNAME = 'includes';

    /**
     * The singleton of the Project.
     * @var Project
     */
    static protected $Project;

    /**
     * Location of the project.
     * @var string
     */
    public $location;

    /**
     * Configuration Object of the Project.
     * @var ProjectConfig
     */
    public $Config;



    public function __construct ()
    {
        parent::__construct();

        $this->location = PROJECT_LOCATION;
        chdir($this->location);

        /* load the Project configurations */
        $this->load_configurations();

        /* needs to manage the exception and error handlers */
        if ($this->Config->env == 'dev') {
            ini_set('display_errors', 1);
            error_reporting(E_ALL | E_STRICT);
        }
    }

    static function get ()
    {
        if (self::$Project === null) {
            self::$Project = new Project();
        }
        return self::$Project;
    }



    private function load_configurations ()
    {
        $configFilepath = $this->location . '/' . ProjectConfig::CONFIGURATION_FILENAME;
        if (!file_exists($configFilepath)) {
            throw new \Exception('configuration file not found.');
        }

        $this->Config = ProjectConfig::construct_from_file($configFilepath);
    }





    function __set ($name, $value)
    {
        parent::__set($name, $value);

        switch ($name) {
            case 'location':
                $this->load_configurations();
                break;
        }
    }

}