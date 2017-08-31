<?php
namespace vcms;

use vcms\utils\Object;

require_once 'ProjectConfig.class.php';
require_once 'VObject.class.php';
require_once 'Object.class.php';
require_once __DIR__ . '/ConfigurableObject.class.php';



class Project extends ConfigurableObject
{

    const INCLUDES_DIRNAME = 'includes';

    /**
     * The singleton of the Project.
     * @var Project
     */
    static protected $Project;

    /**
     * @var Array
     */
    protected $include_dirpaths;

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

        $this->location = dirname(getcwd());
        chdir($this->location);
        define('PROJECT_LOCATION', $this->location);
        /*
         * The include dirpaths are used for the autoloader.
         * The autoloader will automatically search for the classes
         * to include from these directories (recursively)
         */
        $this->add_include_dirpaths(__DIR__);
        $this->add_include_dirpaths($this->location . '/' . Project::INCLUDES_DIRNAME);

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



    function add_include_dirpaths (...$dirpaths)
    {
        foreach ($dirpaths as $p) {
            set_include_path($p);
            $this->include_dirpaths[] = $p;
        }
    }

    function get_include_dirpaths ()
    {
        return $this->include_dirpaths;
    }





    private function load_configurations ()
    {
        $configFilepath = $this->location . '/' . ProjectConfig::CONFIGURATION_FILENAME;
        if (!file_exists($configFilepath)) {
            throw new \Exception('configuration file not found.');
        }

//        $json = json_decode(file_get_contents($configFilepath));
//        $this->Config = Object::cast($json, '\vcms\ProjectConfig');
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