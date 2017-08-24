<?php
namespace vcms;

use vcms\VObject as Object;

class ConfigurableObject extends Object
{
    public $Config;


    function __get ($name)
    {
        if (!array_key_exists($name, get_object_vars($this))) {
            if (array_key_exists($name, get_object_vars($this->Config))) {
                return $this->Config->{$name};
            }
        }
        return parent::__get($name);
    }
}