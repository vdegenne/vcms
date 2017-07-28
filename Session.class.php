<?php
namespace vcms;


class Session extends VObject
{
    /**
     * @var User
     */
    protected $User;


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
        if (array_key_exists($name, get_object_vars($this))) {
            $this->{$name} = $value;
            $_SESSION[$name] = $this->{$name};
        }
    }


    function __get ($name)
    {
        return $this->{$name};
    }
}