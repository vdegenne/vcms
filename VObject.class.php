<?php
namespace vcms;


class VObject
{

    protected $readonlys = [];

    function __construct ()
    {
        $this->readonlys = array_map(
            function ($p) { return $p->name; },
            (new \ReflectionObject($this))
                ->getProperties(\ReflectionProperty::IS_PROTECTED)
        );
    }

    function __get ($name)
    {
        if (array_search($name, $this->readonlys) !== false) {
            throw new \Exception('trying to modify a readonly property');
        }
        return $this->$name;
    }

    function __set ($name, $value)
    {
        if (array_key_exists($name, get_object_vars($this)) !== false) {
            $this->$name = $value;
        }
    }


    function get_last_child_publics (): array {
        $publics = [];

        $classInfo = new \ReflectionClass($this);
        foreach ($classInfo->getProperties() as $prop) {
            if ($prop->isPublic() && $prop->getDeclaringClass()->name === $classInfo->name) {
                $publics[$prop->name] = $this->{$prop->name};
            }
        }

        return $publics;
    }
}