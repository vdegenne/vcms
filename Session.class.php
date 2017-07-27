<?php
namespace vcms;


class Session extends VObject
{
    /**
     * @var User
     */
    public $User;


    static function open (): Session
    {
        session_start();

        /* get the saved properties back */
        $Session = new Session();

        foreach (get_object_vars($Session) as $propName => $propValue) {
            if (@$_SESSION[$propName]) {
                $Session->{$propName} = $_SESSION[$propName];
            }
        }

        return $Session;
    }

    function __set ($name, $value)
    {
        parent::__set($name, $value);

        if (array_key_exists($name, get_class_vars(Session::class)) !== false) {
            $_SESSION[$name] = $this->{$name};
        }
    }


}