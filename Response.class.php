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
        echo $this->Resource->content;
    }
}