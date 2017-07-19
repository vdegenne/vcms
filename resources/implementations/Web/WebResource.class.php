<?php
namespace vcms\resources\implementations;

use vcms\Request;
use vcms\resources\ResourceImpl;
use vcms\resources\ResourceType;


class WebResource extends ResourceImpl
{
    const LAYOUT_DIRPATH = '../layouts';
    const HEAD_FILENAME = 'head.php';

    protected $type = ResourceType::WEB;

    protected $metadatas;

    protected $structureFilepath = '../layouts/structure.php';

    protected $headFilepath;

    function __construct (Request $Request = null)
    {

    }

    function update () {
        parent::__update();
    }

    function load_configuration () {
        parent::__load_configuration();

        $this->metadatas = $this->Config->metadatas;
    }

    function resolve_content (string $contentFilename = null) {
        parent::__resolve_content($contentFilename);

        $this->headFilepath = sprintf('%s/%s/%s',
            $this::REPO_DIRPATH,
            $this->Request->requestURI,
            $this::HEAD_FILENAME
        );
    }

}