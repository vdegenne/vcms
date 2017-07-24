<?php
namespace vcms\resources\implementations;

use Exception;

class ResourceFactory
{

    static function create_resource_from_repo (string $dirpath): Resource
    {
        $Config=ResourceConfigFactory::create_config_object($dirpath);

        $classname = __NAMESPACE__ . '\\' . $Config->stringType . 'Resource';
        $Resource = new $classname($dirpath, $Config);

        return $Resource;
    }

}