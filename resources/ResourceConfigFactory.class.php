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

        $ConfigStdClass = json_decode(file_get_contents($_configPath));


        if ($resourceType !== null) {
            $ConfigStdClass->type = $resourceType;
        }
        if (!isset($ConfigStdClass->type)) {
            $ConfigStdClass->type = '';
        }

        /** @var Config $Config */
        $Config = null;
        try {
            if (!empty($ConfigStdClass->type)) {
                $ConfigStdClass->type = strtolower($ConfigStdClass->type);
                $ConfigStdClass->type[0] = strtoupper($ConfigStdClass->type[0]);
            }
            $classname = __NAMESPACE__ . '\\' . $ConfigStdClass->type . 'ResourceConfig';
            $Config = new $classname();

            /** If the class is not found, the exception is useless because a FATAL ERROR
             * takes place.
             * We need to complexify the error handlers.
             * https://insomanic.me.uk/php-trick-catching-fatal-errors-e-error-with-a-custom-error-handler-cea2262697a2
             */
        } catch (Exception $e) {
            throw new \Exception('this type of resource is not implemented');
        }

        Object::cast($ConfigStdClass, $Config);


        /* we should implement the global configuration merging here */
        // from dirpath to PROJECT LOCATION
        // we check if there is a GLOBAL_CONFIGURATION_FILENAME file in the current location
        // then we take that content and fill_the_blanks with the current resource
        // we loop the process until the end
        if ($resourceType !== 'V') {
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