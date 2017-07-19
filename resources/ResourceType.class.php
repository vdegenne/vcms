<?php
namespace vcms\resources;

class ResourceType
{
    const WEB = 0;
    const REST = 1;

    function __get($name)
    {
        switch (strtolower($name)) {
            case 'web':
                return self::WEB;
                break;
            case 'rest':
                return self::REST;
                break;
        }
    }


}