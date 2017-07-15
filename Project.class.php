<?php
namespace vcms;

require_once 'VcmsObject.class.php';



class Project extends VcmsObject
{
    const INCLUDES_DIRNAME = 'includes';

    /**
     * @var Array
     */
    private $include_dirpaths;
    /**
     * @var String
     * Location of the project. It is set automatically in the bootstrap
     */
    public $location;



    public function __construct() {}


    function add_include_dirpaths (...$dirpaths) {
        foreach ($dirpaths as $p)
        $this->include_dirpaths[] = $p;
    }

    function get_include_dirpaths() { return $this->include_dirpaths; }



}