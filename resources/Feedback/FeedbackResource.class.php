<?php
namespace vcms\resources;


class FeedbackResource extends Resource
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

    function process_response (): string
    {
        $this->Response->content=json_encode($this->get_last_child_publics());
        return parent::process_response();
    }
}