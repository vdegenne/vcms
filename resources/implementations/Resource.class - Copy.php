<?php
namespace vcms\resources\implementations;


use vcms\Request;
use vcms\VcmsObject;
use vcms\utils\Object;
use Exception;


class Resource extends VcmsObject
{

    /**
     * The REPO is the location of all the registered resources.
     * You can manually create your resource from an object.
     * Or you can persist and create one in the repo.
     * Always see the dirpaths starting from the `www` directory.
     */
    const REPO_DIRPATH = '../resources';

    const RESOURCE_CONTENT_FILENAME = 'content'; /* without the extension */

    const RESOURCE_CONFIG_FILENAME = 'resource.json';


    /**
     * The type of the resource.
     * The type is analysed in the bootstrap.
     * @var ResourceType
     */
    protected $type = ResourceType::WEB; /* default */

    /**
     * The request which invoked the Resource.
     * Or null if Resource was manually created.
     * @var Resource|null
     */
    protected $Request;

    /**
     * The configuration object of this resource.
     * @var ResourceConfig
     */
    protected $Config;

    /**
     * filepath of the resource content.
     * @var string
     */
    protected $contentFilepath;

    /**
     * The raw content as fetched in the local file of the resource.
     * @var string
     */
    protected $content;


    /**
     * Either the resource was found or not.
     * null if the resource is not fetched from the REPO.
     * @var boolean|null
     */
    protected $exists = null;



    function __construct(Request $Request = null)
    {
        if ($Request !== null) {
            $this->Request = $Request;
            $this->update();
        }
    }


    /* UPDATE */
    abstract function update ();
    public function __update ()
    {
        /* load the configuration file */
        $this->load_configuration();
        /* load the content file */
        $this->resolve_content(null);
    }


    /* LOAD CONFIGURATION */
    abstract function load_configuration ();
    protected function __load_configuration ()
    {
        $configFilepath = sprintf('%s/%s/%s',
            $this::REPO_DIRPATH,
            $this->Request->requestURI,
            $this::RESOURCE_CONFIG_FILENAME);

        if (!file_exists($configFilepath))
        {
            throw new Exception('configuration file not found.');
        }

        /* get the type of the resource */
        $jsonConfig = json_decode(file_get_contents($configFilepath));

        try {
            $ResourceConfigClassName = ('vcms\\resources\\implementations\\'. $jsonConfig->type . 'ResourceConfig');
            $Config = new $ResourceConfigClassName;
        }
        catch (Exception $e) {
            throw new Exception ('property "type" not declared in the configuration.');
        }


        if ($this->Config !== null) {
            $props = (new \ReflectionObject($this->Config))->getProperties(\ReflectionProperty::IS_PROTECTED);
            foreach (get_object_vars($jsonConfig) as $propName => $propValue) {
                $this->Config->{$propName} = $propValue;
            }
        }
        else {
            $this->Config = $Config;
            Object::cast($jsonConfig, $this->Config);
        }

    }


    abstract function resolve_content (string $contentFilename = null);
    protected function __resolve_content (string $contentFilename = null)
    {
        $contentFilepathGlob = sprintf('%s/%s/%s*',
            $this::REPO_DIRPATH,
            $this->Request->requestURI,
            ($contentFilename !== null) ? $contentFilename : $this::RESOURCE_CONTENT_FILENAME
        );

        $globfiles = glob($contentFilepathGlob);

        $this->exists = (count($globfiles) > 0);

        if ($this->exists) {
            $this->contentFilepath = $globfiles[0];
        }
    }



    function __set ($name, $value)
    {
        parent::__set($name, $value);

        switch ($name) {
            case 'Request':
            case 'contentFilepath':
                $this->update();
                break;
        }
    }

    public function preprocess () {

    }


    static function getResource ($type, $Configs = null) {
        switch ($type) {
            case ResourceType::REST:
                $R = new RESTResource();
                $C = new RESTResourceConfig();
                break;
            case ResourceType::WEB:
                $R = new WebResource();
                $C = new WebResourceConfig();
                break;
            // ...
        }

        if ($Configs !== null) {
            $props = (new \ReflectionObject($C))->getProperties(\ReflectionProperty::IS_PROTECTED);
            foreach ($props as $prop) {
                if (@$Configs->{$prop->name} !== null && $C->{$prop->name} === null) {
                    $C->{$prop->name} = $Configs->{$prop->name};
                }
            }
            $R->Config = $C;
        }

        return $R;
    }

}