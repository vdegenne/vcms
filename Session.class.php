<?php
namespace vcms;

use vcms\VObject;

class Session extends VObject {

    function __construct () { session_start(); }

    function __set ($name, $value) {
        $_SESSION[$name] = $value;
    }

    function __isset ($name) {
        $isset = isset($_SESSION[$name]);
        if (!$isset) {
            return parent::__isset($name);
        }
        return $isset;
    }

    function __get ($name) { return $_SESSION[$name]; }

}