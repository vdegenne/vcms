<?php
namespace vcms\resources\implementations;

class ResourceType
{
    const PLAIN = 0;
    const WEB = 1;
    const REST = 2;

    const TEST = 10; /* temporary */

    static function from_string (string $type): int
    {
        switch ($type) {
            case 'PLAIN':
                return self::PLAIN;
                break;
            case 'WEB':
                return self::WEB;
                break;
            case 'REST':
                return self::REST;
                break;

            case 'TEST':
                return self::TEST;
                break;

        }
    }
}