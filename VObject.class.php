<?php
namespace vdegenne;

use ReflectionObject;
use ReflectionProperty;
use Exception;


class VObject {

  protected $readonlyProperties;

  public function __construct () {

    $this->readonlyProperties = array_map(function ($p) {
      return $p->name;
    }, (new ReflectionObject($this))->getProperties(
      ReflectionProperty::IS_PROTECTED));

  }


  function __get ($k) {
    if (array_search($k, $this->readonlyProperties) !== false) {
      throw new Exception('trying to modify a readonly property');
    }
  }

  function __set ($k, $v) {

    $readonlyProperties = array_map(function ($p) {
      return $p->name;
    }, (new ReflectionObject($this))->getProperties(
      ReflectionProperty::IS_PROTECTED));

    if (array_search($k, $readonlyProperties) !== false) {
      throw new Exception('can\'t modify a readonly property');
    }
  }
}