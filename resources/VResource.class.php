<?php
namespace vcms\resources;


class VResource extends Resource
{
    const REPO_DIRPATH = 'pages';
    // public static $REPO_DIRPATH = 'resources';


    function process_response ()
    {
        global $Request, $Feedback;

        parent::process_response();


        if ($this->Config->get_params !== null) {
            if (!$Request::has_get($this->Config->get_params)) {
                $Feedback->failure('needs arguments');
            };
        }
        if ($this->Config->post_params !== null) {
            if (!$Request::has_post($this->Config->post_params)) {
                $Feedback->failure('needs arguments.');
            }
        }
    }

}