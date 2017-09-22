<?php
namespace vcms;

class Analyzer {

    private static $wallclock;

    static function start() {
        self::$wallclock = microtime(true);
    }

    static function end() {
        return (microtime(true) - self::$wallclock);
    }
}