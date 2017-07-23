<?php
namespace vcms\resources\implementations;

use vcms\utils\Object;
use Exception;

class ResourceConfigFactory
{
    static function create_config_object (string $resourceDirpath): ResourceConfig
    {
        $configFilepath = $resourceDirpath . '/' . Resource::RESOURCE_CONFIG_FILENAME;

        if (!file_exists($configFilepath)) {
            throw new Exception('no configuration file was found');
        }

        $ConfigStdClass = json_decode(file_get_contents($configFilepath));

        if (!isset($ConfigStdClass->type)) {
            throw new Exception('missing type in the configuration file');
        }

         $ConfigStdClass->type = strtoupper($ConfigStdClass->type);

        try {
            $classname = __NAMESPACE__ . '\\' . $ConfigStdClass->type . 'ResourceConfig';
            $Config = new $classname();

            /** If the class is not found, the exception is useless because a FATAL ERROR
             * takes place.
             * We need to complexify the error handlers.
             * https://insomanic.me.uk/php-trick-catching-fatal-errors-e-error-with-a-custom-error-handler-cea2262697a2
             */
        }
        catch (Exception $e) {
            throw new Exception('this type of resource is not implemented');
        }

        Object::cast($ConfigStdClass, $Config);

        /* check if required attributes are in the configuration file */
        $Config->check_required();

        return $Config;
    }
}