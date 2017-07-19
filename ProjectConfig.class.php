<?php
namespace vcms;

require_once 'VcmsObject.class.php';


class ProjectConfig extends VcmsObject
{
    protected $name;

    protected $database;
    protected $needs_database;
}