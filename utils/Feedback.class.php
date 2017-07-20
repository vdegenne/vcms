<?php
namespace vcms\utils;

use JsonSerializable;
use vcms\VcmsObject;


class Feedback extends VcmsObject
    implements JsonSerializable
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var bool
     */
    protected $success;

    /**
     * @var integer|string|array|null
     */
    protected $data;



    function fail (string $message, $data = null)
    {
        $this->success = false;
        $this->message = $message;
        $this->data = $data;
        echo $this;
    }

    function success (string $message, $data = null)
    {
        $this->success = true;
        $this->message = $message;
        $this->data = $data;
        echo $this;
    }
  

    function jsonSerialize () {
        return get_object_vars($this);
    }

    public function __toString (): string
    {
        return json_encode($this);
    }
}