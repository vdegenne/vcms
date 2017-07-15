<?php
namespace vcms;

class VcmsObject {


    function __get($name)
    {
        return $this->$name;
    }

    function __set($name, $value)
    {
        if (array_key_exists($name, get_object_vars($this)) !== false) {
            $this->$name = $value;
        }
    }
}