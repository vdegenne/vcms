<?php
namespace vcms\resources\implementations;

use vcms\Request;
use vcms\resources\ResourceType;
use vcms\resources\ResourceImpl;


class RESTResource extends ResourceImpl
{

    protected $type = ResourceType::REST;


    function update () {
        $this->load_configuration();
        $this->resolve_content($this->Request->method);
    }

    function load_configuration () {
        parent::__load_configuration();
    }

    function resolve_content (string $contentFilename = null) {
        parent::__resolve_content($contentFilename);
    }
}