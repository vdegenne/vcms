<?php
namespace vcms;


class Project
{
    const INCLUDES_DIRNAME = 'includes';

    private $include_dirpaths;

    public function __construct() {}


    function add_include_dirpaths (...$dirpaths) {
        foreach ($dirpaths as $p)
        $this->include_dirpaths[] = $p;
    }

    function get_include_dirpaths() { return $this->include_dirpaths; }
}