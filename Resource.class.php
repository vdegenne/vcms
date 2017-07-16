<?php

namespace vcms;


class Resource extends VcmsObject
{
    /**
     * The REPO is the location of all the registered resources.
     * You can manually create your resource from an object.
     * Or you can persist and create one in the repo following the
     * Always see the dirpaths starting from the `www` directory.
     */
    const REPO_DIRPATH = '../resources';

    const RESOURCE_CONTENT_FILENAME = 'content.php';


    /**
     * The request which invoked the Resource.
     * Or null if Resource was manually created.
     * @var Resource|null
     */
    protected $Request;

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


    function __construct (Request $Request = null)
    {
        if ($Request !== null) {
            $this->Request = $Request;
            $this->update();
        }
    }


    private function update ()
    {
        $this->contentFilepath = sprintf(
            self::REPO_DIRPATH.'/%s/'.self::RESOURCE_CONTENT_FILENAME,
            $this->Request->requestURI
        );

        if (($this->exists = file_exists($this->contentFilepath)) === false)
        {
            return;
        }
    }


    function __set($name, $value)
    {
        parent::__set($name, $value);

        switch ($name) {
            case 'contentFilepath':
                $this->update();
                break;
        }
    }


}