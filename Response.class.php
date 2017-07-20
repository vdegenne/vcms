<?php
namespace vcms\Response;


use vcms\Request;
use vcms\resources\ResourceImpl;
use vcms\VcmsObject;

class Response extends VcmsObject
{
    /**
     * @var Request
     */
    protected $Request;
    /**
     * @var ResourceImpl
     */
    protected $Resource;


    function send ()
    {
        header('Content-type: ' . $this->Resource->Config->mimetype);
        echo $this->Resource->content;
    }
}