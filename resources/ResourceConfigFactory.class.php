<?php
namespace vcms\resources;


use vcms\utils\Object;


class ResourceConfigFactory
{
    const RESOURCE_CONFIG_FILENAME = 'resource.json';

    /**
     * @param string $configPath
     * @return Config
     * @throws ResourceException
     * @throws \Exception
     */
    static function create_config_object (string $configPath, string $resourceType = null)
    {
        if (!isset(pathinfo($configPath)['extension'])) {
            $configPath=$configPath . '/' . self::RESOURCE_CONFIG_FILENAME;
        }

        if (!file_exists($configPath)) {
            throw new ResourceException("$configPath configuration file not found", 2);
        }

        $ConfigStdClass = json_decode(file_get_contents($configPath));


        if ($resourceType !== null) {
            $ConfigStdClass->type = $resourceType;
        }
        if (!isset($ConfigStdClass->type)) {
            $ConfigStdClass->type = '';
        }


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
        }
        catch (Exception $e) {
            throw new \Exception('this type of resource is not implemented');
        }

        Object::cast($ConfigStdClass, $Config);

        /* check if required attributes are in the configuration file */
        $Config->check_required();
        $Config->process_attributes();

        return $Config;
    }
}