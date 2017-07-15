<?php

namespace vdegenne;

class User implements \JsonSerializable {

  protected $id;
  protected $username;
  protected $email;
  protected $password;
  protected $isAuthenticated;
  protected $hreflang;


  public function __construct() {

  }

  public function __get($k) { return $this->{$k}; }
  public function __set($k, $v) {
    array_key_exists($k, get_object_vars($this)) && $this->{$k} = $v;
  }


    
  public function jsonSerialize() {
    return get_object_vars($this);
  }
}