<?php
namespace vcms\resources;

use vcms\ConfigurableObject;
use vcms\Response;
use vcms\Request;
use vcms\VcmsObject;
use Exception;
use vcms\VObject;

class Resource extends ConfigurableObject
     implements \JsonSerializable {

    const GLOBAL_CONFIGURATION_FILENAME = 'inherit.json';

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
        $this->Config = $Config;

        /* it means the resource was made manually
           but we expect it to be loaded from a repository
           if no Config Object is provided */
        if ($dirpath !== null) {
            $this->dirpath = $dirpath;

            if ($Config === null) {
                $this->load_configuration();
            }
        }

        /* we prepare a Config Object if none is provided */
        if ($this->Config === null) {
            $classname = get_class($this) . 'Config';
            $this->Config = new $classname();
        }

        $this->Response = new Response();
    }



    protected function load_configuration ()
    {
        $this->Config = ResourceConfigFactory::load_config_object($this->dirpath);
    }


    function dump_json ()
    {
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

    function process_response (): string
    {
        $this->Response->mimetype = $this->mimetype;

        /**
         * We make sure there is not a running buffer
         * as processing Resources can embedded other
         * Resources that possibly could stress send.
         */
        //        @ob_end_clean();
        return $this->Response->content;
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