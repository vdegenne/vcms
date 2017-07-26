<?php
namespace vcms;


class QueryString extends VcmsObject
{
    
    /**
     * @var array
     */
    protected $arguments = [];




    public function __construct ($arguments = [])
    {
        $this->arguments = $arguments;
    }


    // see __toString
//    public function to_string () {
//        return http_build_query($this->arguments);
//    }


    function add_arguments (string $argName, string $argValue)
    {
        $this->arguments[$argName] = $argValue;
    }


    function delete_argument ($arg) : bool
    {
        if (array_key_exists($arg, $this->arguments)) {
            unset($this->arguments[$arg]);
            return true;
        }
        return false;
    }


    function has(...$params)
    {
        $paramsCount = 0;

        foreach ($params as $p) {
            if (array_key_exists($p, $this->arguments)) {
                $paramsCount++;
            }
        }

        return ($paramsCount == count($params));
    }

    function __isset ($name)
    {
        if (!array_key_exists($name, array_keys(get_object_vars($this)))) {
            return $this->has($name);
        }
        return isset($name);
    }







    function __get($name)
    {
        if ($this->has($name)) {
            return $this->arguments[$name];
        }
        return parent::__get($name);
    }



    function __toString ()
    {
        return http_build_query($this->arguments);
    }


    static function http_build_query (array $params) : string
    {
        $query = http_build_query($params); // php pre-built function
        return preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $query);
    }
}