<?php
namespace vcms;


class Session extends VObject {

    function __construct () { session_start(); }



    function get_session_user_object() {
        global $Resource;

        $sessionUserObject = $Resource->Config->session_user_object;
        if ($sessionUserObject === null) {
            $sessionUserObject = 'vcms\User';
        }
        return $sessionUserObject;
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