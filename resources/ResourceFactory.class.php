<?php
namespace vcms\resources;



class ResourceFactory
{
    /**
     * @param string $dirpath
     * @return Resource
     */
    static function create_resource_from_repo (string $dirpath)
    {
        $Config = ResourceConfigFactory::create_config_object($dirpath);

        $classname = __NAMESPACE__ . '\\' . $Config->stringType . 'Resource';
        $Resource = new $classname($dirpath, $Config);

        return $Resource;
    }

}