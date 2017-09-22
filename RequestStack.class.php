<?php
namespace vcms;

use vcms\resources\Resource;

class RequestStack {

    public static $stack = [];

    static function push_request (Request $request, Resource $resource = null) {
        array_push(self::$stack, [$request, $resource, false]);
    }

    static function pop_request () : Array
    {
        return array_pop(self::$stack);
    }

    static function lock_resource () {
        self::$stack[count(self::$stack) - 1][2] = true;
    }

    static function set_last_request_response (Resource $resource) {
        self::$stack[count(self::$stack) - 1][1] = $resource;
    }

    static function is_stack_empty () : bool
    {
        return empty(self::$stack);
    }
}