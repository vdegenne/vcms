<?php
namespace vdegenne;


class Feedback implements \JsonSerializable
{
    
    const ERROR = 0;
    const SUCCESS = 1;
    const REDIRECT = 2;

    /**
     * @var string
     */
    protected $message = '';
    /**
     * @var FeedbackType
     */
    protected $type = FeedbackType::SUCCESS;
    /**
     * @var mixed
     */
    protected $data = '';



    public function error (String $message, $data = '') {
        $this->type = self::ERROR;
        $this->message = $message;
        $this->data = $data;
        echo $this;
        exit(1);
    }

    public function success (String $message, $data = '') {
        $this->type = self::SUCCESS;
        $this->message = $message;
        $this->data = $data;
        echo $this;
        exit(1);
    }
  
  
    public function __get ($k) {
        return $this->$k;
    }

    public function __set ($k, $v) {
        $this->$k = $v;
    }

    function jsonSerialize () {
        return get_object_vars($this);
    }

    public function __toString (): String {
        return json_encode($this);
    }
}


class FeedbackType {
    const ERROR = 0;
    const SUCCESS = 1;
}