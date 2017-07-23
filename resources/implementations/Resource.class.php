<?php
namespace vcms\resources\implementations;

use vcms\Request;
use vcms\Response;
use vcms\VcmsObject;
use Exception;

class Resource extends VcmsObject
{
    const REPO_DIRPATH = '../resources';
    public static $REPO_DIRPATH = '../resources';


    const RESOURCE_CONFIG_FILENAME = 'resource.json';

    /**
     * Location of the resource on disk.
     * @var string
     */
    public $dirpath;

    /**
     * @var Response
     */
    public $Response;


    function __construct ($dirpath = null)
    {
        if ($dirpath !== null) {
            $this->dirpath = $dirpath;
            $this->fetch_from_repo();
        }

        $this->Response = new Response();
    }


    public function fetch_from_repo ()
    {
        $this->load_configuration();
    }


    protected function load_configuration ()
    {
        $configFilepath = $this->dirpath . '/' . $this::RESOURCE_CONFIG_FILENAME;

        if (!file_exists($configFilepath)) {
            throw new Exception('the configuration file was not find.');
        }
    }



    function __set ($name, $value)
    {
        parent::__set($name, $value);

        switch ($name) {
            case 'dirpath':
            case 'REPO_DIRPATH':
                $this->fetch_from_repo();
                break;
        }
    }



}