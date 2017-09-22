<?php
namespace vcms;


class Session extends VObject {

    function __construct () { session_start(); }



    function get_user_classname() {
        global $Project;

        $sessionUserClassname = $Project->Config->session_user_classname;
        if ($sessionUserClassname === null) {
            $sessionUserClassname = 'vcms\User';
        }
        return $sessionUserClassname;
    }



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