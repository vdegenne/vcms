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


    function add_arguments (array $args)
    {
        foreach ($args as $argKey => $argVal) {
            $this->arguments[$argKey] = $argVal;
        }
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



    function get($key)
    {
        if ($this->has($key)) {
            return $this->arguments[$key];
        } else
            return false;
    }



    function __get($name)
    {
        if ($this->has($name)) {
            return $this->arguments[$name];
        }
        // else
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