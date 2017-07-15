<?php

namespace vdegenne;


class Resource {

    /** @var string */
    protected $url;
    /** @var Domain */
    protected $Domain;
    /** @var bool */
    protected $inline;


    public function __construct ($url, Domain $Domain = null, $inline = true) {
        $this->url = $url;
        $this->Domain = $Domain;
        $this->inline = $inline;
    }


    public function set_Domain (Domain $Domain) {
        $this->Domain = $Domain;
    }

    public function has_Domain () { return !is_null($this->Domain); }

    public function get_localPath () {
        return $this->Domain->localPath . '/' . ltrim($this->url, '/');
    }

    public function get_url () {
        if (!is_null($this->Domain)) {
            return "http://{$this->Domain->name}/" . ltrim($this->url, '/');
        }
        else {
            return $this->url;
        }
    }

    public function is_inline () { return $this->inline; }
}