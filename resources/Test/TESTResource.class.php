<?php
namespace vcms\resources;


class TestResource extends Resource
{
    function process_response ()
    {
        $this->Response->content = 'test';
    }
}