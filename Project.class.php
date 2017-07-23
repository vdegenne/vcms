<?php
namespace vcms;

use vcms\utils\Object;

require_once 'ProjectConfig.class.php';
require_once 'VcmsObject.class.php';
require_once 'Object.class.php';


class Project extends VcmsObject
{
    const INCLUDES_DIRNAME = 'includes';
    const CONFIGURATION_FILENAME = 'project.json';


    /**
     * @var Array
     */
    protected $include_dirpaths;

    /**
     * Location of the project. It is set automatically in the bootstrap
     * @var string
     */
    protected $location;

    /**
     * Configuration Object of the Project.
     * @var ProjectConfig
     */
    protected $Config;



    public function __construct() {

    }

    function add_include_dirpaths (...$dirpaths) {
        foreach ($dirpaths as $p)
        $this->include_dirpaths[] = $p;
    }

    function get_include_dirpaths () { return $this->include_dirpaths; }

    private function update ()
    {
        $configFilepath = $this->location . '/' . self::CONFIGURATION_FILENAME;
        if (!file_exists($configFilepath)) {
            throw new \Exception('configuration file not found.');
        }

        $this->Config = Object::cast(json_decode(file_get_contents($configFilepath)), '\\vcms\\ProjectConfig');
    }

    function __get ($name)
    {
        if (!array_key_exists($name, get_object_vars($this))) {
            if (array_key_exists($name, get_object_vars($this->Config))) {
                return $this->Config->{$name};
            }
        }
        return parent::__get($name);
    }



    function __set($name, $value)
    {
        parent::__set($name, $value);

        switch ($name) {
            case 'location':
                $this->update();
                break;
        }
    }

}