<?php
namespace vcms\database;

use vcms\VObject;

class DatabaseEntity extends VObject
{
    public $trackedVars = [];

    public function update($name, $value) {
        $this->trackedVars[] = $name;
        $this->$name = $value;
    }

    public function reset_tracked_vars () {
        $this->trackedVars = [];
    }
}