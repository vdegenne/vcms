<?php
namespace vcms\resources\implementations;

use Exception;

class ResourceFactory
{

    static function create_resource (int $type): Resource
    {
        switch ($type) {
            case ResourceType::PLAIN:
                return new Resource();
                break;

            case ResourceType::TEST:
                return new TestResource();
                break;
            default:
                throw new Exception('invalid resource type.');
        }
    }

}