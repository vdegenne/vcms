<?php
namespace vcms\resources\implementations;


class FEEDBACKResource extends Resource
    implements \JsonSerializable
{
    /**
     * The message of the Feedback
     * @var string
     */
    public $message;

    /**
     * If the Feedback is a success or a failure.
     * @var bool
     */
    public $success;

    /**
     * The data to send back.
     * @var mixed|null
     */
    public $data;


    function success ($message, $data = null) {
        $this->success = true;
        $this->message = $message;
        $this->data = $data;
        $this->send();
    }
    function failure ($message, $data = null) {
        $this->success = false;
        $this->message = $message;
        $this->data = $data;
        $this->send();
    }

    function process_response ()
    {
        parent::process_response();
        $this->Response->content=json_encode($this);
    }

    function jsonSerialize ()
    {
        return [
            'message'=>$this->message,
            'success'=>$this->success,
            'data'=>$this->data
        ];
    }
}