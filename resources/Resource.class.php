<?php
namespace vcms\resources;

use vcms\ConfigurableObject;
use vcms\RequestStack;
use vcms\Response;
use vcms\Request;
use vcms\VcmsObject;
use Exception;
use vcms\VObject;

class Resource extends ConfigurableObject {

    const GLOBAL_CONFIGURATION_FILENAME = 'inherit.json';

    /**
     * Content of the resource
     * @var string
     */
    public $content;

    /**
     * Location of the resource on disk.
     * @var string
     */
    protected $dirpath;


    /**
     * The configuration Object of the Resource.
     * @var ResourceConfig
     */
    public $Config;



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
    }


    protected function load_configuration ()
    {
        $this->Config = ResourceConfigFactory::load_config_object($this->dirpath);
    }

    function process () {}

    function use_as_response()
    {
        if (!RequestStack::is_stack_empty()) {
            RequestStack::set_last_request_response($this);
        }
    }

}