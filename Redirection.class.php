<?php
namespace vdegenne;


class Redirection {

  private $code;
  private $url;

  function __construct ($url, $code = null) {
    $this->url = $url;
    $this->code = $code;
  }

  function __set ($k, $v) {
    if (array_key_exists($k, get_object_vars($this))) {
      $this->{$k} = $v;
    }
  }
  function __get ($name) {
    return $this->{$name};
  }
}