<?php
namespace vcms;

require_once 'VcmsObject.class.php';



class Project extends VcmsObject
{
    const INCLUDES_DIRNAME = 'includes';
    const CONFIGURATION_FILENAME = 'config.json';

    /**
     * @var Array
     */
    protected $include_dirpaths;
    /**
     * @var String
     * Location of the project. It is set automatically in the bootstrap
     */
    protected $location;

    protected $configJson;



    public function __construct() {

    }

    function add_include_dirpaths (...$dirpaths) {
        foreach ($dirpaths as $p)
        $this->include_dirpaths[] = $p;
    }

    function get_include_dirpaths() { return $this->include_dirpaths; }

    private function update () {
        $configFilepath = $this->location . '/' . self::CONFIGURATION_FILENAME;
        if (!file_exists($configFilepath)) {
            throw new \Exception('configuration file not found.');
        }

        $this->configJson = json_decode(file_get_contents($configFilepath), true);
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