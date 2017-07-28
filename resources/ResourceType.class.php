<?php
namespace vcms\resources;

class ResourceType
{
    const PLAIN = 0;
    const WEB = 1;
    const REST = 2;

    /* inherited resource types */
    const V = 10;

    static function from_string (string $type): int
    {
        switch ($type) {
            case '':
            case 'Plain':
                return self::PLAIN;

            case 'Web':
                return self::WEB;

            case 'Rest':
                return self::REST;

            case 'V':
                return self::V;
        }
    }
}