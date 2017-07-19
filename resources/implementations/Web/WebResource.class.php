<?php
namespace vcms\resources\implementations;

use vcms\Request;
use vcms\resources\ResourceImpl;
use vcms\resources\ResourceType;


class WebResource extends ResourceImpl
{
    const LAYOUT_DIRPATH = '../layouts';

    protected $type = ResourceType::WEB;

    protected $metadatas;

    protected $structureFilepath = '../layouts/structure.php';

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
    }

}