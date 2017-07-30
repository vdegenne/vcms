<?php
namespace vcms\resources;

use vcms\ConfigurableObject;
use vcms\Response;
use vcms\Request;
use vcms\VcmsObject;
use Exception;
use vcms\VObject;

class Resource extends ConfigurableObject
    implements \JsonSerializable
{


    /**
     * Location of the resource on disk.
     * @var string
     */
    protected $dirpath;

    /**
     * The filename of the content to process for the Response.
     * @var string
     */
    // public $contentFilename;

    /**
     * The configuration Object of the Resource.
     * @var ResourceConfig
     */
    public $Config;

    /**
     * @var Response
     */
    public $Response;


    function __construct (string $dirpath = null, $Config = null)
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


    function dump_json () {
        //$this->process_response();
        $this->Response->content = json_encode($this, JSON_PRETTY_PRINT);
        $this->Response->mimetype = 'application/json';
        $this->Response->send();
    }

    function send ()
    {
        $this->process_response();
        $this->Response->send();
    }

    function process_response ()
    {
        $this->Response->mimetype = $this->mimetype;

        /**
         * We make sure there is not a running buffer
         * as processing Resources can embedded other
         * Resources that possibly could stress send.
         */
        @ob_end_clean();
    }


//    function __get ($name)
//    {
//        if (!array_key_exists($name, get_object_vars($this))) {
//            if (array_key_exists($name, get_object_vars($this->Config))) {
//                return $this->Config->{$name};
//            }
//        }
//        return parent::__get($name);
//    }
//
//
//
//    function __set ($name, $value)
//    {
//        parent::__set($name, $value);
//
//        switch ($name) {
//            case 'dirpath':
//            case 'REPO_DIRPATH':
//                $this->fetch_from_repo();
//                break;
//        }
//    }

    function jsonSerialize ()
    {
        return get_object_vars($this);
    }
}