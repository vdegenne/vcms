<?php
namespace vdegenne;

use Exception;

/**
 * The Website object is pretty much like the parent of the Page objects.
 * It contains general informations and options before the page in the general structure.
 *
 * Class Website
 * @package vdegenne
 */
class Website {

  const OPTIONS_FILENAME = 'options.json';
  
  
  /** @var string */
  private $name;
  /** @var Resource */
  private $Favicon;
  /** @var Stylesheet[] */
  private $Stylesheets = [];
  /** @var \vdegenne\Script[] */
  private $Scripts = [];

  /**
   * (To convert to an \vdegenne\Options object !)
   * @var Object Options of the website (including the global options of pages)
   */
  private $options;



  public function __construct ($name = null) {
    $this->name = $name;

    $this->load_options();
  }
  

  private function load_options () {
    if (!file_exists(self::OPTIONS_FILENAME)) {
      throw new Exception('The options file is absent');
    }

    $this->options = json_decode(file_get_contents(self::OPTIONS_FILENAME));
  }
  

  public function add_Stylesheet (Stylesheet $Stylesheet) {
    $GLOBALS['_Stylesheets'][] = $Stylesheet;
    $this->Stylesheets[] = $Stylesheet;
  }

  public function add_Script (Resource $Script) {
    $GLOBALS['_Scripts'][] = $Script;
    $this->Scripts[] = $Script;
  }

  public function set_Favicon (Resource $Favicon) {
    $this->Favicon = $Favicon;
  }

  /** @return \vdegenne\Script[] */
  public function get_Scripts () {
    return $this->Scripts;
  }

  /** @return \vdegenne\Resource */
  public function get_Favicon () {
    return $this->Favicon;
  }


  public function __get ($k) {
    return $this->{$k};
  }

  public function __set ($k, $v) {
    array_key_exists($k, get_object_vars($this)) && $this->{$k} = $v;
  }

}