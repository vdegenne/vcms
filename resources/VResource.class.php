<?php
namespace vcms\resources;


class VResource extends Resource
{
    const REPO_DIRPATH = 'pages';

    /**
     * @var VResourceConfig
     */
    public $Config;

    /**
     * @var FeedbackResource
     */
    public $Feedback;


    public function __construct ($dirpath = null, $Config = null)
    {
        $this->Feedback = new FeedbackResource();

        parent::__construct($dirpath, $Config);
    }


    function ensure_params() : bool
    {
        global $Request;

        $Feedback = $this->Feedback;

        if ($this->Config->get_params !== null) {
            if (!$Request::has_get($this->Config->get_params)) {
                $Feedback->failure('needs arguments');
                return false;
            };
        }
        if ($this->Config->post_params !== null) {
            if (!$Request::has_post($this->Config->post_params)) {
                $Feedback->failure('needs arguments.');
                return false;
            }
        }

        return true;
    }

}