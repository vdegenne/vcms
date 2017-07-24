<?php
namespace vcms\resources\implementations;

use vcms\Response;
use vcms\Request;
use vcms\VcmsObject;
use Exception;

class Resource extends VcmsObject
{
    const REPO_DIRPATH = 'resources';
    public static $REPO_DIRPATH = 'resources';


    const RESOURCE_CONFIG_FILENAME = 'resource.json';

    /**
     * Location of the resource on disk.
     * @var string
     */
    protected $dirpath;

    /**
     * The filename of the content to process for the Response.
     * @var string
     */
    public $contentFilename;

    /**
     * The configuration Object of the Resource.
     * @var ResourceConfig
     */
    public $Config;

    /**
     * @var Response
     */
    public $Response;


    function __construct (string $dirpath = null, ResourceConfig $Config = null)
    {
        if ($dirpath !== null) {
            $this->dirpath = $dirpath;

            if ($Config === null) {
                $this->load_configuration();
            }
        }

        $this->Config = $Config;
        if ($this->Config === null) { /* little bit hacky but works */
            $classname = get_class($this) . 'Config';
            $this->Config = new $classname();
        }
        $this->Response = new Response();
    }



    protected function load_configuration ()
    {
        $this->Config=ResourceConfigFactory::create_config_object($this->dirpath);
    }

    function send ()
    {
        $this->process_response();
        $this->Response->send();
    }

    function process_response ()
    {
        global $Request, $Feedback;

        $this->Response->mimetype = $this->mimetype;


        if ($this->Config->get_params !== null) {
            if (!$Request::has_get($this->Config->get_params)) {
                $Feedback->failure('needs arguments');
            };
        }
        if ($this->Config->post_params !== null) {
            if (!$Request::has_post($this->Config->post_params)) {
                $Feedback->failure('needs arguments.');
            }
        }
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