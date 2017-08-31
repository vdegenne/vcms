<?php
namespace vcms;

use Exception;
use vcms\utils\Object;


class Config
{

    static function construct_from_file ($json) : Config
    {

        if (gettype($json) === 'string') {
            if (!file_exists($json)) {
                throw new Exception("\"Can't create the Config Object ($json not found)\"");
            }
            $json = json_decode(file_get_contents($json));
        }


        /**
         * TODO: we should check deep for dash to transform
         */
        $preObj = new \StdClass();
        foreach (get_object_vars($json) as $k => $v) {
            $k = str_replace('-', '_', $k);
            $preObj->{$k} = $v;
        }

        /** @var Config $Config */
        $Config = Object::cast($preObj, get_called_class());
        $Config->process_attributes();

        return $Config;
    }

    function fill_the_blanks (Config $Config)
    {
        foreach (get_object_vars($Config) as $attname => $attvalue) {
            if ($this->{$attname} === null) {
                $this->{$attname} = $attvalue;
            }
        }
    }

    function replace_not_nulls (Config $Config)
    {
        foreach (get_object_vars($this) as $attname => $attvalue) {
            if ($this->{$attname} !== null && isset($Config->{$attname})) {
                $this->{$attname} = $Config->{$attname};
            }
        }
    }

    function process_attributes () {}

    function check_required (array $required = [])
    {
        $required = array_merge($required, []);

        foreach ($required as $r) {
            if (!isset($this->{$r})) {
                throw new Exception("property \"$r\" missing from the configuration file.");
            }
        }
    }
}