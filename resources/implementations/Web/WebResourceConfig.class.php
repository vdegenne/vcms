<?php
namespace vcms\resources\implementations;


class WEBResourceConfig extends ResourceConfig
{
    public $mimetype = 'text/html';
    public $metadatas;


    function check_required (array $required=[])
    {
        $required=array_merge($required, ['metadatas']);
        parent::check_required($required);
    }


}