<?php
namespace vcms;


class Response
{
    /**
     * Mimetype of the Response.
     * @var string
     */
    public $mimetype;

    /**
     * Content of the Response.
     * @var string
     */
    public $content;

    function send ()
    {
        header('content-type: ' . $this->mimetype);
        exit($this->content);
    }
}