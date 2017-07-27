<?php
namespace vcms\resources;


use vcms\Config;

class ResourceConfig extends Config
{
    const RESOURCE_CONFIG_FILENAME = 'resource.json';

    /**
     * the mimetype of the resource.
     * @var string
     */
    public $mimetype;
}