<?php
namespace vcms;

/**
 * Class Domain
 * @package vdegenne
 *
 * La classe Domain permet principalement de faire la correspondance
 * entre les urls web et les urls locals
 */
class Domain {

    /** @var string */
    protected $name;
    /** @var int */
    protected $particles;
    /** @var Domain */
    protected $MasterDomain;
    /** @var string */
    protected $localPath;


    public function __construct ($name) {
        $this->name = $name;
        $this->update();
    }

    public function update () {
        $this->particles = explode('.', $this->name);
        $this->MasterDomain =  (count($this->particles) > 2)
                                ? new Domain($this->get_master_domain())
                                : null;
    }

    public function has_master_domain () {
        return !is_null($this->MasterDomain);
    }
    public function get_master_domain ($level = 1) {
        return implode('.', array_slice($this->particles, $level));
    }
    public function get_MasterDomain_object () { return $this->MasterDomain; }

    public function set_MasterDomain ($MDomain) {
        $this->MasterDomain = $MDomain;
    }

    public function set_name ($name) {
        $this->name = $name;

        $this->update();
    }

    public function set_localPath ($localPath) {
        $this->localPath = $localPath;
    }

    public function __get ($k) { return $this->{$k}; }
    public function __set ($k, $v) { array_key_exists($k, get_object_vars($this)) && $this->{$k} = $v; }
}