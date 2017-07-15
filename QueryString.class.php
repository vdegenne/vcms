<?php

namespace vdegenne;

class QueryString
{
    
    /** @var array */
    protected $arguments = [];


    
    public function __construct($arguments = []) {
        $this->arguments = $arguments;
    }

    public function to_string() {
        return http_build_query($this->arguments);
    }

    public function __toString() {
        return http_build_query($this->arguments);
    }

    static public function http_build_query(array $params) {
        $query = http_build_query($params); // php pre-built function
        return preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $query);
    }


    public function add_argument(array $arguments) {
        foreach ($arguments as $argKey => $argVal) {
            $this->arguments[$argKey] = $argVal;
        }
    }

    public function delete_argument($value) {
        if (array_key_exists($value, $this->arguments)) {
            unset($this->arguments[$value]);
        }
    }

    public function has(...$params) {

        $paramsCount = 0;
        
        foreach ($params as $p) {            
            if (array_key_exists($p, $this->arguments)) {
                $paramsCount++;
            }
        }

        if ($paramsCount == count($params)) {
            return true;
        }
        else {
            return false;
        }
    }
    
    public function get($key) {
        if ($this->has($key)) {
            return $this->arguments[$key];
        } else
            return false;
    }
  
    public function __get($k) {
        if ($this->has($k)) {
            return $this->arguments[$k];
        }
        return $this->{$k};
    }

    public function get_arguments() {
        return $this->arguments;
    }

}