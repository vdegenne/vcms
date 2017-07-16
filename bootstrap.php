<?php
use vcms\Project;
use vcms\Request;
use Lol\Test;

require_once "Project.class.php";

$Project = new Project();
/*
 * The include dirpaths are used for the autoloader.
 * The autoloader will automatically search for the classes
 * to include from these directories (recursively)
 */
$Project->location = dirname(getcwd());
$Project->add_include_dirpaths(__DIR__);
$Project->add_include_dirpaths($Project->location . '/' . Project::INCLUDES_DIRNAME);

/* register the autoloader */
require_once '__autoloader.inc.php';


/* the http request object with some useful properties */
$Request = Request::get();


$Resource = $Request->generate_resource();



/**
 * the following lines will load the configurations of the website
 * from 'config.json'. This json is separate so bootstrap can load
 * the basic informations (e.g. the environment mode) and prepare
 * the error handling type.
 */

//try {
//    /* load the configuration file */
//
//    $GLOBALS['_ENV'] = $configJson['env'];
//
//} catch (Exception $e) {
//    throw new Exception("no configuration file found.");
//}
//
//
//if ($_ENV == 'dev') {
//    ini_set('display_errors', 1);
//    error_reporting(E_ALL | E_STRICT);
//}
//// ini_set('display_startup_errors', 1);
//
///**
// * this two functions depend on the 'env' variable in the config.json file :
// *  - dev : will print all errors and warnings directly on the page
// *  - prod :  will silently throw the errors messages in the 'debug.log' file
// at the root of the project path (PROJECT_PATH)
//*/
//set_error_handler(function ($errno, $errstr, $errfile, $errline) {
//
//    if ($_ENV !== 'dev') {
//        return true;
//    }
//
//    // tell php to use internal handler too (prints message on page)
//    return false;
//});
//
//register_shutdown_function(function () {
//
//    if ($_ENV === 'prod') {
//
//        if (($error = error_get_last()) !== null) {
//
//            file_put_contents(PROJECT_PATH . '/debug.log',
//            '['.time()."] $error[message]\n\n",
//            FILE_APPEND);
//
//            printf('%s',  "it seems like the page you trying to reach is unavailable<br>" .
//            "if the problem persists, please contact the administrator.");
//        }
//
//    }
//
//});





/**
 * CONSTANTS
 */

// define('PROJECT_NAME', $configJson['project_name']);

//define('SUPER_ROOT', $configJson['super_root']);
//$SUPER_ROOT = SUPER_ROOT;

//define('INCLUDES_PATH', SUPER_ROOT . '/includes');
//$INCLUDES_PATH = INCLUDES_PATH;
//define('LAYOUTS_PATH', "$INCLUDES_PATH/" . Layout::LAYOUTS_DIRNAME);
//$LAYOUTS_PATH = LAYOUTS_PATH;
//
///* the relative URI generated from the logical redirection
// * (see. htaccess file) */

//$REL_URI = REL_URI;
//
//
//
///**
// * Defining the Domain object
// */
//$Domain = new Domain($_SERVER['SERVER_NAME']);
//$Domain->localPath =  ($configJson['build_type'] === 'debug')
//    ? "$PROJECT_PATH/www"
//    : "$PROJECT_PATH/www";
//
//// should use $_SERVER['HTTP_HOST'] instead
//define('DOMAIN', $Domain->name);
//$DOMAIN = DOMAIN;
//
//
//
//if ($Domain->has_master_domain()) {
//    // Master Domain
//    $MDomain = $Domain->MasterDomain;
//    if ($configJson['master_domain_relativepath']) {
//        $MDomain->localPath = "$SUPER_ROOT/{$configJson['master_domain_relativepath']}";
//    }
//    define('MDOMAIN', $MDomain->name);
//    $MDOMAIN = MDOMAIN;
//}
//
//// require_once('scripts/session.script.php');
//
//
//
///**
// * @var Request
// */

//define('HREFLANG', $Request->lang);
///**
// * @var Website
// */
//$Website = $Request->Website;
///**
// * @var Page
// */
//$Page = $Request->Page;
///**
// * @var Querystring
// */
//$QS = $Request->QueryString;
//$QS->delete_argument('relURI');
//$QS->delete_argument('hl');
//
//
//
//
//if ($Page->needsSession) {
//    if (!isset($_SESSION['User'])) {
//        $_SESSION['User'] = new User();
//    }
//    $User = $_SESSION['User'];
//}
//else {
//    $User = new User();
//}
//$User->hreflang = $Request->lang;
//
//
//
//if (!$Page->exists()) {
//    $Page->relPath = '404';
//}
//
//
//
//
//if ($Page->needsDatabase)
//    {
//        $Database = Database::get($configJson['db_hostname'], 'degennevbase');
//    }
//
//
//$Layout = new Layout();
//
//
//function mkurl () {
//    global $Request;
//    return call_user_func_array([$Request, 'mkurl'], func_get_args());
//}

/*******
 * Processing the content of the resource.
 */
ob_start();
include_once $Resource->contentFilepath;
$Resource->content = ob_get_contents();
ob_end_clean();
/*******
 * End of processing
 */