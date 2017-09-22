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


    function success ($message = null, $data = null) {
        $this->use_as_response(true, $message, $data);
    }

    function failure ($message = null, $data = null) {
        $this->use_as_response(false, $message, $data);
    }

    function use_as_response (bool $success = null, string $message = null, $data = null)
    {
        if ($success !== null)
            $this->success = $success;

        if ($message !== null)
            $this->message = $message;

        if ($data !== null)
            $this->data = $data;


        $this->process();
        parent::use_as_response();
    }



    function process ()
    {
        $this->content = json_encode([
            'message' => $this->message,
            'data' => $this->data
        ]);
    }
}