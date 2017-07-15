<?php
namespace vdegenne;


use ReflectionProperty;
use ReflectionObject;
use Exception;


class Page {

    const PAGES_DIRNAME = 'pages';

    const CONTENT_FILENAME = 'content.php';
    const OPTIONS_FILENAME = 'options.json';
    const METADATAS_FILENAME = 'metadatas.json';
    const SCRIPTS_FILENAME = 'scripts.json'; // to update
    const PREPROCESSOR_FILENAME = 'preprocess.php';


    /**
     * (rel)ative (Path) gets initialized in the constructor from the Request
     * Object. This separate variable is used in the case where the page's location
     * needs to be changed when it doesn't rely on the relative URI of the request.
     * Can be changed thru set_relative_path () where update () is automatically
     * called.
     *
     * It represents the relative path extracted from the http request
     * and can be changed to refer to another "logical" URI.
     * when set_relative_path() is called, it simulates a new request based
     * on a new URI and will call the subsequent internal treatments to load
     * the page.
     *
     * @var string
     */
    protected $relPath;

    /**
       n   * The local path (absolute) of where the files of the page belongs.
       * @var string
       */
    private $path;

    /**
     * @var string Language of the page
     */
    private $hreflang;


    /**
     * @var PageMetadatas
     */
    var $metadatas;

    /**
     * Breadcrumbs of the page's relative path.
     * @var array
     */
    private $breadcrumbs = [];


    /**
     * Associated Request object through which the page was called.
     * @var Request
     */
    private $Request;


    /**
     * Vérifier...
     */
    /** @var Stylesheet[] */
    protected $Stylesheets = [];
    /** @var Script[] */
    protected $Scripts = [];


    /**
     * to add more to the bottom of the assigned layout head template
     * @var string
     */
    var $extraHead;
    /** @var bool */
    var $needsDatabase;
    /** @var bool */
    var $needsAuthentication;
    /** @var bool */
    var $needsSession;
    /** @var bool */
    var $mergeParentsScripts;

    /**
     * String for now, but MIGHT be an Object if needs complexity
     * @var string robots' meta tag's value
     */
    var $robots;

    /** @var bool */
    var $justContent;


    /**
     * Pagev2 constructor.
     * @param Request $Request Associated Request object
     * @param Array|null $options An Array of some of the available options to be applied to the Page object
     */
    public function __construct (Request $Request, $options = null) {

        // $this->PAGES_DIRNAME = ($_ENV === 'dev') ? 'src/pages' : 'pages';

        $this->Request = $Request;
        $this->relPath = $Request->relURI;

        $this->metadatas = new PageMetadatas();

        $this->update();
    }


    public function __get ($k) {
        // parent::__get($k);
        return $this->{$k};
    }
  
    function __set ($k, $v) {

        /*
         * important to include that call
         * protecting readonly values
         */
        // parent::__set($k, $v);

        /* some properties can't be changed */
        switch ($k) {
          
        case 'relPath':
            $this->relPath = $v;
            $this->update();
            break;

          
        default:
            if (array_key_exists($k, get_object_var($this))) {
                $this->{$k} = $v;
            }
            break;
        }

    }


    /**
     * This function is used to update the structure of the Page object
     * when some of its other properties has changed.
     */
    public function update () {

        $this->path = rtrim(PROJECT_PATH . '/' . self::PAGES_DIRNAME . '/' . $this->relPath, '/'); /*preg_replace('@\\/@', DS, $this->relPath), DS); */

        $this->breadcrumbs = explode('/', trim($this->relPath, '/'));

        /* load options */
        $this->reset_options();
        $this->load_options();
        if ($this->Request->Website->options !== null) {
            $this->apply_options($this->Request->Website->options->pages);
        }
        if ($this->Request->lang !== null) {
            $this->load_metadatas();
        }

        /* load scripts */
        $this->load_scripts();
    }

    function reset_options () {
        $this->needsDatabase = null;
        $this->needsAuthentication = null;
        $this->needsSession = null;
        $this->mergeParentsScripts = null;
        $this->robots = null;
        $this->justContent = null;
    }

    function load_options () {
        $optionsFilepath = $this->path . '/' . self::OPTIONS_FILENAME;
        if (file_exists($optionsFilepath)) {
            $options = json_decode(file_get_contents($optionsFilepath), true);
            $this->apply_options($options);
        }
        $this->merge_parent_options();
    }

    function merge_parent_options () {
        $currentPath = $this->path;

        while ($currentPath !== (PROJECT_PATH . '/' . self::PAGES_DIRNAME)) {

            $currentPath = FileSystem::one_folder_up($currentPath);
            $optionsFilepath = "$currentPath/" . self::OPTIONS_FILENAME;

            if (file_exists($optionsFilepath)) {
                $this->apply_options(json_decode(file_get_contents($optionsFilepath), true));
            }
        }
    }

    /**
     * Applying an array of options is not overriding the page's properties already set.
     * If you need to specifically override a property just use the classic accessor.
     *
     * @param array $options
     */
    function apply_options ($options) {
        /* Get the public properties */
        $publics = array_map(function (ReflectionProperty $property) {
            return $property->getName();
        }, (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC));


        if (is_object($options)) {
            $options = (array)$options;
        }

        foreach ($options as $k => $v) {
            if ((array_search($k, $publics) !== false) &&
            $this->{$k} === null
            ) {
                $this->{$k} = $v;
            }
        }

    }


    public function load_metadatas () {
        $metadatasFilepath = $this->path . DS . self::METADATAS_FILENAME;
        if (file_exists($metadatasFilepath)) {

            /**
             * We determine whether the file is global or contains metadatas for each languages
             */
            $metadatas = json_decode(file_get_contents($metadatasFilepath), true);
            $global = false;
            foreach (array_keys(get_class_vars('vdegenne\PageMetadatas')) as $prop) {
                foreach (array_keys($metadatas) as $metaProp) {
                    if ($prop !== 'canonical' && $prop === $metaProp) {
                        $global = true;
                        break 2;
                    }
                }
            }


            if ($global) {

                $this->metadatas
                    = Object::cast(json_decode(file_get_contents($metadatasFilepath)),
                    'vdegenne\PageMetadatas');
            }
            else {

                if (($metadatasIndex = array_search($this->Request->lang, array_keys($metadatas))) !== false) {
                    $this->metadatas
                        = Object::cast(json_decode(json_encode($metadatas[$this->Request->lang])),
                        'vdegenne\PageMetadatas');
                }
            }

            if (isset($metadatas['canonical'])) {
                $this->metadatas->canonical = $metadatas['canonical'];
            }
        }
    }




    function load_scripts () {
        $scriptsPath = $this->path;

        do {
            // next if no script file
            if (!file_exists($scriptsFilePath = "$scriptsPath/" . Page::SCRIPTS_FILENAME)) goto next;

            $scripts = json_decode(file_get_contents($scriptsFilePath), true);
            foreach ($scripts as $script) {

                $inline = (isset($script['inline'])) ? $script['inline'] : false;

                $Domain = (isset($script['subdomain']))
                    ? $script['subdomain'] ? $GLOBALS['SDomain'] : $GLOBALS['Domain']
                    : null;

                is_null($Domain) && $inline && $Domain = $GLOBALS['Domain'];

                $this->add_Script(new Script($script['url'], $Domain, $inline));
            }


          next:
            if (basename($scriptsPath) === self::PAGES_DIRNAME) break;
            $scriptsPath = FileSystem::one_folder_up($scriptsPath, DS);

            // end of one do cycle
        } while ($this->mergeParentsScripts);
    }


    public function exists () {
        return is_dir($this->path);
    }


    public function verifies_PUT () {

        global $_PUT;

        if (!isset($_PUT)) return false;

        if (func_num_args()) {

            $hasAll = true;
            foreach (func_get_args() as $arg) {
                !array_key_exists($arg, $_PUT) && $hasAll = false;
            }

            return $hasAll;
        }
    }

    public function verifies_POST () {

        if (func_num_args()) {

            $hasAll = true;
            foreach (func_get_args() as $arg) {
                !array_key_exists($arg, $_POST) && $hasAll = false;
            }

            return $hasAll;
        }

    }

    public function verifies_GET () {

        if (func_num_args()) {

            $hasAll = true;
            foreach (func_get_args() as $arg) {
                !array_key_exists($arg, $_GET) && $hasAll = false;
            }

            return $hasAll;
        }

    }

    public function has_preprocessor_file () {
        return file_exists($this->path . DS . self::PREPROCESSOR_FILENAME);
    }

    public function add_Stylesheet (Stylesheet $Stylesheet) {
        $GLOBALS['_Stylesheets'][] = $Stylesheet;
        $this->Stylesheets[] = $Stylesheet;
    }

    public function add_Script (Resource $Script) {
        $GLOBALS['_Scripts'][] = $Script;
        $this->Scripts[] = $Script;
    }

    public function get_Scripts () {
        return $this->Scripts;
    }

    public function path_to_content () {
        return $this->path . DS . Page::CONTENT_FILENAME;
    }

    function path_to_another_content ($pageRelDirPath) {

        $pageContentFilePath
            = rtrim(PROJECT_PATH . '/' . self::PAGES_DIRNAME . '/'
            . ($pageRelDirPath && $pageRelDirPath !== '/' ? preg_replace('@\\/@', DS, $pageRelDirPath) . DS : '')
            . self::CONTENT_FILENAME
            , DS);

        if (!file_exists($pageContentFilePath)) {
            throw new Exception('content does not exist');
        }

        return $pageContentFilePath;
    }

    public function path_to_preprocessor_file () {
        return $this->path . DS . self::PREPROCESSOR_FILENAME;
    }

    function path_to_another_preprocessor_file ($pageRelDirPath) {

        $pagePreprocessorFilePath
            = rtrim(PROJECT_PATH . '/' . self::PAGES_DIRNAME . '/'
            . ($pageRelDirPath && $pageRelDirPath !== '/' ? preg_replace('@\\/@', DS, $pageRelDirPath) . DS : '')
            . self::PREPROCESSOR_FILENAME
            , DS);

        if (!file_exists($pagePreprocessorFilePath)) {
            throw new Exception('preprocessor does not exist');
        }

        return $pagePreprocessorFilePath;
    }

    public function get_breadcrumb ($index = null) {
        if (!$index) {
            return $this->breadcrumb;
        }
        else {
            if ($index >= count($this->breadcrumb) || $index < 0) {
                return null;
            }

            return $this->breadcrumb[$index];
        }
    }

    function get_URL () {
        return $this->Request->URL;
    }
}


class PageMetadatas {
    var $fulltitle;
    var $title;
    var $description = '';
    var $keywords = '';
    var $canonical;
}