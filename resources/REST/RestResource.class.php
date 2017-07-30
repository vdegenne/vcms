<?php
namespace vcms\resources;

use vcms\Request;


class RestResource extends VResource
{
    /**
     * @var Request
     */
    protected $Request;

    public $restContentFilename;
    public $restConfigFilename;


    function set_Request (Request $request)
    {
        $this->Request = $request;

        $this->determine_method_files();
        $RestConfig = ResourceConfigFactory::create_config_object(
            $this->dirpath . '/' . $this->restConfigFilename,
            'rest');

        $this->Config->replace_not_nulls($RestConfig);
        $this->Config->fill_the_blanks($RestConfig);
    }


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

        // chdir($this->dirpath);
        ob_start();
        include PROJECT_LOCATION . '/' . $this->dirpath . '/' . $this->restContentFilename;
        $this->Response->content = ob_get_contents();
        @ob_end_clean();
        // chdir($Project->location);
    }


    function determine_method_files ()
    {
        chdir($this->dirpath);
        $globfilename = '';
        foreach (str_split($this->Request->method) as $letter) {
            $globfilename .= '[' . strtolower($letter) . strtoupper($letter) . ']';
        }

        /* trying to find the content file */
        $globRestContentFilename = "$globfilename.php";
        $restContentFilenames = glob($globRestContentFilename);
        if (count($restContentFilenames) < 1) {
            chdir(PROJECT_LOCATION);
            throw new ResourceException('the content for this REST method doesn\'t exist', 2);
        }
        $this->restContentFilename = $restContentFilenames[0];

        /* trying to find the configuration file */
        $globRestConfigFilename = "$globfilename.json";
        $restConfigFilenames = glob($globRestConfigFilename);
        if (count($restConfigFilenames) < 1) {
            /* not raising an Exception because it's not necessary to have a
               configuration file for the method */
            // throw new \Exception('the configuration file for this REST method doesn\'t exist');
        } else {
            $this->restConfigFilename = $restConfigFilenames[0];
        }
        chdir(PROJECT_LOCATION);
    }
}