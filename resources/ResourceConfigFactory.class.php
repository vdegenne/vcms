<?php
namespace vcms\resources;


use vcms\Config;
use vcms\FileSystem;
use vcms\utils\Object;


class ResourceConfigFactory {
    const RESOURCE_CONFIG_FILENAME = 'resource.json';

    /**
     * @param string $configPath
     * @return Config
     * @throws ResourceException
     * @throws \Exception
     */
    static function load_config_object (string $configPath, string $resourceType = null)
    {
        $pathIsFilepath = isset(pathinfo($configPath)['extension']);
        $_configPath = $configPath;

        if (!$pathIsFilepath) {
            $_configPath = $configPath . '/' . self::RESOURCE_CONFIG_FILENAME;
        }

        if (!file_exists($configPath)) {
            throw new ResourceException("$_configPath configuration file not found", 2);
        }

        /*
         * we should determine the type first
         */
        $type = null;
        $json = null;
        if ($resourceType !== null) {
            $type = $resourceType;
            $json = $_configPath;
        }
        elseif ($json = json_decode(file_get_contents($_configPath))) {
            $type = $json->type === null ? '' : $json->type;
        }
        $type = strtolower($type);
        if ($type !== '') {
            $type[0] = strtoupper($type[0]);
        }
        // class to use for this requested Configuration Object
        $ConfigClass = __NAMESPACE__ . '\\' . $type . 'ResourceConfig';


        $Config = null;
        $Config = $ConfigClass::construct_from_file($json);
        $Config->type = $type;


        /* we should implement the global configuration merging here */
        // from dirpath to PROJECT LOCATION
        // we check if there is a GLOBAL_CONFIGURATION_FILENAME file in the current location
        // then we take that content and fill_the_blanks with the current resource
        // we loop the process until the end

        /* we should implement a mechanism in inherit.json to stop filling the blanks
           if the current inherit.json has a property called stop-inherit set to true */
        if ($type !== 'V') {
            $currentPath = $configPath;
            if ($pathIsFilepath) {
                $currentPath = FileSystem::one_folder_up($currentPath);
            }
            while ($currentPath !== '') {
                $filepath = $currentPath . '/' . Resource::GLOBAL_CONFIGURATION_FILENAME;
                if (file_exists($filepath)) {
                    $inheritConfig = self::load_config_object($filepath, 'V');
                    $Config->fill_the_blanks($inheritConfig);
                }
                $currentPath = FileSystem::one_folder_up($currentPath);
            }
        }

        /* check if required attributes are in the configuration file */
        $Config->check_required();
        $Config->process_attributes();

        return $Config;
    }
}