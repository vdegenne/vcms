<?php
namespace vcms\resources;



class ResourceFactory
{
    /**
     * @param string $dirpath
     * @return Resource
     */
    static function create_resource_from_repo (string $dirpath): Resource
    {
        $Config = ResourceConfigFactory::load_config_object($dirpath);

        $classname = __NAMESPACE__ . '\\' . $Config->typeString . 'Resource';
        $Resource = new $classname($dirpath, $Config);

        return $Resource;
    }

}