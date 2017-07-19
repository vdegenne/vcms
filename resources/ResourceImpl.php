<?php
namespace vcms\resources;


use vcms\VcmsObject;

abstract class ResourceImpl extends VcmsObject
{

    /**
     * The REPO is the location of all the registered resources.
     * You can manually create your resource from an object.
     * Or you can persist and create one in the repo following the
     * Always see the dirpaths starting from the `www` directory.
     */
    const REPO_DIRPATH = '../resources';

    /**
     * without the extension.
     */
    const RESOURCE_CONTENT_FILENAME = 'content';

    const RESOURCE_CONFIG_FILENAME = 'resource.json';


    /**
     * The type of the resource.
     * The type is analysed in the bootstrap.
     * @var ResourceType
     */
    protected $type = ResourceType::WEB;

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
     * The processed content. In case of a php file.
     * @var string
     */
    protected $processedContent;


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
    public function update_impl ()
    {
        /* load the configuration file */
        $this->load_configuration();
        /* load the content file */
        $this->load_content();

        return true;
    }

    /* LOAD CONFIGURATION */
    abstract function load_configuration ();
    private function load_configuration_impl ()
    {
        $configFilepath = sprintf('%s/%s/%s',
            self::REPO_DIRPATH,
            $this->Request->requestURI,
            self::RESOURCE_CONFIG_FILENAME);

        if (!file_exists($configFilepath))
        {
            throw new Exception('configuration file not found.');
        }

        $this->Config = new ResourceConfig();
        Object::cast(json_decode(file_get_contents($configFilepath)), $this->Config);


        $this->type = (new ResourceType)->{$this->Config->type};
    }


    abstract function load_content ();
    private function load_content_impl ()
    {
        $contentFilepathGlob = sprintf('%s/%s/%s.*',
            self::REPO_DIRPATH,
            $this->Request->requestURI,
            self::RESOURCE_CONTENT_FILENAME
        );

        $globfiles = glob($contentFilepathGlob);

        if (count($globfiles) === 0) {
            throw new Exception('no content file.');
        }

        $this->contentFilepath = $globfiles[0];
        $this->content = file_get_contents($this->contentFilepath);
    }



    abstract function __set ();
    function __set_impl ($name, $value)
    {
        parent::__set($name, $value);

        switch ($name) {
            case 'contentFilepath':
                $this->update();
                break;
        }
    }

    public function preprocess () {

    }
}