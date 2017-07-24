<?php
namespace vcms;

use Exception;


class Config
{

    function fill_the_blanks (Config $Config)
    {
        foreach (get_object_vars($Config) as $attname => $attvalue) {
            if ($this->{$attname} === null) {
                $this->{$attname} = $attvalue;
            }
        }
    }


    function process_attributes () {}

    function check_required (array $required = [])
    {
        $required = array_merge($required, ['type']);

        foreach ($required as $r) {
            if (!isset($this->{$r})) {
                throw new Exception("property \"$r\" missing from the configuration file.");
            }
        }
    }
}