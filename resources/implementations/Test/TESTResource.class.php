<?php
namespace vcms\resources\implementations;


class TestResource extends Resource
{
    function process_response ()
    {
        $this->Response->content = 'test';
    }
}