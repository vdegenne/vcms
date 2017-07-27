<?php
namespace vcms\resources;


class TESTResourceConfig extends ResourceConfig
{
    public $new;



    function check_required (array $required = [])
    {
        $required = array_merge($required, ['new']);
        return parent::check_required($required);
    }
}