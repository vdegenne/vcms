<?php
namespace vcms\resources\implementations;

use vcms\resources\ResourceException;
use vcms\utils\Object;
use Exception;

class ResourceConfigFactory
{
    static function create_config_object (string $configPath): ResourceConfig
    {
        if (!is_file($configPath)) {
            $configPath=$configPath . '/' . Resource::RESOURCE_CONFIG_FILENAME;
        }

        if (!file_exists($configPath)) {
            throw new ResourceException('no configuration file was found', 2);
        }

        $ConfigStdClass = json_decode(file_get_contents($configPath));

        if (!isset($ConfigStdClass->type)) {
            $ConfigStdClass->type = '';
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
        $Config->process_attributes();

        return $Config;
    }
}