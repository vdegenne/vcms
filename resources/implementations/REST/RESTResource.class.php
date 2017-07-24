<?php
namespace vcms\resources\implementations;


use vcms\Config;
use vcms\Request;
use vcms\utils\Authentication;

class RESTResource extends Resource
{
    /**
     * @var Request
     */
    public $Request;


    function process_response ()
    {
        foreach ($GLOBALS as $globalname => $globalvalue) {
            global $$globalname;
        }

        parent::process_response();

        /* make GET and POST arguments local variables */
        if ($this->Config->get_params) {
            foreach ($this->Config->get_params as $g) {
                $$g = $_GET[$g];
            }
        }
        if ($this->Config->post_params) {
            foreach ($this->Config->post_params as $p) {
                $$p = $_POST[$p];
            }
        }

        function get_method_file ($method)
        {
            $globfilename='';
            foreach (str_split($method) as $letter) {
                $globfilename .= '['.strtolower($letter).strtoupper($letter).']';
            }
            $globfilename .= '.*';

            $files = glob($globfilename);

            if (count($files) < 1) {
                throw new \Exception('the file for this method doesn\'t exists');
            }

            return $files[0];
        }

        chdir($this->dirpath);
        $file = get_method_file ($this->Request->method);

        ob_start();
        include $file;
        $this->Response->content = ob_get_contents();
        ob_end_clean();
        chdir($Project->location);
    }
}