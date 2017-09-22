<?php
namespace vcms;


use vcms\resources\Resource;
use vcms\resources\ResourceFactory;
use vcms\resources\ResourceType;
use vcms\resources\ResourceException;
use Exception;
use vcms\resources\VResource;


class Request extends VObject {

    /**
     * the URI from the http request (query string trimmed).
     * @var string
     */
    protected $requestURI;


    /**
     * The method of the HTTP Request (GET, POST, ...)
     * @var string
     */
    public $method;


    /**
     * The querystring of the Request.
     * @var QueryString
     */
    public $QueryString;


    /**
     * @var resources\Resource
     */
    public $associatedResource;


    public $response = null;


    public function __construct (string $uri = null, string $method = null)
    {
        global $Project;

        parent::__construct();

        if ($uri === null) {

            $uri = trim($_SERVER['REDIRECT_URL'], '/');

            if ($Project->translation_support) {
                $brokenUri = explode('/', $uri);
                if (array_search($brokenUri[0], $Project->langs) !== false) {
                    $this->lang = $brokenUri[0];
                    array_shift($brokenUri);
                    $uri = implode('/', $brokenUri);
                }
            }
        }
        else {
            if (($querystringPos = strpos($uri, '?')) !== false) {
                $querystringParams = substr($uri, $querystringPos + 1);
                $uri = substr($uri, 0, $querystringPos);
            }
        }
        $this->requestURI = $uri;

        if ($method === null) {
            $method = $_SERVER['REQUEST_METHOD'];
        }
        $this->method = $method;
        if ($method === 'PUT') {
            $_POST = array_merge($_POST, REST::parse_put_form_data());
        }


        // $this->Domain = $Domain;

        $this->QueryString = new QueryString($_GET);
        if (isset($querystringParams)) {
            foreach(explode('&', $querystringParams) as $pair) {
                list($key, $value) = explode('=', $pair);
                $this->QueryString->$key = $value;
            }
        }

        $this->associatedResource = $this->generate_resource();

        RequestStack::push_request($this, $this->associatedResource);
    }


    static function generate_http_request ()
    {
        return new Request();
    }


    function prepare_response ()
    {
        $Response = new Response();
        $Response->Request = $this;
        return $Response;
    }


    function generate_resource (): Resource
    {
        /* get the config file */
        $resourceDirpath = VResource::REPO_DIRPATH . '/' . $this->requestURI;

        try {
            /* trying to load the resource */
            $Resource = ResourceFactory::create_resource_from_repo($resourceDirpath);

            /* specific processing */
            if ($Resource->type === ResourceType::REST) {
                $Resource->set_Request($this);
            }

        } catch (ResourceException $e) {

            switch ($e->getCode()) {

                case ResourceException::INTEGRITY_COMPROMISED:
                    $Resource = ResourceFactory::create_resource_from_repo(VResource::REPO_DIRPATH . '/404');
                    /* 404 page not found fall back */
                    break;

                default:
                    throw new Exception($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }


        return $Resource;
    }


    static function has_arguments ($array, array $values)
    {
        $count = 0;
        foreach ($values as $v) {
            if (array_key_exists($v, $array)) {
                $count++;
            }
        }
        return $count === count($values);
    }

    static function has_post (array $values)
    {
        return self::has_arguments($_POST, $values);
    }

    static function has_get (array $values)
    {
        return self::has_arguments($_GET, $values);
    }

    static function has_put (array $values)
    {
        return self::has_arguments($GLOBALS['_PUT'], $values);
    }


    function request () {
        if ($this->response === null) {
            $this->associatedResource->process();

            $requested = RequestStack::pop_request();
            /** @var Resource $resource */
            $resource = $requested[1];
            $this->response['content'] = $resource->content;
            $this->response['mimetype'] = $resource->Config->mimetype;
        }
    }


    function contents () : string
    {
        $this->request();
        return $this->response['content'];
    }



    private function resolve_hreflang ()
    {

        $Domain = $this->Domain;
        $needsSession = $this->Page->needsSession;
        $QS = $this->QueryString;
        $options = $this->Website->options;


        /* based on hl */
        if ($QS->has('hl')) {
            //      echo 'set the lang based on hl<br>';
            $this->lang = $QS->hl;
        } /* based on session */ else if ($needsSession && isset($_SESSION['lang'])) {
            //      echo 'set the lang based on session<br>';
            $this->lang = $_SESSION['lang'];
        } /* based on cookie */ else if (isset($_COOKIE['hreflang'])) {
            //      echo 'set the lang based on cookie<br>';
            $this->lang = $_COOKIE['hreflang'];
        } /* based on preferred languages amongst availables */ else if (isset($options->availableLanguages)) {
            //      echo 'set the lang based on available preferred language<br>';
            $this->lang = Lang::get_prefered_language($options->availableLanguages);
            if ($this->lang === false) {
                $this->lang = $options->availableLanguages[0];
            }
        } /* based on preferred languages */ else {
            //      echo 'set the lang based on preferred language<br>';
            $this->lang = Lang::get_prefered_language();
            goto end;
        }


        /**
         * we make sure the language is available, else we charge the main language
         */
        if (isset($options->availableLanguages)) {
            if (array_search($this->lang, $options->availableLanguages) === false) {
                $this->lang = $options->availableLanguages[0];
            }
        }

        end:
        if (!isset($_COOKIE['hreflang']) || ($_COOKIE['hreflang'] !== $this->lang)) {
            setcookie('hreflang', $this->lang, time() + 60 * 60 * 24 * 30, '/', ($Domain->MasterDomain) !== null ? $Domain->MasterDomain->name : $Domain->name);
        }


        if ($needsSession) {
            //      echo 'set the session attr lang' . NL;
            $_SESSION['lang'] = $this->lang;
        }
        return true;
        if (!isset($_COOKIE['hreflang'])) {

            if (!$QS->has('hl') && ($this->needsSession && isset($_SESSION['hreflang'])) && array_search($_SESSION['hreflang'], $options['availableLanguages']) !== false) {
                $hl = $_SESSION['hreflang'];
            } elseif ($QS->has('hl') && array_search($QS->get('hl'), $options['availableLanguages']) !== false) {
                $hl = $QS->get('hl');
            } else {
                if (($hl = Lang::get_prefered_language($options['availableLanguages'])) === false) {
                    $hl = $options['availableLanguages'][0];
                }
            }

            setcookie('hreflang', $hl, time() + 60 * 60 * 24 * 30, null, (isset($MDomain) ? MDOMAIN : DOMAIN));
        } else {
            if ($QS->has('hl') && array_search($QS->get('hl'), $options['availableLanguages']) !== false) {
                $hl = $QS->get('hl');
                if ($QS->get('hl') !== $_COOKIE['hreflang']) {
                    setcookie('hreflang', $QS->get('hl'), time() + 60 * 60 * 24 * 30, null, (isset($MDomain) ? MDOMAIN : DOMAIN));
                }
            } elseif (array_search($_COOKIE['hreflang'], $options['availableLanguages']) === false) {
                if (($hl = Lang::get_prefered_language($options['availableLanguages'])) === false) {
                    $hl = $options['availableLanguages'][0];
                }
                setcookie('hreflang', $hl, time() + 60 * 60 * 24 * 30, null, (isset($MDomain) ? MDOMAIN : DOMAIN));
            } else {
                $hl = $_COOKIE['hreflang'];
            }
        }
    }



    function __get ($name)
    {
        switch ($name) {
            case 'URL':
                return "http://{$this->Domain->name}/{$this->requestURI}";
            default:
                return parent::__get($name);
        }
    }

}