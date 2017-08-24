<?php
namespace vcms\resources;


class PlainResource extends Resource {

    function __construct ($dirpath = null, $Config = null)
    {
        parent::__construct($dirpath, new PlainResourceConfig());
    }

    function process_response (): string
    {
        $this->Response->content = file_get_contents($this->dirpath);

        return parent::process_response();
    }


}