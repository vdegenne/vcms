<?php
namespace vcms\resources\implementations;

use vcms\VcmsObject;


class ResourceConfig extends VcmsObject
{
    protected $type;

    protected $needs_database;
    protected $database;
}