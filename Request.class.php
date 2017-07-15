<?php
namespace vdegenne;



class Request {

  /**
   * @var Request Singleton
   */
  static private $Request;

  /**
   * @var string The raw URI, the one that was called from the url bar in the client's browser,
   *      opposed to the relURI properties (see below) that represents the relative URI after the framework's business
   *      transformation (e.g. the hreflang is removed). In many case you'd prefer to use the relURI but the rawURI can
   *      be useful when you want to check the raw requested URI for analysis.
   */
  private $rawURI;
  /**
   * (rel)ative (URI)
   * @var string
   */
  private $relURI;

  /**
   * @var string Language of the request
   */
  private $lang;

  /**
   * @var Domain Associated Domain object
   */
  private $Domain;

  /**
   * @var Page Requested Page object
   */
  private $Page;


  /**
   * @var QueryString
   */
  private $QueryString;

  /**
   * @var Website Associated Website object
   */
  private $Website;

  /**
   * @var Redirection
   */
  private $Redirection;




  private function __construct ($relURI, Domain $Domain) {
    $this->rawURI = trim($_SERVER['REQUEST_URI'], '/');
    $this->relURI = $relURI;
    $this->Domain = $Domain;

    $this->QueryString = new QueryString($_GET);

    /**
     * the Request is building the Website object
     */
    $this->Website = new Website();

    $this->Page = new Page($this, $this->Website->options->pages);
    if ($this->Page->needsSession) {
      session_start();
    }

    $this->resolve_hreflang();

    $this->Page->load_metadatas();
  }

  static function get ($relURI, Domain $Domain) {
    if (self::$Request === null) {

      self::$Request = new Request($relURI, $Domain);
    }

    return Request::$Request;
  }



  private function resolve_hreflang () {

    $Domain = $this->Domain;
    $needsSession = $this->Page->needsSession;
    $QS = $this->QueryString;
    $options = $this->Website->options;



    /* based on hl */
    if ($QS->has('hl')) {
//      echo 'set the lang based on hl<br>';
      $this->lang = $QS->hl;
    }
    /* based on session */
    else if ($needsSession && isset($_SESSION['lang'])) {
//      echo 'set the lang based on session<br>';
      $this->lang = $_SESSION['lang'];
    }
    /* based on cookie */
    else if (isset($_COOKIE['hreflang'])) {
//      echo 'set the lang based on cookie<br>';
      $this->lang = $_COOKIE['hreflang'];
    }
    /* based on preferred languages amongst availables */
    else if (isset($options->availableLanguages)) {
//      echo 'set the lang based on available preferred language<br>';
      $this->lang = Lang::get_prefered_language($options->availableLanguages);
      if ($this->lang === false) {
        $this->lang = $options->availableLanguages[0];
      }
    }
    /* based on preferred languages */
    else {
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
      setcookie('hreflang',
                $this->lang,
                time() + 60 * 60 * 24 * 30,
                '/',
                ($Domain->MasterDomain) !== null ? $Domain->MasterDomain->name : $Domain->name
      );
    }


    if ($needsSession) {
//      echo 'set the session attr lang' . NL;
      $_SESSION['lang'] = $this->lang;
    }
    return true;
    if (!isset($_COOKIE['hreflang'])) {

      if (!$QS->has('hl') && ($this->needsSession && isset($_SESSION['hreflang'])) && array_search($_SESSION['hreflang'], $options['availableLanguages']) !== false) {
        $hl = $_SESSION['hreflang'];
      }
      elseif ($QS->has('hl') && array_search($QS->get('hl'), $options['availableLanguages']) !== false) {
        $hl = $QS->get('hl');
      }
      else {
        if (($hl = Lang::get_prefered_language($options['availableLanguages'])) === false) {
          $hl = $options['availableLanguages'][0];
        }
      }

      setcookie('hreflang', $hl, time() + 60 * 60 * 24 * 30, null, (isset($MDomain) ? MDOMAIN : DOMAIN));
    }
    else {
      if ($QS->has('hl') && array_search($QS->get('hl'), $options['availableLanguages']) !== false) {
        $hl = $QS->get('hl');
        if ($QS->get('hl') !== $_COOKIE['hreflang']) {
          setcookie('hreflang', $QS->get('hl'), time() + 60 * 60 * 24 * 30, null, (isset($MDomain) ? MDOMAIN : DOMAIN));
        }
      }
      elseif (array_search($_COOKIE['hreflang'], $options['availableLanguages']) === false) {
        if (($hl = Lang::get_prefered_language($options['availableLanguages'])) === false) {
          $hl = $options['availableLanguages'][0];
        }
        setcookie('hreflang', $hl, time() + 60 * 60 * 24 * 30, null, (isset($MDomain) ? MDOMAIN : DOMAIN));
      }
      else {
        $hl = $_COOKIE['hreflang'];
      }
    }
  }


  /**
   * Deprecated, the Page object should be generated internally into the Request object
   * @param $pagesOptions
   * @return Page
   */
  function generate_Page ($pagesOptions) {
    /** In the Page Object relURI is rename in relPath
     * since the Page is physical */
    $this->Page = new Page($this, $pagesOptions);

    return $this->Page;
  }


  function has_pending_redirect () {
    return $this->Redirection !== null;
  }



  /**
   * (m)a(k)e (url)
   * will build an absolute URL with protocol, hostname,
   * and path to a ressource.
   *
   * @param string $uri the path to the ressource
   * @param bool $masterdomain
   * @param bool $withQS
   * @param null $QS
   * @return string
   */
  function mkurl ($uri = '', $masterdomain = false, $withQS = true, $QS = null) {

    $url = 'http://' . ($masterdomain ? $this->Domain->MasterDomain->name : $this->Domain->name) . '/';

    // inter-path ?
    //    $url
    //    .= strlen($this->urlPath)
    //    ? ($this->urlPath . '/')
    //    : '';

    $url .= $uri;

    if ($withQS) {
      $url
      .= (is_null($QS))
      ? ((empty($this->QueryString->get_arguments()))
      ? '' : '?' . $this->QueryString)
      : ((empty($QS))
      ? ''
      : '?' . (new QueryString($QS)));
    }

    return $url;
  }


  function __get ($k) {
    switch ($k) {
      case 'URL':
        $return = "http://{$this->Domain->name}/{$this->relURI}";
        break;
      case 'Page':
        if ($this->Page === null) {
          throw new Exception("La page n'a pas été générée.");
        }
        return $this->Page;
        break;
      default:
        $return = $this->{$k};
        break;
    }
    return $return;
  }

  function __set ($k, $v) {
    if (array_key_exists($k, get_object_vars($this))) {
      $this->{$k} = $v;
    }
  }
}