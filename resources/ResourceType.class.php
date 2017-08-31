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
        switch (strtolower($type)) {
            case '':
            case 'plain':
                return self::PLAIN;

            case 'web':
                return self::WEB;

            case 'rest':
                return self::REST;

            case 'v':
                return self::V;
        }
    }
}