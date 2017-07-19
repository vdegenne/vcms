<?php
namespace vcms\resources\implementations;

use vcms\resources\Resource;
use vcms\resources\ResourceType;
use vcms\resources\ResourceTypeImpl;


class RESTResource extends Resource
    implements ResourceTypeImpl
{
    protected $type = ResourceType::REST;



    public function preprocess ()
    {

    }

}