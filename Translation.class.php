<?php
namespace vdegenne;


class Translation {

  private $id;
  private $page;
  private $tagId;
  private $trans;
  private $hreflang;

  function __set ($k, $v) {
    if (array_key_exists($k, get_object_vars($this))) {
      $this->{$k} = $v;
    }
  }
  function __get ($name) {
    return $this->{$name};
  }
}