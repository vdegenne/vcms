<?php
namespace vcms\resources;


class PlainResource extends Resource {

    function __construct ($dirpath = null, $Config = null)
    {
        parent::__construct($dirpath, new PlainResourceConfig());
    }

    function process_response ()
    {
        parent::process_response();

        $this->Response->content = file_get_contents($this->dirpath);
    }


}